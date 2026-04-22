<?php

namespace Astroinfo\App\Log;

use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
 * Technical Class to manage error collection
 */
class ErrorCollector
{
    private Logger $logger;
    private TestHandler $memoryHandler;
    private static ?ErrorCollector $instance = null;

    public static function initErrorHandling(): void
    {
        self::getInstance(); // Ensure the singleton instance is created

        set_error_handler(function ($severity, $message, $file, $line)
        {
            self::logWarning("PHP Warning: {$message} in {$file} on line {$line}");
        });

        set_exception_handler(function ($exception)
        {
            self::logCriticalError("Uncaught Exception: " . $exception->getMessage());
        });

        register_shutdown_function(function ()
        {
            $error = error_get_last();
            if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR))
            {
                self::logCriticalError("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
            }
        });
    }

    public function __construct(string $channelName = 'app_errors')
    {
        $this->logger = new Logger($channelName);

        // This handler stores logs in an internal array
        $this->memoryHandler = new TestHandler();
        $this->logger->pushHandler($this->memoryHandler);
    }

    public static function logWarning(string $message, array $context = []): void
    {
        self::getInstance()->logger->warning($message, $context);
    }

    public static function logCriticalError(string $message, array $context = []): void
    {
        self::getInstance()->logger->error($message, $context);
    }

    /**
     * Retrieves all logs and converts them to a JSON string
     */
    public static function getLogsAsJson(): string
    {
        $records = self::getInstance()->memoryHandler->getRecords();
        return json_encode($records, JSON_PRETTY_PRINT);
    }

    public static function getInstance(): ErrorCollector
    {
        if (self::$instance === null)
        {
            self::$instance = new ErrorCollector();
        }
        return self::$instance;
    }
}
