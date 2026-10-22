<?php
namespace App\Repositories;
use Illuminate\Support\Str;
class PedidoRepository
{
    private array $pedidos = [];

    public function create(array $dados): array
    {
        $pedido = [
            'id' => (string) Str::uuid(),
            'cliente' => $dados['cliente'],
            'descricao' => $dados['descricao'],
            'valor' => number_format((float) $dados['valor'], 2, '.', ''),
            'status' => 'CRIADO',
        ];
        $this->pedidos[$pedido['id']] = $pedido;
        return $pedido;
    }

    public function find(string $id): ?array
    {
        return $this->pedidos[$id] ?? null;
    }
}
