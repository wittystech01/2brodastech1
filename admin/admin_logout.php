<?php
require_once __DIR__ . '/../includes/config.php';
session_unset();
session_destroy();
header('Location: ' . SITE_URL . '/admin/admin_login.php');
exit;
