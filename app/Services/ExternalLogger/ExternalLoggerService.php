<?php

namespace App\Services\ExternalLogger;
use App\Services\ExternalLogger\LogDataModel;

/**
 * Class ExternalLoggerService, implements logic to save logs to external logger
 * @package App\Services\ExternalLogger
 */
final class ExternalLoggerService
{
    private $trace_id;
    private $timestamp;
    private $log_data;

    public function __construct($trace_id = null, $timestamp = null, $log_data = null)
    {
        if (is_null($trace_id)) {
            // TODO Recover trace id from header injected by middleware
            $trace_id = uniqid();
        }
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        if (is_null($log_data)) {
            $log_data = new LogDataModel();
        }

        $this->trace_id = $trace_id;
        $this->timestamp = $timestamp;
        $this->log_data = $log_data;
    }

    public function saveLog()
    {
        // TODO implement call to external logger
    }
}