<?php
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["userrole"])) {
    header("Location: login.php");
    exit();
}
