<?php
namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class Frontend
{
    public function response(ServerRequestInterface $request): ?Response
    {
        $path = rawurldecode($request->getUri()->getPath());
        $documentation = $path === '/docs' || str_starts_with($path, '/docs/');
        if (! in_array($request->getMethod(), ['GET', 'HEAD']) || ($path !== '/' && ! str_starts_with($path, '/assets/') && ! $documentation)) {
            return null;
        }

        $root = realpath(base_path('frontend/dist'));
        if ($root === false) {
            return new Response(503, ['Content-Type' => 'text/plain; charset=utf-8'], 'Compile o frontend com: npm ci --prefix frontend && npm run build --prefix frontend');
        }

        $relativePath = match ($path) {
            '/' => '/index.html',
            '/docs', '/docs/' => '/docs/index.html',
            default => $path,
        };
        $file = realpath($root.$relativePath);
        if ($file === false || ! str_starts_with($file, $root.DIRECTORY_SEPARATOR) || ! is_file($file)) {
            return new Response(404, ['Content-Type' => 'text/plain; charset=utf-8'], 'Arquivo não encontrado.');
        }

        $contentType = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'html' => 'text/html; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        return new Response(200, ['Content-Type' => $contentType, 'X-Content-Type-Options' => 'nosniff'], $request->getMethod() === 'HEAD' ? '' : file_get_contents($file));
    }
}
