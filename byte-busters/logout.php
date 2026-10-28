<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $_SESSION = [];
    session_destroy();
}
header('Location: index.php');
