<?php
session_start();
session_unset();
session_destroy();
header("Location: /easykick/admin/login.php");
exit();
