<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();


require_once __DIR__ . '/../classes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();


if (!defined('BASE_URL')) {
    if (isset($_ENV['BASE_URL'])) {
        define('BASE_URL', $_ENV['BASE_URL']);
    } else {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Detect path from document root to 'web' folder
        // __DIR__ is .../web/config
        // dirname(__DIR__) is .../web
        $web_root = str_replace('\\', '/', dirname(__DIR__));
        $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

        // Calculate relative path
        $relative_path = str_replace($doc_root, '', $web_root);

        // Ensure leading slash and remove trailing slash
        $relative_path = '/' . trim($relative_path, '/');
        if ($relative_path === '/')
            $relative_path = '';

        define('BASE_URL', "$protocol://$host$relative_path");
    }
}
