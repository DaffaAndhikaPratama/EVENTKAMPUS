<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../classes/ApiClientEvent.php';

$apiClient = new ApiClientEvent();
$client = $apiClient->getClient();

return $client;
?>