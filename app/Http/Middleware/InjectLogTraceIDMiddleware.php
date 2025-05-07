<?php

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class InjectLogTraceIDMiddleware
{
    const TRACE_ID_HEADER = 'X-Log-Trace-ID';

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $traceId = $request->headers->get(self::TRACE_ID_HEADER);

        if (empty($traceId)) {
            try {
                $traceId = uniqid();
            } catch (\Throwable $e) {
                $traceId = uniqid('corr_', true);
            }
        }

        $response = $next($request);
        $response->headers->set(self::TRACE_ID_HEADER, $traceId);

        return $response;
    }
}