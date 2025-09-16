<?php
session_start();
session_unset();
session_destroy();

header("Location: dept_login.php");
exit();
?>
