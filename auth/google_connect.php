<?php
session_start();
require_once __DIR__ . '/../config/google_init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$client->setAccessType('offline');
$client->setPrompt('select_account consent'); 

$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);
exit;
?>