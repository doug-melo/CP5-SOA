<?php
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;
Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
