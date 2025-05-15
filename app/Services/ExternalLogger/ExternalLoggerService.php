<?php

namespace App\Services\ExternalLogger;
use App\Services\ExternalLogger\Model\LogDataModel;
use App\Services\ExternalLogger\Model\RequestModel;

/**
 * Class ExternalLoggerService, implements logic to save logs to external logger
 * @package App\Services\ExternalLogger
 */
final class ExternalLoggerService
{
    private $trace_id;
    private $timestamp;
    private $log_data_model; // Changed from $log_data to $log_data_model for clarity

    public function __construct(string $trace_id = null, $timestamp = null, LogDataModel $log_data_model = null)
    {
        if (is_null($trace_id)) {
            // TODO Recover trace id from header injected by middleware
            $trace_id = uniqid();
        }
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        $this->trace_id = $trace_id;
        $this->timestamp = $timestamp;
        $this->log_data_model = $log_data_model;
    }

    /**
     * Creates and populates the log data model with the provided information.
     *
     * @param string|null $databaseTransaction Summary of the Doctrine ORM transaction.
     * @param string|null $endpointAccessed The specific API endpoint accessed.
     * @param array|null $requestDetails HTTP method and relevant request metadata.
     * Example: ['method' => 'GET', 'ip_address' => '127.0.0.1']
     * @param array|null $queryParameters Non-sensitive query string parameters.
     * Example: ['filter' => 'active']
     * @param string|null $payloadDetails Redacted request payload.
     * @param array|null $httpHeaders Pertinent HTTP headers (non-sensitive).
     * Example: ['User-Agent' => 'ClientApp/1.0']
     * @param array|null $responseDetails HTTP status code, error messages, response summary.
     * Example: ['status_code' => 200, 'message' => 'OK']
     * @param float|null $responseTime Duration of the API call in milliseconds.
     *
     * @return self Returns the service instance for method chaining if desired.
     */
    public function createRequestEntry(
        ?string $method,
        ?string $endpointAccessed,
        ?array $requestDetails,
        ?array $queryParameters,
        ?array $payloadDetails,
        ?array $httpHeaders
    ): self
    {
        $this->log_data_model = new RequestModel();

        // Populate the LogDataModel with the provided data
        $this->log_data_model->method = $method;
        $this->log_data_model->endpointAccessed = $endpointAccessed;
        $this->log_data_model->requestDetails = $requestDetails;
        $this->log_data_model->queryParameters = $queryParameters;
        $this->log_data_model->payloadDetails = $payloadDetails;
        $this->log_data_model->httpHeaders = $httpHeaders;

        return $this;
    }

    public function saveLog()
    {
        // TODO implement call to external logger
    }
}