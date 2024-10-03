<?php
namespace Infinitalk\CloudLogger;

use Google\Cloud\Logging\LoggingClient;

class Logger
{
    private $logger;
    private const LOG_LEVELS = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct(array $configs = [])
    {
        $options = [];
        if (isset($configs['project_id'])) {
            $options['projectId'] = $configs['project_id'];
        }

        $logging = new LoggingClient($options);

        $labels = [
            'service_name' => $configs['service_name'] ?? getenv('K_SERVICE') ?: null,
            'revision_name' => $configs['revision_name'] ?? getenv('K_REVISION') ?: null,
            'location' => $configs['location'] ?? getenv('REGION') ?: null,
        ];
        $logName = $options['log_name'] 
            ?? $options['service_name'] 
            ?? getenv('K_SERVICE') 
            ?? 'cloud_run_logging';

        $this->logger = $logging->logger($logName, [
            'resource' => [
                'type' => 'cloud_run_revision',
                'labels' => $labels
            ]
        ]);
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
        $passLevelCheck = $options['passLevelCheck'] ?? false;

        if (!in_array($severity, self::LOG_LEVELS)) {
            $parts = [$severity, $message];
            $severity = 'WARNING';
            $message = implode(' ', $parts);
        }

        if (!$passLevelCheck && !$this->isShouldLog($severity)) {
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