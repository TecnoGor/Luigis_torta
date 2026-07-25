<?php
require_once __DIR__ . '/../includes/config.php';
session_destroy();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
