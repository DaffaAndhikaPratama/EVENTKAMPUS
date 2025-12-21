<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google_init.php';

if (!isset($_GET['code'])) {
    header('Location: ../index.php');
    exit;
}

try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        if (!isset($_SESSION['user_id'])) {
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $email = $google_account_info->email;

            $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
            } else {
                $_SESSION['error_message'] = "Email Google ini belum terdaftar. Silakan daftar manual terlebih dahulu.";
                header('Location: ../auth/login.php');
                exit;
            }
        }

        if (isset($token['refresh_token']) && isset($_SESSION['user_id'])) {
            $refresh_token = $token['refresh_token'];
            $user_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("UPDATE users SET google_refresh_token = ? WHERE id = ?");
            $stmt->bind_param("si", $refresh_token, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Berhasil terhubung dengan Google Calendar!";
            }
        }

    } else {
        $_SESSION['error_message'] = "Gagal login dengan Google (Token Error).";
        header('Location: ../auth/login.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
}

header('Location: ../pages/dashboard.php?tab=calendar');
exit;
?>