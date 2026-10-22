<?php
namespace App\Providers;
use App\Repositories\PedidoRepository;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PedidoRepository::class);
    }
}
