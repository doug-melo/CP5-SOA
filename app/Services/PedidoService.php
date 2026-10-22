<?php
namespace App\Services;
use App\Repositories\PedidoRepository;
class PedidoService
{
    public function __construct(private PedidoRepository $repository) {}

    public function criar(array $dados): array
    {
        // O status inicial e o identificador são definidos pelo servidor.
        return $this->repository->create($dados);
    }

    public function consultar(string $id): ?array
    {
        return $this->repository->find($id);
    }
}
