<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
include("database.php");

$user_id = $_SESSION['id'] ?? null;
$email = $_SESSION['email'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;

if (!$user_id || !$email) {
    echo "<script>
        alert('Please login to continue.');
        window.location.href = 'login_lecturer.php';
    </script>";
    exit();
}

// Fetch user details with a prepared statement
$query = "SELECT users.*, roles.role_name 
          FROM users 
          JOIN roles ON users.role_id = roles.id 
          WHERE users.email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('User not found. Please login again.');
        window.location.href = 'login_lecturer.php';
    </script>";
    exit();
}

$user = $result->fetch_assoc();
$res_id = $user['id'];
$res_username = $user['username'];
$res_email = $user['email'];
$res_role = $user['role_id'];
$res_role_name = $user['role_name'];
$res_contact = $user['contact_number'];

// Verify that the user is a lecturer (case-insensitive comparison)
if (strtolower($res_role_name) !== 'lecturer') {
    echo "<script>
        alert('You must be a lecturer to set availability.');
        window.location.href = 'home.php';
    </script>";
    exit();
}

// Fetch the lecturer's ID from the lecturers table
$query = "SELECT id FROM lecturers WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Lecturer record not found. Please contact the administrator.');
        window.location.href = 'home_lecturer.php';
    </script>";
    exit();
}

$lecturer = $result->fetch_assoc();
$lecturer_id = $lecturer['id'];

// Function to normalize time to HH:MM format (Simplified)
function normalizeTime($time)
{
    // Ensure the input is a string and convert to UTF-8
    $time = (string)$time;
    $time = mb_convert_encoding($time, 'UTF-8', 'UTF-8');

    // Log the initial input
    file_put_contents('debug.log', "normalizeTime - Initial Time: '$time'\n", FILE_APPEND);

    // Remove all non-printable characters and trim
    $time = preg_replace('/[^\x20-\x7E]/', '', $time);
    $time = trim($time);

    // Keep only digits and colon
    $time = preg_replace('/[^\d:]/', '', $time);

    // Log after cleaning
    file_put_contents('debug.log', "normalizeTime - After Cleaning: '$time'\n", FILE_APPEND);

    // Split and validate structure
    $parts = explode(':', $time);
    if (count($parts) !== 2) {
        throw new Exception("Invalid time format (wrong structure): $time. Please use HH:MM (24-hour format).");
    }

    $hours = (int)$parts[0];
    $minutes = (int)$parts[1];

    // Validate ranges
    if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
        throw new Exception("Invalid time format (out of range): $time. Hours must be 00-23, minutes must be 00-59.");
    }

    // Reformat to ensure HH:MM
    $time = sprintf("%02d:%02d", $hours, $minutes);

    // Log final formatted time
    file_put_contents('debug.log', "normalizeTime - Final Formatted Time: '$time'\n", FILE_APPEND);

    return $time;
}

// Function to validate time intervals (only allow minutes as 00 or 30)
function validateTimeInterval($time)
{
    $timeParts = explode(':', $time);
    $minutes = (int)$timeParts[1];
    return $minutes === 0 || $minutes === 30;
}

