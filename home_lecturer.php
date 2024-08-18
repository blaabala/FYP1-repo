<?php
include("header.php");
?>
<main>
    <div class="main-box top">
        <div class="top">
            <div class="box">
                <p>Welcome, <strong><?php echo $res_username . ' (' . $res_role_name . ')'; ?></strong></p>
            </div>
            <div class="box">
                <p>Email Address: <strong><?php echo $res_email ?></strong></p>
            </div>
            <div class="box">
                <p>Current Date & Time:<br><strong id="datetime"></strong></p>
            </div>
        </div>
    </div>
</main>

<?php include("footer.php"); ?>