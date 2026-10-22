<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
class RequestLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);
        Log::log($response->getStatusCode() >= 400 ? 'warning' : 'info', 'pedido.http', [
            'requestId' => $requestId,
            'pedidoId' => $request->attributes->get('pedidoId'),
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
        ]);
        return $response;
    }
}
