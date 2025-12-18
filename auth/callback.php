<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google_init.php';

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['error'])) {
            throw new Exception("Gagal mendapatkan token: " . $token['error']);
        }

        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $g_id = $google_account_info->id;
        $email = $google_account_info->email;
        $name = $google_account_info->name;

        $check = $conn->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();


        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $update = $conn->prepare("UPDATE users SET google_id = ?, is_verified = 1, verification_token = NULL WHERE email = ?");
            $update->bind_param("ss", $g_id, $email);
            $update->execute();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, google_id, role, is_verified) VALUES (?, ?, ?, 'mahasiswa', 1)");
            $stmt->bind_param("sss", $name, $email, $g_id);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = 'mahasiswa';
            } else {
                throw new Exception("Gagal mendaftarkan akun baru.");
            }
        }

        if (isset($token['refresh_token']) && isset($_SESSION['user_id'])) {
            $refresh_token = $token['refresh_token'];
            $uid = $_SESSION['user_id'];
            $upd_tok = $conn->prepare("UPDATE users SET google_refresh_token = ? WHERE id = ?");
            $upd_tok->bind_param("si", $refresh_token, $uid);
            $upd_tok->execute();
        }

        header("Location: " . BASE_URL . "/pages/dashboard.php?tab=calendar");
        exit;

    } catch (Exception $e) {
        die("Error Login Google: " . $e->getMessage());
    }
} else {
    header("Location: " . $client->createAuthUrl());
    exit;
}
?>