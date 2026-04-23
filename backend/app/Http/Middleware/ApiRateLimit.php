<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRateLimit
{
    /**
     * The maximum number of requests allowed within the time window.
     *
     * @var int
     */
    protected $maxRequests = 100;

    /**
     * The time window for the rate limit in seconds.
     *
     * @var int
     */
    protected $timeWindow = 60;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Logic to apply rate limiting
        $ip = $request->ip();
        $key = 'api_rate_limit:' . $ip;
        $requests = cache()->get($key, 0);

        if ($requests >= $this->maxRequests) {
            return response()->json(['message' => 'Too Many Requests'], 429);
        }

        cache()->increment($key);
        cache()->put($key, $requests + 1, $this->timeWindow);

        return $next($request);
    }
}