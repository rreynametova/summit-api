<?php

namespace App\Services\ExternalLogger\Logger;

use App\Http\Middleware\InjectLogTraceIDMiddleware;
use App\Services\ExternalLogger\ExternalLoggerService;
use Doctrine\DBAL\Logging\SQLLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Log as LaravelLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExternalSqlLogger implements SQLLogger
{
    protected $startTime;
    protected $currentQuerySql;
    protected $currentQueryParams;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->startTime = microtime(true);
        $this->currentQuerySql = $sql;
        $this->currentQueryParams = $params;
    }

    public function stopQuery()
    {
        $executionTimeMs = (microtime(true) - $this->startTime) * 1000;
        $traceId = $this->request->header(InjectLogTraceIDMiddleware::TRACE_ID_HEADER, $this->request->attributes->get('trace_id', Str::uuid()->toString()));

        $executedSqlReadable = $this->currentQuerySql;
        if (!empty($this->currentQueryParams)) {
            $tempSql = $this->currentQuerySql;
            foreach ($this->currentQueryParams as $param) {
                $value = $param;
                if (is_string($value)) {
                    $value = "'" . addslashes($value) . "'"; // Escapado simple
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif (is_null($value)) {
                    $value = 'NULL';
                }
                $tempSql = Str::replaceFirst('?', (string) $value, $tempSql);
            }
            $executedSqlReadable = $tempSql;
        }

        try {
            $traceId = $this->request->headers->get(InjectLogTraceIDMiddleware::TRACE_ID_HEADER);
            $service = new ExternalLoggerService($traceId, date('Y-m-d H:i:s'));

            $service->createSqlEntry(
                $this->currentQuerySql,
                $this->currentQueryParams,
                $executedSqlReadable,
                round($executionTimeMs, 0)
            );

            $service->saveLog();
        } catch (\Throwable $e) {
            Log::error("No se pudo trackear este request" . $e->getMessage());
        }

        // Limpia para la siguiente query
        $this->currentQuerySql = null;
        $this->currentQueryParams = null;
    }
}