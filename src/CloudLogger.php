<?php
require_once 'vendor/autoload.php';

use Google\Cloud\Logging\LoggingClient;

class CloudLogger
{
    private $logger;

    public function __construct(string $projectId)
    {
        $this->logger = new LoggingClient([
            'projectId' => $projectId,
        ]);
    }

    public function log($message, array $context = [])
    {
        $logName = 'your_log_name'; // Replace with your actual log name
        $resourceType = 'global'; // Replace with your actual resource type
        $resourceLabels = ['project_id' => $this->projectId]; // Replace with your actual resource labels

        $entry = $this->logger->entry($message, [
            'logName' => $logName,
            'resource' => [
                'type' => $resourceType,
                'labels' => $resourceLabels,
            ],
        ]);

        $this->logger->write($entry);
    }
}