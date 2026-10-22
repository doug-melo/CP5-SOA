<?php
namespace Tests\Feature;
use Tests\TestCase;
class PedidoTest extends TestCase
{
    private function payload(): array
    {
        return ['cliente' => 'Douglas Melo', 'descricao' => 'Teclado mecânico', 'valor' => 199.90];
    }

    public function test_cria_e_consulta_o_mesmo_pedido(): void
    {
        $response = $this->postJson('/api/pedidos', $this->payload());
        $response->assertCreated()->assertJsonPath('status', 'CRIADO')
            ->assertJsonPath('valor', '199.90')->assertJsonStructure(['id', 'cliente', 'descricao', 'valor', 'status'])
            ->assertHeader('X-Request-Id');
        $id = $response->json('id');
        $response->assertHeader('Location', '/api/pedidos/'.$id);
        $this->getJson('/api/pedidos/'.$id)->assertOk()->assertExactJson($response->json());
    }

    public function test_pedido_inexistente_retorna_404(): void
    {
        $this->getJson('/api/pedidos/inexistente')->assertNotFound()
            ->assertJsonPath('message', 'Pedido não encontrado.');
    }

    public function test_dados_invalidos_retornam_422(): void
    {
        $this->postJson('/api/pedidos', ['valor' => -1])->assertUnprocessable()
            ->assertJsonValidationErrors(['cliente', 'descricao', 'valor']);
    }

    public function test_pedidos_possuem_ids_distintos(): void
    {
        $a = $this->postJson('/api/pedidos', $this->payload())->assertCreated()->json('id');
        $b = $this->postJson('/api/pedidos', $this->payload())->assertCreated()->json('id');
        $this->assertNotSame($a, $b);
        $this->getJson('/api/pedidos/'.$a)->assertOk()->assertJsonPath('id', $a);
    }

    public function test_json_malformado_retorna_400(): void
    {
        $this->call('POST', '/api/pedidos', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{')
            ->assertStatus(400)->assertJsonPath('message', 'JSON inválido.');
    }

    public function test_formulario_retorna_415(): void
    {
        $this->post('/api/pedidos', $this->payload())->assertStatus(415);
    }
}
