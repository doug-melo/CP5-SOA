<?php
namespace App\Http\Controllers;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class PedidoController
{
    public function __construct(private PedidoService $service) {}

    public function store(Request $request): JsonResponse
    {
        if (! $request->isJson()) {
            return response()->json(['message' => 'Utilize Content-Type: application/json.'], 415);
        }
        try {
            json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return response()->json(['message' => 'JSON inválido.'], 400);
        }
        $dados = $request->validate([
            'cliente' => ['required', 'string', 'max:120'],
            'descricao' => ['required', 'string', 'max:500'],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:999999999.99', 'decimal:0,2'],
        ]);
        $pedido = $this->service->criar($dados);
        $request->attributes->set('pedidoId', $pedido['id']);
        return response()->json($pedido, 201)
            ->header('Location', '/api/pedidos/'.$pedido['id']);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $request->attributes->set('pedidoId', $id);
        $pedido = $this->service->consultar($id);
        return $pedido
            ? response()->json($pedido)
            : response()->json(['message' => 'Pedido não encontrado.'], 404);
    }
}
