<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses Ditolak.");
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'add') {
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $author = $conn->real_escape_string($_POST['author']);
    $summary = $conn->real_escape_string($_POST['summary']);
    $content = $conn->real_escape_string($_POST['content']);

    $sql = "INSERT INTO articles (title, category, author, summary, content, created_at) 
            VALUES ('$title', '$category', '$author', '$summary', '$content', NOW())";

    if ($conn->query($sql)) {
        $_SESSION['success_message'] = "Artikel berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan artikel: " . $conn->error;
    }

    header("Location: manage_articles.php");
    exit;

} elseif ($action == 'delete') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id && $conn->query("DELETE FROM articles WHERE id = $id")) {
        $_SESSION['success_message'] = "Artikel berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus artikel.";
    }

    header("Location: manage_articles.php");
    exit;
}

header("Location: manage_articles.php");
exit;
?>