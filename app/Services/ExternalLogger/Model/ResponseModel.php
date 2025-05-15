<?php

namespace App\Services\ExternalLogger\Model;

/**
 * Class LogDataModel
 * Defines the structure for log data.
 * @package App\Services\ExternalLogger
 */
class ResponseModel extends LogDataModel
{
    /**
     * @var int|null HTTP status codes, error messages, and response payload summaries.
     * Example: ['status_code' => 200, 'message' => 'Success', 'response_summary' => '{ "id": 123 }']
     */
    public $responseCode = null;

    /**
     * @var string|null HTTP status codes, error messages, and response payload summaries.
     * Example: ['status_code' => 200, 'message' => 'Success', 'response_summary' => '{ "id": 123 }']
     */
    public $responseBody = null;

    /**
     * @var int|null Duration of the API call in milliseconds.
     */
    public $responseTime = null;


    /**
     * Converts the model to an array, useful for serialization or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'responseDetails' => $this->responseCode,
            'responseBody' => $this->responseBody,
            'responseTime' => $this->responseTime,
        ];
    }
}