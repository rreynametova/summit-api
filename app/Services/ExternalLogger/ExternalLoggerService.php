<?php

namespace App\Services\ExternalLogger;
use App\Http\Middleware\InjectLogTraceIDMiddleware;
use App\Services\ExternalLogger\Model\LogDataModel;
use App\Services\ExternalLogger\Model\RequestModel;
use App\Services\ExternalLogger\Model\ResponseModel;
use App\Services\ExternalLogger\Model\SqlModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class ExternalLoggerService, implements logic to save logs to external logger
 * @package App\Services\ExternalLogger
 */
final class ExternalLoggerService
{
    private $trace_id;
    private $type;
    private $timestamp;
    private $log_data_model;
    private $microservice_url;

    public function __construct(string $trace_id = null, $timestamp = null, LogDataModel $log_data_model = null)
    {
        $this->microservice_url = env('SIMPLE_LOG_MICROSERVICE_URL', 'http://host.docker.internal:80/api/logs');

        if (is_null($trace_id)) {
            $trace_id = request()->header(InjectLogTraceIDMiddleware::TRACE_ID_HEADER) ?? uniqid();
        }
        if (is_null($timestamp)) {
            $timestamp = date('Y-m-d H:i:s');;
        }

        $this->trace_id = $trace_id;
        $this->timestamp = $timestamp;
        $this->log_data_model = $log_data_model;
    }

    /**
     * Creates and populates the log data model with the provided information.
     *
     * @param string|null $method
     * @param string|null $endpointAccessed The specific API endpoint accessed.
     * @param array|null $requestDetails HTTP method and relevant request metadata.
     * Example: ['method' => 'GET', 'ip_address' => '127.0.0.1']
     * @param array|null $queryParameters Non-sensitive query string parameters.
     * Example: ['filter' => 'active']
     * @param array|null $payloadDetails Redacted request payload.
     * @param array|null $httpHeaders Pertinent HTTP headers (non-sensitive).
     * Example: ['User-Agent' => 'ClientApp/1.0']
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
        $this->type = 'request';

        // Populate the LogDataModel with the provided data
        $this->log_data_model->method = $method;
        $this->log_data_model->endpointAccessed = $endpointAccessed;
        $this->log_data_model->requestDetails = $requestDetails;
        $this->log_data_model->queryParameters = $queryParameters;
        $this->log_data_model->payloadDetails = $payloadDetails;
        $this->log_data_model->httpHeaders = $httpHeaders;

        return $this;
    }

    public function createResponseEntry(
        ?int $responseCode,
        ?string $responseBody,
        ?int $responseTime
    )
    {
        $this->log_data_model = new ResponseModel();
        $this->type = 'response';

        // Populate the LogDataModel with the provided data
        $this->log_data_model->responseCode = $responseCode;;
        $this->log_data_model->responseBody = $responseBody;
        $this->log_data_model->responseTime = $responseTime;

        return $this;
    }

    public function createSqlEntry(
        ?string $rawSql,
        ?array $parameters,
        ?string $executedSqlReadable,
        ?int $executionTime
    )
    {
        $this->log_data_model = new SqlModel();
        $this->type = 'sql_query';

        // Populate the LogDataModel with the provided data
        $this->log_data_model->rawSql = $rawSql;;
        $this->log_data_model->parameters = $parameters;
        $this->log_data_model->executedSqlReadable = $executedSqlReadable;
        $this->log_data_model->executionTime = $executionTime;

        return $this;
    }

    public function saveLog()
    {
        $body = [
            'trace_id' => $this->trace_id,
            'timestamp' => $this->timestamp,
            'type' => $this->type,
            'log_data' => $this->log_data_model->toArray()
        ];
        $headers = ['Accept' => 'application/json'];

        try {
            Http::withHeaders($headers)->post($this->microservice_url, $body);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Error de conexión (host.docker.internal) al intentar enviar log al microservicio.', [
                'error_message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción general (host.docker.internal) al enviar log al microservicio.', [
                'error_message' => $e->getMessage(),
            ]);
        }

    }
}