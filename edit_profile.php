<?php include("header.php"); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $email = $_POST['email'];
    $current_password_input = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $faculty = $_POST['faculty'];
    $phoneno = $_POST['phoneno'];

    $current_password_query = "SELECT password FROM users WHERE id = ?";
    $stmt = $con->prepare($current_password_query);
    $stmt->bind_param('i', $res_id);
    $stmt->execute();
    $stmt->bind_result($current_password_hash);
    $stmt->fetch();
    $stmt->close();

    // if new passwords match, validate the current password and update
    if (!empty($current_password_input)) {
        if (password_verify($current_password_input, $current_password_hash)) {
            if (!empty($new_password) && $new_password === $confirm_password) {
                $password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $_SESSION['error_message'] = "New passwords do not match!";
                header("Location: edit_profile.php?id=$res_id");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Current password is incorrect!";
            header("Location: edit_profile.php?id=$res_id");
            exit();
        }
    } else {
        $password = $current_password_hash;
    }

    $update_query = "UPDATE users SET username = ?, email = ?, password = ?, faculty = ?, contact_number = ? WHERE id = ?";
    if ($stmt = $con->prepare($update_query)) {
        $stmt->bind_param('sssssi', $username, $email, $password, $faculty, $phoneno, $res_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating profile.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing query.";
    }
    header("Location: edit_profile.php?id=$res_id");
    exit();
}

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('Profile updated successfully!');</script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<script>alert('Failed to update profile, please try again!');</script>";
    unset($_SESSION['error_message']);
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 space-y-6">
        <header class="text-center text-2xl font-bold text-gray-800 mb-6">
            Edit User Profile
        </header>
        <form action="" method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">User Name</label>
                <input required type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($res_username); ?>" placeholder="Enter your full name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">User Email</label>
                <input required type="email" id="email" name="email" value="<?php echo htmlspecialchars($res_email); ?>"
                    placeholder="Enter your email address"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" name="current_password"
                    placeholder="Enter your current password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter your new password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New
                    Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                    placeholder="Confirm your new password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="faculty" class="block text-sm font-medium text-gray-700">Faculty</label>
                <select id="faculty" name="faculty"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="MK-FMHS" <?php if ($res_faculty == 'MK-FMHS') echo 'selected'; ?>>M. Kandiah Faculty
                        of Medicine and Health Sciences</option>
                    <option value="LKC-FES" <?php if ($res_faculty == 'LKC-FES') echo 'selected'; ?>>Lee Kong Chian
                        Faculty of Engineering and Science</option>
                    <option value="FEGT" <?php if ($res_faculty == 'FEGT') echo 'selected'; ?>>Faculty of Engineering
                        and Green Technology</option>
                    <option value="FICT" <?php if ($res_faculty == 'FICT') echo 'selected'; ?>>Faculty of Information
                        and Communication Technology</option>
                    <option value="FSc" <?php if ($res_faculty == 'FSc') echo 'selected'; ?>>Faculty of Science</option>
                    <option value="FAM" <?php if ($res_faculty == 'FAM') echo 'selected'; ?>>Faculty of Accountancy and
                        Management (Sungai Long Campus)</option>
                    <option value="FBF" <?php if ($res_faculty == 'FBF') echo 'selected'; ?>>Faculty of Business and
                        Finance (Kampar Campus)</option>
                    <option value="FAS" <?php if ($res_faculty == 'FAS') echo 'selected'; ?>>Faculty of Arts and Social
                        Science (Kampar Campus)</option>
                    <option value="FCI" <?php if ($res_faculty == 'FCI') echo 'selected'; ?>>Faculty of Creative
                        Industries</option>
                    <option value="Postgraduate" <?php if ($res_faculty == 'Postgraduate') echo 'selected'; ?>>Institute
                        of Postgraduate Studies & Research</option>
                    <option value="ICS" <?php if ($res_faculty == 'ICS') echo 'selected'; ?>>Institute of Chinese
                        Studies</option>
                    <option value="IMLD" <?php if ($res_faculty == 'IMLD') echo 'selected'; ?>>Institute of Management
                        and Leadership Development</option>
                    <option value="CFS-KPR" <?php if ($res_faculty == 'CFS-KPR') echo 'selected'; ?>>Centre for
                        Foundation Studies (Kampar Campus)</option>
                    <option value="CFS-SGLONG" <?php if ($res_faculty == 'CFS-SGLONG') echo 'selected'; ?>>Centre for
                        Foundation Studies (Sungai Long Campus)</option>
                    <option value="CEE" <?php if ($res_faculty == 'CEE') echo 'selected'; ?>>Centre for Extension
                        Education</option>
                    <option value="CCCD" <?php if ($res_faculty == 'CCCD') echo 'selected'; ?>>Centre for Corporate and
                        Community Development</option>
                </select>
            </div>
            <div>
                <label for="phoneno" class="block text-sm font-medium text-gray-700">Contact Number</label>
                <input required type="text" id="phoneno" name="phoneno"
                    value="<?php echo htmlspecialchars($res_contact); ?>" placeholder="i.e.: +60123456789"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" name="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Profile
                </button>
            </div>
        </form>
    </div>
    <?php include("footer.php"); ?>