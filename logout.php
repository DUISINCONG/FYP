<?php
session_start();
session_destroy();
header("Location: customerLogin.php");
exit();
?>