// Handle form submission for setting availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_availability'])) {
    try {
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        $start_datetime = $end_datetime = null;
        $day_of_week = $start_time = $end_time = $start_date = $end_date = null;

        if ($is_recurring) {
            $day_of_week = isset($_POST['day_of_week']) ? (int)$_POST['day_of_week'] : -1;
            $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
            $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            // Enhanced logging: Include raw input and its byte representation
            $start_time_bytes = bin2hex($start_time);
            $end_time_bytes = bin2hex($end_time);
            file_put_contents('debug.log', "Raw Start Time: '$start_time' (Bytes: $start_time_bytes), Raw End Time: '$end_time' (Bytes: $end_time_bytes)\n", FILE_APPEND);

            // Validate recurring fields
            if ($day_of_week < 0 || $day_of_week > 6 || !$start_time || !$end_time) {
                $_SESSION['error_message'] = "Please fill in all required fields for recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Normalize the time
            $start_time = normalizeTime($start_time);
            $end_time = normalizeTime($end_time);

            // Log normalized times
            file_put_contents('debug.log', "Normalized Start Time: '$start_time', Normalized End Time: '$end_time'\n", FILE_APPEND);

            // Validate time intervals (only allow 00 or 30 minutes)
            if (!validateTimeInterval($start_time) || !validateTimeInterval($end_time)) {
                $_SESSION['error_message'] = "Time must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).";
                header("Location: set_availability.php");
                exit;
            }

            // Ensure end_time is after start_time
            $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
            $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
            if ($start_time_obj >= $end_time_obj) {
                $_SESSION['error_message'] = "End time must be after start time.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate dates if provided
            if ($start_date && $end_date) {
                $start_date_obj = new DateTime($start_date);
                $end_date_obj = new DateTime($end_date);
                if ($start_date_obj >= $end_date_obj) {
                    $_SESSION['error_message'] = "End date must be after start date.";
                    header("Location: set_availability.php");
                    exit;
                }
            }
        } else {
            $start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
            $end_datetime = isset($_POST['end_datetime']) ? $_POST['end_datetime'] : null;

            // Validate non-recurring fields
            if (!$start_datetime || !$end_datetime) {
                $_SESSION['error_message'] = "Please fill in all required fields for non-recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate datetime
            $start_dt_obj = new DateTime($start_datetime);
            $end_dt_obj = new DateTime($end_datetime);
            if ($start_dt_obj >= $end_dt_obj) {
                $_SESSION['error_message'] = "End datetime must be after start datetime.";
                header("Location: set_availability.php");
                exit;
            }
        }

        // Insert into lecturer_availability table
        $query = "INSERT INTO lecturer_availability (lecturer_id, start_datetime, end_datetime, is_recurring, day_of_week, start_time, end_time, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("isssissss", $lecturer_id, $start_datetime, $end_datetime, $is_recurring, $day_of_week, $start_time, $end_time, $start_date, $end_date);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Availability set successfully.";
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error setting availability: " . $e->getMessage();
    }
    header("Location: set_availability.php");
    exit;
}

// Handle form submission for setting blocked dates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_block_date'])) {
    try {
        $start_date = !empty($_POST['block_start_date']) ? $_POST['block_start_date'] : null;
        $end_date = !empty($_POST['block_end_date']) ? $_POST['block_end_date'] : null;
        $reason = !empty($_POST['reason']) ? trim($_POST['reason']) : null;

        // Validate fields
        if (!$start_date || !$end_date) {
            $_SESSION['error_message'] = "Please fill in all required fields for blocked dates.";
            header("Location: set_availability.php");
            exit;
        }

        // Validate dates
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        if ($start_date_obj > $end_date_obj) {
            $_SESSION['error_message'] = "End date must be on or after start date.";
            header("Location: set_availability.php");
            exit;
        }

        // Check for conflicts with existing appointments
        $query = "SELECT id, title, start_datetime, end_datetime 
                  FROM appointments 
                  WHERE lecturer_id = ? 
                  AND status IN ('Confirmed', 'Completed') 
                  AND (
                      (start_datetime <= ? AND end_datetime >= ?) 
                      OR (start_datetime >= ? AND start_datetime <= ?) 
                      OR (end_datetime >= ? AND end_datetime <= ?)
                  )";
        $stmt = $con->prepare($query);
        $stmt->bind_param("issssss", $lecturer_id, $end_date, $start_date, $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $conflicts = [];
            while ($appointment = $result->fetch_assoc()) {
                $start = date("d M Y, h:i A", strtotime($appointment['start_datetime']));
                $end = date("d M Y, h:i A", strtotime($appointment['end_datetime']));
                $conflicts[] = "Appointment ID {$appointment['id']} ({$appointment['title']}) on {$start} to {$end}";
            }
            $_SESSION['error_message'] = "Cannot set blocked date: Conflicts with existing appointments:\n" . implode("\n", $conflicts);
            header("Location: set_availability.php");
            exit;
        }

        // Insert into blocked_dates table
        $query = "INSERT INTO blocked_dates (lecturer_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("isss", $lecturer_id, $start_date, $end_date, $reason);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Blocked date set successfully.";
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error setting blocked date: " . $e->getMessage();
    }
    header("Location: set_availability.php");
    exit;
}

// Handle form submission for updating availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_availability'])) {
    try {
        $avail_id = (int)$_POST['avail_id'];
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        $start_datetime = $end_datetime = null;
        $day_of_week = $start_time = $end_time = $start_date = $end_date = null;

        if ($is_recurring) {
            $day_of_week = isset($_POST['day_of_week']) ? (int)$_POST['day_of_week'] : -1;
            $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
            $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            // Enhanced logging: Include raw input and its byte representation
            $start_time_bytes = bin2hex($start_time);
            $end_time_bytes = bin2hex($end_time);
            file_put_contents('debug.log', "Update - Raw Start Time: '$start_time' (Bytes: $start_time_bytes), Raw End Time: '$end_time' (Bytes: $end_time_bytes)\n", FILE_APPEND);

            // Validate recurring fields
            if ($day_of_week < 0 || $day_of_week > 6 || !$start_time || !$end_time) {
                $_SESSION['error_message'] = "Please fill in all required fields for recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Normalize the time
            $start_time = normalizeTime($start_time);
            $end_time = normalizeTime($end_time);

            // Log normalized times
            file_put_contents('debug.log', "Update - Normalized Start Time: '$start_time', Normalized End Time: '$end_time'\n", FILE_APPEND);

            // Validate time intervals (only allow 00 or 30 minutes)
            if (!validateTimeInterval($start_time) || !validateTimeInterval($end_time)) {
                $_SESSION['error_message'] = "Time must be on the hour (e.g., 10:00) or half-hour (e.g., 10:30).";
                header("Location: set_availability.php");
                exit;
            }

            // Ensure end_time is after start_time
            $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
            $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
            if ($start_time_obj >= $end_time_obj) {
                $_SESSION['error_message'] = "End time must be after start time.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate dates if provided
            if ($start_date && $end_date) {
                $start_date_obj = new DateTime($start_date);
                $end_date_obj = new DateTime($end_date);
                if ($start_date_obj >= $end_date_obj) {
                    $_SESSION['error_message'] = "End date must be after start date.";
                    header("Location: set_availability.php");
                    exit;
                }
            }
        } else {
            $start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
            $end_datetime = isset($_POST['end_datetime']) ? $_POST['end_datetime'] : null;

            // Validate non-recurring fields
            if (!$start_datetime || !$end_datetime) {
                $_SESSION['error_message'] = "Please fill in all required fields for non-recurring availability.";
                header("Location: set_availability.php");
                exit;
            }

            // Validate datetime
            $start_dt_obj = new DateTime($start_datetime);
            $end_dt_obj = new DateTime($end_datetime);
            if ($start_dt_obj >= $end_dt_obj) {
                $_SESSION['error_message'] = "End datetime must be after start datetime.";
                header("Location: set_availability.php");
                exit;
            }
        }

        // Update lecturer_availability table
        $query = "UPDATE lecturer_availability SET start_datetime = ?, end_datetime = ?, is_recurring = ?, day_of_week = ?, start_time = ?, end_time = ?, start_date = ?, end_date = ? WHERE id = ? AND lecturer_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssissssii", $start_datetime, $end_datetime, $is_recurring, $day_of_week, $start_time, $end_time, $start_date, $end_date, $avail_id, $lecturer_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Availability updated successfully.";
            } else {
                $_SESSION['error_message'] = "No availability was updated. It may not exist or you lack permission to update it.";
            }
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating availability: " . $e->getMessage();
    }
    header("Location: set_availability.php");
    exit;
}

// Fetch existing availability for display
$query = "SELECT * FROM lecturer_availability WHERE lecturer_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
$availabilities = [];
while ($row = $result->fetch_assoc()) {
    $availabilities[] = $row;
}

// Fetch existing blocked dates
$query = "SELECT * FROM blocked_dates WHERE lecturer_id = ? ORDER BY start_date ASC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
$blocked_dates = [];
while ($row = $result->fetch_assoc()) {
    $blocked_dates[] = $row;
}

// Function to check if a date range overlaps with any blocked date range
function checkOverlap($start_date, $end_date, $blocked_dates)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    foreach ($blocked_dates as $blocked) {
        $block_start = new DateTime($blocked['start_date']);
        $block_end = new DateTime($blocked['end_date']);
        // Check for overlap: start_date <= block_end AND end_date >= block_start
        if ($start <= $block_end && $end >= $block_start) {
            return true;
        }
    }
    return false;
}

