<?php

namespace App\Services\ExternalLogger\Model;

/**
 * Class LogDataModel
 * Defines the structure for log data.
 * @package App\Services\ExternalLogger
 */
class SqlModel extends LogDataModel
{

    /**
     * @var string|null HTTP status codes, error messages, and response payload summaries.
     * Example: ['status_code' => 200, 'message' => 'Success', 'response_summary' => '{ "id": 123 }']
     */
    public $rawSql = null;

    /**
     * @var array|null HTTP status codes, error messages, and response payload summaries.
     * Example: ['status_code' => 200, 'message' => 'Success', 'response_summary' => '{ "id": 123 }']
     */
    public $parameters = null;

    /**
     * @var string|null Duration of the API call in milliseconds.
     */
    public $executedSqlReadable = null;

    /**
     * @var int|null Duration of the API call in milliseconds.
     */
    public $executionTime = null;


    /**
     * Converts the model to an array, useful for serialization or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'rawSql' => $this->rawSql,
            'parameters' => $this->parameters,
            'executedSqlReadable' => $this->executedSqlReadable,
            'executionTime' => $this->executionTime,
        ];
    }
}