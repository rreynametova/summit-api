<?php

namespace App\Http\Middleware;

use App\Services\ExternalLogger\ExternalLoggerService;
use \Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class TrackRequestMiddleware
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        try {
            $this->startTime = microtime(true);
            $traceId = $request->headers->get(InjectLogTraceIDMiddleware::TRACE_ID_HEADER);
            $service = new ExternalLoggerService($traceId, date('Y-m-d H:i:s'));

            $service->createRequestEntry(
                $request->method(),
                $request->path(),
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'referrer' => $request->header('Referer'),
                    'user_id' => Auth::id() ?? null,
                ],
                $request->getquerystring(),
                $request->post(),
                $request->headers->all(),
            );

            $service->saveLog();
        } catch (\Throwable $e) {
            Log::error("No se pudo trackear este request" . $e->getMessage());
        }

        $response = $next($request);
        return $response;
    }

    /**
     * @param Request $request
     * @param SymfonyResponse $response
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $traceId = $request->headers->get(InjectLogTraceIDMiddleware::TRACE_ID_HEADER);
            $service = new ExternalLoggerService($traceId, date('Y-m-d H:i:s'));
            $endTime = microtime(true);
            $responseTime = intval(($endTime - $this->startTime) * 1000);

            $service->createResponseEntry(
                intval($response->getStatusCode()),
                $response->getContent(),
                $responseTime,
            );

            $service->saveLog();
        } catch (\Throwable $e) {
            Log::error("No se pudo trackear este request" . $e->getMessage());
        }
    }
}