<?php
require_once __DIR__ . '/../vendor/autoload.php';

class ApiClientEvent
{
    private $client;
    private $cacheDir;
    private $cacheTtl; 

    public function __construct()
    {
        if (!isset($_ENV['GOOGLE_CLIENT_ID'])) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();
        }

        $this->client = new Google_Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URL']);

        $this->client->addScope("email");
        $this->client->addScope("profile");
        $this->client->addScope("https://www.googleapis.com/auth/calendar");

        $this->cacheDir = __DIR__ . '/../storage/cache/';
        $this->cacheTtl = 3600; 

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getCachedEvents($calendarId = 'primary', $limit = 10)
    {
        $cacheFile = $this->cacheDir . 'events_' . md5($calendarId . $limit) . '.json';

        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (isset($cacheData['timestamp']) && (time() - $cacheData['timestamp'] < $this->cacheTtl)) {
                return $cacheData['data']; 
            }
        }

        if ($this->client->getAccessToken()) {
            $service = new Google_Service_Calendar($this->client);
            $optParams = array(
                'maxResults' => $limit,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
            );

            try {
                $results = $service->events->listEvents($calendarId, $optParams);
                $items = $results->getItems();

                $this->saveToCache($cacheFile, $items);

                return $items;
            } catch (Exception $e) {
                error_log("API Error: " . $e->getMessage());
                return [];
            }
        }

        return [];
    }

    private function saveToCache($file, $data)
    {
        $payload = [
            'timestamp' => time(),
            'data' => $data
        ];
        file_put_contents($file, json_encode($payload));
    }
}
