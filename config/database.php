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
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/tugasAkhir/web');
}
