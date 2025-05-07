<?php

namespace App\Services\ExternalLogger;

/**
 * Class LogDataModel
 * Defines the structure for log data.
 * @package App\Services\ExternalLogger
 */
class LogDataModel
{
    /**
     * @var string|null A summary of the database transaction.
     */
    public $databaseTransaction = null;

    /**
     * @var string|null Correlation or transaction ID.
     */
    public $logTraceID = null;

    /**
     * @var string|null The specific API endpoint or resource accessed.
     */
    public $endpointAccessed = null;

    /**
     * @var array|null HTTP method and relevant request metadata.
     * Example: ['method' => 'POST', 'ip_address' => '192.168.1.1']
     */
    public $requestDetails = null;

    /**
     * @var array|null Non-sensitive query string parameters.
     * Example: ['page' => 1, 'search' => 'keyword']
     */
    public $queryParameters = null;

    /**
     * @var string|null Relevant request payload data (sensitive information redacted).
     */
    public $payloadDetails = null;

    /**
     * @var array|null Pertinent HTTP headers (sensitive headers excluded).
     * Example: ['User-Agent' => 'MyApp/1.0', 'Referer' => 'https://example.com']
     */
    public $httpHeaders = null;

    /**
     * @var array|null HTTP status codes, error messages, and response payload summaries.
     * Example: ['status_code' => 200, 'message' => 'Success', 'response_summary' => '{ "id": 123 }']
     */
    public $responseDetails = null;

    /**
     * @var float|null Duration of the API call in milliseconds.
     */
    public $responseTime = null;

    /**
     * @var int|null Timestamp of the log event.
     */
    public $timestamp = null;

    /**
     * Constructor for LogDataModel.
     *
     * @param string|null $logTraceID
     * @param int|null $timestamp
     */
    public function __construct(?string $logTraceID = null, ?int $timestamp = null)
    {
        $this->logTraceID = $logTraceID;
        $this->timestamp = $timestamp ?? time();
    }

    /**
     * Converts the model to an array, useful for serialization or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'databaseTransaction' => $this->databaseTransaction,
            'logTraceID' => $this->logTraceID,
            'endpointAccessed' => $this->endpointAccessed,
            'requestDetails' => $this->requestDetails,
            'queryParameters' => $this->queryParameters,
            'payloadDetails' => $this->payloadDetails,
            'httpHeaders' => $this->httpHeaders,
            'responseDetails' => $this->responseDetails,
            'responseTime' => $this->responseTime,
            'timestamp' => $this->timestamp,
        ];
    }
}