// Function to find overlapping periods for recurring availability
function findRecurringOverlaps($avail, $blocked_dates)
{
    if (empty($blocked_dates)) {
        return [];
    }

    $overlapping_periods = [];
    $day_of_week = $avail['day_of_week'];
    $start_time = $avail['start_time'];
    $end_time = $avail['end_time'];
    $recurring_start = $avail['start_date'] ? new DateTime($avail['start_date']) : new DateTime();
    $recurring_end = $avail['end_date'] ? new DateTime($avail['end_date']) : (new DateTime())->modify('+1 year'); // Default to 1 year if no end date

    // Iterate through each week between recurring_start and recurring_end
    $current = clone $recurring_start;
    while ($current <= $recurring_end) {
        if ((int)$current->format('w') === $day_of_week) {
            $instance_start = (clone $current)->setTime((int)substr($start_time, 0, 2), (int)substr($start_time, 3, 2));
            $instance_end = (clone $current)->setTime((int)substr($end_time, 0, 2), (int)substr($end_time, 3, 2));
            foreach ($blocked_dates as $blocked) {
                $block_start = new DateTime($blocked['start_date']);
                $block_end = new DateTime($blocked['end_date']);
                if ($instance_start <= $block_end && $instance_end >= $block_start) {
                    $overlapping_periods[] = $instance_start->format('d M Y');
                }
            }
        }
        $current->modify('+1 day');
    }

    // Simplify overlapping periods if they are consecutive
    if (empty($overlapping_periods)) {
        return [];
    }

    $simplified_periods = [];
    $current_period = [];
    $dates = array_unique($overlapping_periods);
    sort($dates); // Sort dates in ascending order

    foreach ($dates as $index => $date) {
        if (empty($current_period)) {
            $current_period = [$date, $date];
        } else {
            $last_date = new DateTime($current_period[1]);
            $current_date = new DateTime($date);
            $interval = $last_date->diff($current_date);
            if ($interval->days == 7) { // Check if dates are one week apart (same day of week)
                $current_period[1] = $date;
            } else {
                // End the current period and start a new one
                if ($current_period[0] === $current_period[1]) {
                    $simplified_periods[] = $current_period[0];
                } else {
                    $simplified_periods[] = $current_period[0] . " to " . $current_period[1];
                }
                $current_period = [$date, $date];
            }
        }
    }

    // Handle the last period
    if (!empty($current_period)) {
        if ($current_period[0] === $current_period[1]) {
            $simplified_periods[] = $current_period[0];
        } else {
            $simplified_periods[] = $current_period[0] . " to " . $current_period[1];
        }
    }

    return $simplified_periods;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Set Availability</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
    }

    .disabled-text {
        color: #888;
        font-style: italic;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 50;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        Csak color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Hide the AM/PM part of the time picker (browser-specific) */
    input[type="time"]::-webkit-datetime-edit-ampm-field {
        display: none;
    }
    </style>
    <script>
    function toggleAvailabilityFields(context = 'main-form') {
        let isRecurring, nonRecurringFields, recurringFields;

        if (context === 'main-form') {
            isRecurring = document.querySelector('#is_recurring').checked;
            nonRecurringFields = document.querySelector('#non-recurring-fields');
            recurringFields = document.querySelector('#recurring-fields');
        } else {
            // For modal (edit-avail-modal)
            isRecurring = document.querySelector(`#${context} #edit-is-recurring`).checked;
            nonRecurringFields = document.querySelector(`#${context} #edit-non-recurring-fields`);
            recurringFields = document.querySelector(`#${context} #edit-recurring-fields`);
        }

        nonRecurringFields.classList.toggle('hidden', isRecurring);
        recurringFields.classList.toggle('hidden', !isRecurring);
    }

    // Ensure time is in 24-hour format (HH:MM) before submission
    function ensure24HourFormat(inputId) {
        const input = document.getElementById(inputId);
        let timeValue = input.value;

        // If the input is empty, do nothing
        if (!timeValue) return;

        // Remove any non-digit or non-colon characters
        timeValue = timeValue.replace(/[^\d:]/g, '');

        // Match HH:MM format
        if (timeValue.match(/^\d{1,2}:\d{2}$/)) {
            let [hours, minutes] = timeValue.split(':').map(Number);
            // Validate hours and minutes
            if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
                input.value = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            } else {
                alert('Invalid time. Hours must be 00-23 and minutes must be 00-59.');
                input.value = '';
            }
        } else {
            alert('Please enter the time in 24-hour format (e.g., 14:30).');
            input.value = '';
        }
    }

    // Validate form on submission
    function validateForm(formId) {
        // Ensure time fields are validated before submission
        if (document.getElementById('is_recurring')?.checked || formId === 'edit-avail-form') {
            ensure24HourFormat(formId === 'edit-avail-form' ? 'edit-start-time' : 'start_time');
            ensure24HourFormat(formId === 'edit-avail-form' ? 'edit-end-time' : 'end_time');

            // Check if the inputs are empty after validation
            const startTime = document.getElementById(formId === 'edit-avail-form' ? 'edit-start-time' : 'start_time')
                .value;
            const endTime = document.getElementById(formId === 'edit-avail-form' ? 'edit-end-time' : 'end_time').value;

            if (!startTime || !endTime) {
                alert('Please ensure both start and end times are filled and valid.');
                return false;
            }
        }
        return true;
    }

    // Edit Blocked Date Modal
    function openEditBlockedModal(id, startDate, endDate, reason) {
        const modal = document.getElementById('edit-blocked-modal');
        document.getElementById('edit-blocked-id').value = id;
        document.getElementById('edit-block-start-date').UT
        value = formatDateForInput(startDate);
        document.getElementById('edit-block-end-date').value = formatDateForInput(endDate);
        document.getElementById('edit-block-reason').value = reason || '';
        modal.style.display = 'block';
    }

    function closeEditBlockedModal() {
        const modal = document.getElementById('edit-blocked-modal');
        modal.style.display = 'none';
    }

    // Edit Availability Modal
    function openEditAvailModal(id, isRecurring, startDatetime, endDatetime, dayOfWeek, startTime, endTime, startDate,
        endDate) {
        const modal = document.getElementById('edit-avail-modal');
        document.getElementById('edit-avail-id').value = id;
        document.getElementById('edit-is-recurring').checked = isRecurring === '1';
        document.getElementById('edit-start-datetime').value = startDatetime ? startDatetime.replace(' ', 'T') : '';
        document.getElementById('edit-end-datetime').value = endDatetime ? endDatetime.replace(' ', 'T') : '';
        document.getElementById('edit-day-of-week').value = dayOfWeek !== null ? dayOfWeek : '';
        document.getElementById('edit-start-time').value = startTime || '';
        document.getElementById('edit-end-time').value = endTime || '';
        document.getElementById('edit-avail-start-date').value = startDate || '';
        document.getElementById('edit-avail-end-date').value = endDate || '';

        // Toggle fields based on is_recurring
        toggleAvailabilityFields('edit-avail-modal');
        modal.style.display = 'block';
    }

    function closeEditAvailModal() {
        const modal = document.getElementById('edit-avail-modal');
        modal.style.display = 'none';
    }

    function formatDateForInput(dateStr) {
        // Convert "28 Apr 2025" to "2025-04-28" for <input type="date">
        const date = new Date(dateStr);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDatetimeForInput(datetimeStr) {
        // Convert "02 May 2025, 01:00 PM" to "2025-05-02T13:00"
        const date = new Date(datetimeStr);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function confirmDelete(type, id) {
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
            window.location.href = type === 'availability' ? `delete_availability.php?id=${id}` :
                `delete_blocked_date.php?id=${id}`;
        }
    }

    window.onclick = function(event) {
        const editBlockedModal = document.getElementById('edit-blocked-modal');
        const editAvailModal = document.getElementById('edit-avail-modal');
        if (event.target == editBlockedModal) {
            editBlockedModal.style.display = 'none';
        }
        if (event.target == editAvailModal) {
            editAvailModal.style.display = 'none';
        }
    };
    </script>
</head>

<body class="bg-gray-100 font-merriweather">
    <header class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white shadow-lg">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="home_lecturer.php">
                        <img src="assets/images/logo.png" alt="logo"
                            class="w-16 h-auto transition-transform transform hover:scale-110">
                    </a>
                    <a href="home_lecturer.php"
                        class="text-2xl font-bold tracking-wide hover:text-blue-200 transition-colors">
                        Appointment Management System
                    </a>
                </div>
                <div>
                    <ul class="flex space-x-6 items-center">
                        <li>
                            <a href="home_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Home</a>
                        </li>
                        <li>
                            <a href="appointment_view_lecturer.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Appointments</a>
                        </li>
                        <li>
                            <a href="set_availability.php"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Set
                                Availability</a>
                        </li>
                        <li>
                            <a href="edit_profile_lecturer.php?id=<?php echo htmlspecialchars($res_id); ?>"
                                class="text-lg font-medium hover:text-blue-200 transition-colors duration-300">Edit
                                Profile</a>
                        </li>
                        <li>
                            <a href="logout.php"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors duration-300">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Set Availability</h2>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Form to set availability -->
        <form id="set-availability-form" action="set_availability.php" method="post"
            class="bg-white p-4 rounded shadow mb-6" onsubmit="return validateForm('set-availability-form')">
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="is_recurring" name="is_recurring"
                        onchange="toggleAvailabilityFields('main-form')" class="form-checkbox">
                    <span class="ml-2">Recurring Availability</span>
                </label>
            </div>

            <!-- Non-recurring fields -->
            <div id="non-recurring-fields" class="">
                <div class="mb-4">
                    <label for="start_datetime" class="block text-gray-700">Start Date and Time:</label>
                    <input type="datetime-local" id="start_datetime" name="start_datetime"
                        class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="end_datetime" class="block text-gray-700">End Date and Time:</label>
                    <input type="datetime-local" id="end_datetime" name="end_datetime"
                        class="w-full p-2 border rounded">
                </div>
            </div>

            <!-- Recurring fields -->
            <div id="recurring-fields" class="hidden">
                <div class="mb-4">
                    <label for="day_of_week" class="block text-gray-700">Day of the Week:</label>
                    <select id="day_of_week" name="day_of_week" class="w-full p-2 border rounded">
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="start_time" class="block text-gray-700">Start Time (HH:MM, e.g., 10:00
                        or 10:30):</label>
                    <input type="time" id="start_time" name="start_time" step="1800" class="w-full p-2 border rounded"
                        onblur="ensure24HourFormat('start_time')">
                </div>
                <div class="mb-4">
                    <label for="end_time" class="block text-gray-700">End Time (HH:MM, e.g., 10:00 or
                        10:30):</label>
                    <input type="time" id="end_time" name="end_time" step="1800" class="w-full p-2 border rounded"
                        onblur="ensure24HourFormat('end_time')">
                </div>
                <div class="mb-4">
                    <label for="start_date" class="block text-gray-700">Recurring Start Date (optional):</label>
                    <input type="date" id="start_date" name="start_date" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="end_date" class="block text-gray-700">Recurring End Date (optional):</label>
                    <input type="date" id="end_date" name="end_date" class="w-full p-2 border rounded">
                </div>
            </div>

            <button type="submit" name="set_availability"
                class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Set Availability</button>
        </form>

        <!-- Form to set blocked dates -->
        <h2 class="text-2xl font-bold mb-4">Set Block Date</h2>
        <form action="set_availability.php" method="post" class="bg-white p-4 rounded shadow mb-6">
            <div class="mb-4">
                <label for="block_start_date" class="block text-gray-700">Start Date:</label>
                <input type="date" id="block_start_date" name="block_start_date" class="w-full p-2 border rounded"
                    required>
            </div>
            <div class="mb-4">
                <label for="block_end_date" class="block text-gray-700">End Date:</label>
                <input type="date" id="block_end_date" name="block_end_date" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="reason" class="block text-gray-700">Reason (optional):</label>
                <textarea id="reason" name="reason" class="w-full p-2 border rounded" rows="3"></textarea>
            </div>
            <button type="submit" name="set_block_date"
                class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Set Block Date</button>
        </form>

        <!-- Display existing availability with Edit and Delete buttons -->
        <h3 class="text-xl font-semibold mt-6 mb-2">Existing Availability</h3>
        <?php if (count($availabilities) > 0): ?>
        <ul class="bg-white p-4 rounded shadow">
            <?php foreach ($availabilities as $avail): ?>
            <li class="mb-2 flex justify-between items-center">
                <?php
                        $avail_id = $avail['id'];
                        $is_recurring = $avail['is_recurring'];
                        $start_datetime = $avail['start_datetime'] ? date("d M Y, h:i A", strtotime($avail['start_datetime'])) : null;
                        $end_datetime = $avail['end_datetime'] ? date("d M Y, h:i A", strtotime($avail['end_datetime'])) : null;
                        $day_of_week = $avail['day_of_week'];
                        $start_time = $avail['start_time'];
                        $end_time = $avail['end_time'];
                        $start_date = $avail['start_date'];
                        $end_date = $avail['end_date'];

                        if ($is_recurring) {
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $day_name = $days[$day_of_week];
                            $start_time_display = date("h:i A", strtotime($start_time));
                            $end_time_display = date("h:i A", strtotime($end_time));
                            $recurring_period = '';
                            if ($start_date && $end_date) {
                                $start_date_display = date("d M Y", strtotime($start_date));
                                $end_date_display = date("d M Y", strtotime($end_date));
                                $recurring_period = " (from $start_date_display to $end_date_display)";
                            }
                            echo "Every $day_name, $start_time_display - $end_time_display$recurring_period";

                            // Check for overlapping blocked dates
                            $overlapping_periods = findRecurringOverlaps($avail, $blocked_dates);
                            if (!empty($overlapping_periods)) {
                                $overlap_message = " (Blocked on: " . implode(", ", $overlapping_periods) . ")";
                                echo " <span class='disabled-text'>$overlap_message</span>";
                            }
                        } else {
                            $is_blocked = checkOverlap(date("Y-m-d", strtotime($avail['start_datetime'])), date("Y-m-d", strtotime($avail['end_datetime'])), $blocked_dates);
                            echo "$start_datetime - $end_datetime";
                            if ($is_blocked) {
                                echo " <span class='disabled-text'>(Blocked due to blocked dates)</span>";
                            }
                        }
                        ?>
                <div>
                    <button
                        onclick="openEditAvailModal(<?php echo $avail_id; ?>, '<?php echo $is_recurring; ?>', '<?php echo $start_datetime; ?>', '<?php echo $end_datetime; ?>', '<?php echo $day_of_week; ?>', '<?php echo $start_time; ?>', '<?php echo $end_time; ?>', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')"
                        class="text-blue-500 hover:underline mr-2">Edit</button>
                    <button onclick="confirmDelete('availability', <?php echo $avail_id; ?>)"
                        class="text-red-500 hover:underline">Delete</button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-gray-700">No availability set.</p>
        <?php endif; ?>

        <!-- Display existing blocked dates with Edit and Delete buttons -->
        <h3 class="text-xl font-semibold mt-6 mb-2">Existing Blocked Dates</h3>
        <?php if (count($blocked_dates) > 0): ?>
        <ul class="bg-white p-4 rounded shadow">
            <?php foreach ($blocked_dates as $blocked): ?>
            <li class="mb-2 flex justify-between items-center">
                <?php
                        $start_date = date("d M Y", strtotime($blocked['start_date']));
                        $end_date = date("d M Y", strtotime($blocked['end_date']));
                        $reason = $blocked['reason'] ? " - Reason: " . htmlspecialchars($blocked['reason']) : '';
                        $id = $blocked['id'];
                        echo "<span>$start_date to $end_date$reason</span>";
                        ?>
                <div>
                    <button
                        onclick="openEditBlockedModal(<?php echo $id; ?>, '<?php echo $start_date; ?>', '<?php echo $end_date; ?>', '<?php echo htmlspecialchars($blocked['reason'] ?? ''); ?>')"
                        class="text-blue-500 hover:underline mr-2">Edit</button>
                    <button onclick="confirmDelete('blocked date', <?php echo $id; ?>)"
                        class="text-red-500 hover:underline">Delete</button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-gray-700">No blocked dates set.</p>
        <?php endif; ?>

        <!-- Edit Blocked Date Modal -->
        <div id="edit-blocked-modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditBlockedModal()">×</span>
                <h3 class="text-lg font-bold mb-4">Edit Blocked Date</h3>
                <form id="edit-blocked-form" action="update_blocked_date.php" method="post">
                    <input type="hidden" name="blocked_id" id="edit-blocked-id">
                    <div class="mb-4">
                        <label class="block text-gray-700">Start Date:</label>
                        <input type="date" id="edit-block-start-date" name="start_date"
                            class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700">End Date:</label>
                        <input type="date" id="edit-block-end-date" name="end_date" class="w-full p-2 border rounded"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700">Reason (optional):</label>
                        <textarea id="edit-block-reason" name="reason" class="w-full p-2 border rounded"
                            rows="3"></textarea>
                    </div>
                    <button type="submit" name="update"
                        class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Update Blocked Date</button>
                </form>
            </div>
        </div>

        <!-- Edit Availability Modal -->
        <div id="edit-avail-modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditAvailModal()">×</span>
                <h3 class="text-lg font-bold mb-4">Edit Availability</h3>
                <form id="edit-avail-form" action="set_availability.php" method="post"
                    onsubmit="return validateForm('edit-avail-form')">
                    <input type="hidden" name="avail_id" id="edit-avail-id">
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="edit-is-recurring" name="is_recurring"
                                onchange="toggleAvailabilityFields('edit-avail-modal')" class="form-checkbox">
                            <span class="ml-2">Recurring Availability</span>
                        </label>
                    </div>

                    <!-- Non-recurring fields -->
                    <div id="edit-non-recurring-fields" class="">
                        <div class="mb-4">
                            <label for="edit-start-datetime" class="block text-gray-700">Start Date and Time:</label>
                            <input type="datetime-local" id="edit-start-datetime" name="start_datetime"
                                class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="edit-end-datetime" class="block text-gray-700">End Date and Time:</label>
                            <input type="datetime-local" id="edit-end-datetime" name="end_datetime"
                                class="w-full p-2 border rounded">
                        </div>
                    </div>

                    <!-- Recurring fields -->
                    <div id="edit-recurring-fields" class="hidden">
                        <div class="mb-4">
                            <label for="edit-day-of-week" class="block text-gray-700">Day of the Week:</label>
                            <select id="edit-day-of-week" name="day_of_week" class="w-full p-2 border rounded">
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit-start-time" class="block text-gray-700">Start Time (HH:MM,
                                e.g., 10:00 or 10:30):</label>
                            <input type="time" id="edit-start-time" name="start_time" step="1800"
                                class="w-full p-2 border rounded" onblur="ensure24HourFormat('edit-start-time')">
                        </div>
                        <div class="mb-4">
                            <label for="edit-end-time" class="block text-gray-700">End Time (HH:MM,
                                e.g., 10:00 or 10:30):</label>
                            <input type="time" id="edit-end-time" name="end_time" step="1800"
                                class="w-full p-2 border rounded" onblur="ensure24HourFormat('edit-end-time')">
                        </div>
                        <div class="mb-4">
                            <label for="edit-avail-start-date" class="block text-gray-700">Recurring Start Date
                                (optional):</label>
                            <input type="date" id="edit-avail-start-date" name="start_date"
                                class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="edit-avail-end-date" class="block text-gray-700">Recurring End Date
                                (optional):</label>
                            <input type="date" id="edit-avail-end-date" name="end_date"
                                class="w-full p-2 border rounded">
                        </div>
                    </div>

                    <button type="submit" name="update_availability"
                        class="bg-blue-800 text-white py-2 px-4 rounded hover:bg-blue-900">Update Availability</button>
                </form>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
</body>

</html>