<?php
require_once __DIR__ . '/../vendor/autoload.php';

class CalendarHelper
{
    private $service;
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
        if ($client->getAccessToken()) {
            $this->service = new Google_Service_Calendar($client);
        }
    }

    public function isConnected()
    {
        return $this->service !== null;
    }

    /**
     * List upcoming events
     */
    public function listUpcomingEvents($limit = 10, $calendarId = 'primary')
    {
        if (!$this->isConnected())
            return [];

        $optParams = array(
            'maxResults' => $limit,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );

        try {
            $results = $this->service->events->listEvents($calendarId, $optParams);
            return $results->getItems();
        } catch (Exception $e) {
            return [];
        }
    }

    public function createEvent($summary, $description, $startDateTime, $endDateTime, $calendarId = 'primary')
    {
        if (!$this->isConnected())
            return false;

        $event = new Google_Service_Calendar_Event(array(
            'summary' => $summary,
            'description' => $description,
            'start' => array(
                'dateTime' => $startDateTime,
                'timeZone' => 'Asia/Jakarta',
            ),
            'end' => array(
                'dateTime' => $endDateTime,
                'timeZone' => 'Asia/Jakarta',
            ),
            'reminders' => array(
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ));

        try {
            $event = $this->service->events->insert($calendarId, $event);
            return $event;
        } catch (Exception $e) {
            error_log('Calendar Error: ' . $e->getMessage());
            return false;
        }
    }
}
?>