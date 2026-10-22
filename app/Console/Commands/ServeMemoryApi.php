<?php
namespace App\Console\Commands;
use App\Http\Frontend;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
class ServeMemoryApi extends Command
{
    protected $signature = 'api:serve {--host=127.0.0.1} {--port=8000}';
    protected $description = 'Executa a API com armazenamento em memória no mesmo processo';

    public function handle(): int
    {
        $kernel = $this->laravel->make(Kernel::class);
        $kernel->bootstrap();
        $server = new HttpServer(function (ServerRequestInterface $incoming) use ($kernel) {
            $frontend = (new Frontend)->response($incoming);
            if ($frontend !== null) {
                return $frontend;
            }
            $serverParams = ['REMOTE_ADDR' => $incoming->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1'];
            foreach ($incoming->getHeaders() as $name => $values) {
                $key = strtoupper(str_replace('-', '_', $name));
                $serverParams[in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH']) ? $key : 'HTTP_'.$key] = implode(', ', $values);
            }
            // Cada chamada recebe uma nova Request, mantendo somente o repositório singleton.
            $request = Request::create((string) $incoming->getUri(), $incoming->getMethod(), [], [], [], $serverParams, (string) $incoming->getBody());
            $response = $kernel->handle($request);
            $result = new Response($response->getStatusCode(), $response->headers->all(), $response->getContent());
            $kernel->terminate($request, $response);
            $this->laravel->forgetScopedInstances();
            return $result;
        });
        $address = $this->option('host').':'.$this->option('port');
        $server->listen(new SocketServer($address));
        $this->info('API em http://'.$address.' - pedidos apagados ao encerrar (Ctrl+C).');
        Loop::run();
        return self::SUCCESS;
    }
}
