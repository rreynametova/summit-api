<?php

namespace App\Services\ExternalLogger\Model;

/**
 * Class LogDataModel
 * Defines the structure for log data.
 * @package App\Services\ExternalLogger
 */
class RequestModel extends LogDataModel
{
    /**
     * @var string|null The specific API method.
     */
    public $method = null;

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
     * @var array|null Relevant request payload data (sensitive information redacted).
     */
    public $payloadDetails = null;

    /**
     * @var array|null Pertinent HTTP headers (sensitive headers excluded).
     * Example: ['User-Agent' => 'MyApp/1.0', 'Referer' => 'https://example.com']
     */
    public $httpHeaders = null;

    /**
     * Converts the model to an array, useful for serialization or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'endpointAccessed' => $this->endpointAccessed,
            'requestDetails' => $this->requestDetails,
            'queryParameters' => $this->queryParameters,
            'payloadDetails' => $this->payloadDetails,
            'httpHeaders' => $this->httpHeaders,
        ];
    }
}