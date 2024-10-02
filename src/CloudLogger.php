<?php
namespace Infinitalk\CloudLogger;

use Google\Cloud\Logging\LoggingClient;

class CloudLogger
{
    private $logger;
    private $logName;
    private const LOG_LEVELS = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct($logName, array $resourceOptions = [], $projectId = null)
    {
        $options = [];
        if ($projectId !== null) {
            $options['projectId'] = $projectId;
        }

        $logging = new LoggingClient($options);

        $resourceLabels = [
            'service_name' => $resourceOptions['service_name'] ?? getenv('K_SERVICE') ?: null,
            'revision_name' => $resourceOptions['revision_name'] ?? getenv('K_REVISION') ?: null,
            'location' => $resourceOptions['location'] ?? getenv('REGION') ?: null,
        ];

        $this->logger = $logging->logger($logName, [
            'resource' => [
                'type' => 'cloud_run_revision',
                'labels' => $resourceLabels
            ]
        ]);
        $this->logName = $logName;
    }

    private function isShouldLog($level)
    {
        $logLevel = getenv('LOG_LEVEL') ?: 'INFO';
        $levelIndex = array_search(strtoupper($level), self::LOG_LEVELS);
        $currentLevelIndex = array_search(strtoupper($logLevel), self::LOG_LEVELS);
        return $levelIndex !== false && $levelIndex >= $currentLevelIndex;
    }

    public function log($severity, $message, array $data = [], array $options = [])
    {
        $bypassLevelCheck = $options['bypassLevelCheck'] ?? false;

        if (!$bypassLevelCheck && !$this->isShouldLog($severity)) {
            return;
        }

        $entryData = array_merge(['message' => $message], $data);

        $entry = $this->logger->entry($entryData, [
            'severity' => $severity
        ]);
        $this->logger->write($entry);
    }

    public function emergency($message, array $data = [], array $options = [])
    {
        $this->log('EMERGENCY', $message, $data, $options);
    }

    public function alert($message, array $data = [], array $options = [])
    {
        $this->log('ALERT', $message, $data, $options);
    }

    public function critical($message, array $data = [], array $options = [])
    {
        $this->log('CRITICAL', $message, $data, $options);
    }

    public function error($message, array $data = [], array $options = [])
    {
        $this->log('ERROR', $message, $data, $options);
    }

    public function warning($message, array $data = [], array $options = [])
    {
        $this->log('WARNING', $message, $data, $options);
    }

    public function notice($message, array $data = [], array $options = [])
    {
        $this->log('NOTICE', $message, $data, $options);
    }

    public function info($message, array $data = [], array $options = [])
    {
        $this->log('INFO', $message, $data, $options);
    }

    public function debug($message, array $data = [], array $options = [])
    {
        $this->log('DEBUG', $message, $data, $options);
    }
}