<script setup>
import { reactive, ref } from "vue";

const form = reactive({ cliente: "", descricao: "", valor: "" });
const creating = ref(false);
const searching = ref(false);
const fieldErrors = ref({});
const createError = ref("");
const searchError = ref("");
const createdOrder = ref(null);
const foundOrder = ref(null);
const searchId = ref("");
const copied = ref(false);

const money = (value) =>
  new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(
    Number(value),
  );

async function api(path, options = {}) {
  let response;
  try {
    response = await fetch(path, {
      ...options,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
  } catch {
    throw new Error(
      "Não foi possível conectar à API. Verifique se o servidor está em execução.",
    );
  }
  let data;
  try {
    data = await response.json();
  } catch {
    throw new Error("A API retornou uma resposta inesperada. Tente novamente.");
  }
  if (!response.ok) {
    const error = new Error(
      response.status === 422
        ? "Revise os campos indicados e tente novamente."
        : data.message || "Não foi possível concluir a operação.",
    );
    error.fields = data.errors || {};
    throw error;
  }
  return data;
}

async function createOrder() {
  creating.value = true;
  createError.value = "";
  fieldErrors.value = {};
  createdOrder.value = null;
  try {
    const order = await api("/api/pedidos", {
      method: "POST",
      body: JSON.stringify(form),
    });
    createdOrder.value = order;
    searchId.value = order.id;
    copied.value = false;
    form.cliente = "";
    form.descricao = "";
    form.valor = "";
  } catch (error) {
    createError.value = error.message;
    fieldErrors.value = error.fields || {};
  } finally {
    creating.value = false;
  }
}

async function findOrder() {
  foundOrder.value = null;
  searchError.value = "";
  if (!searchId.value.trim()) {
    searchError.value = "Informe o identificador do pedido.";
    return;
  }
  searching.value = true;
  try {
    foundOrder.value = await api(
      `/api/pedidos/${encodeURIComponent(searchId.value.trim())}`,
    );
  } catch (error) {
    searchError.value = error.message;
  } finally {
    searching.value = false;
  }
}

async function copyId() {
  try {
    await navigator.clipboard.writeText(createdOrder.value.id);
    copied.value = true;
  } catch {
    copied.value = false;
  }
}
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <a class="brand" href="/" aria-label="Página inicial de pedidos"
        ><span class="brand-mark">P<span>·</span></span
        ><span>pedidos<span class="brand-dot">.</span></span></a
      >
      <p class="nav-label">WORKSPACE</p>
      <div class="nav-item">
        <span aria-hidden="true">▦</span> Gerenciar pedidos
        <span class="nav-dot"></span>
      </div>
      <div class="sidebar-bottom">
        <span class="avatar">DM</span>
        <div>
          <strong>Douglas Melo</strong><small>RM556439 · CP5 SOA</small>
        </div>
      </div>
    </aside>

    <div class="workspace">
      <header class="topbar">
        <span
          >Aplicação de pedidos
          <span class="breadcrumb">/ Visão geral</span></span
        >
        <div class="topbar-links">
          <a class="docs-link" href="/docs">Swagger / API ↗</a
          ><span class="stack-tag">Laravel + Vue 3</span>
        </div>
      </header>
      <main>
        <div class="page-heading">
          <div>
            <p class="eyebrow">GERENCIAMENTO</p>
            <h1>Seus pedidos, em um só lugar.</h1>
            <p class="lead">
              Crie um pedido ou consulte os detalhes pelo identificador.
            </p>
          </div>
          <span class="memory-badge"
            ><span></span> Armazenamento em memória</span
          >
        </div>
        <div class="notice">
          <span class="notice-icon" aria-hidden="true">i</span>
          <p>
            Os pedidos ficam disponíveis enquanto o servidor estiver em
            execução. Ao reiniciá-lo, os dados serão apagados.
          </p>
        </div>

        <div class="panels">
          <section class="panel" aria-labelledby="create-title">
            <div class="panel-heading">
              <span class="section-icon" aria-hidden="true">＋</span>
              <div>
                <h2 id="create-title">Novo pedido</h2>
                <p>Preencha as informações para começar.</p>
              </div>
            </div>
            <form @submit.prevent="createOrder">
              <div v-if="createError" class="alert error" role="alert">
                {{ createError }}
              </div>
              <div class="field">
                <label for="cliente">Nome do cliente <span>*</span></label
                ><input
                  id="cliente"
                  v-model="form.cliente"
                  placeholder="Ex.: Douglas dos Santos Melo"
                  maxlength="120"
                  required
                  :aria-invalid="Boolean(fieldErrors.cliente)"
                  :aria-describedby="
                    fieldErrors.cliente ? 'cliente-error' : undefined
                  "
                /><small
                  v-if="fieldErrors.cliente"
                  id="cliente-error"
                  class="field-error"
                  >{{ fieldErrors.cliente[0] }}</small
                >
              </div>
              <div class="field">
                <label for="descricao">Descrição do pedido <span>*</span></label
                ><textarea
                  id="descricao"
                  v-model="form.descricao"
                  rows="3"
                  placeholder="O que o cliente está pedindo?"
                  maxlength="500"
                  required
                  :aria-invalid="Boolean(fieldErrors.descricao)"
                  :aria-describedby="
                    fieldErrors.descricao ? 'descricao-error' : undefined
                  "
                ></textarea
                ><small
                  v-if="fieldErrors.descricao"
                  id="descricao-error"
                  class="field-error"
                  >{{ fieldErrors.descricao[0] }}</small
                >
              </div>
              <div class="field">
                <label for="valor">Valor do pedido <span>*</span></label>
                <div class="money-input">
                  <span>R$</span
                  ><input
                    id="valor"
                    v-model="form.valor"
                    type="number"
                    min="0.01"
                    max="999999999.99"
                    step="0.01"
                    placeholder="0,00"
                    required
                    :aria-invalid="Boolean(fieldErrors.valor)"
                    :aria-describedby="
                      fieldErrors.valor ? 'valor-error' : undefined
                    "
                  />
                </div>
                <small
                  v-if="fieldErrors.valor"
                  id="valor-error"
                  class="field-error"
                  >{{ fieldErrors.valor[0] }}</small
                >
              </div>
              <div class="form-footer">
                <span>Todos os campos são obrigatórios.</span
                ><button class="primary" type="submit" :disabled="creating">
                  {{ creating ? "Criando…" : "Criar pedido" }}
                  <span aria-hidden="true">↗</span>
                </button>
              </div>
            </form>
            <div v-if="createdOrder" class="success-result" role="status">
              <strong>Pedido criado com sucesso.</strong>
              <p>Guarde o identificador para consultar este pedido.</p>
              <div class="id-row">
                <code>{{ createdOrder.id }}</code
                ><button type="button" class="text-button" @click="copyId">
                  {{ copied ? "Copiado" : "Copiar ID" }}
                </button>
              </div>
              <button class="text-button" type="button" @click="findOrder">
                Consultar este pedido →
              </button>
            </div>
          </section>

          <section class="panel" aria-labelledby="search-title">
            <div class="panel-heading">
              <span class="section-icon" aria-hidden="true">⌕</span>
              <div>
                <h2 id="search-title">Consultar pedido</h2>
                <p>Encontre os detalhes de um pedido existente.</p>
              </div>
            </div>
            <form class="search-form" @submit.prevent="findOrder">
              <label for="pedido-id">Identificador do pedido</label>
              <div class="search-row">
                <input
                  id="pedido-id"
                  v-model="searchId"
                  placeholder="Cole o ID do pedido"
                  required
                  autocomplete="off"
                /><button class="secondary" type="submit" :disabled="searching">
                  {{ searching ? "Buscando…" : "Consultar" }}
                </button>
              </div>
            </form>
            <div
              v-if="searchError"
              class="alert error search-feedback"
              role="alert"
            >
              {{ searchError }}
            </div>
            <div v-else-if="foundOrder" class="order-result" aria-live="polite">
              <div class="order-header">
                <span class="eyebrow">DETALHES DO PEDIDO</span
                ><span class="status">{{ foundOrder.status }}</span>
              </div>
              <h3>{{ foundOrder.cliente }}</h3>
              <p class="order-description">{{ foundOrder.descricao }}</p>
              <dl>
                <div>
                  <dt>Valor total</dt>
                  <dd class="order-value">{{ money(foundOrder.valor) }}</dd>
                </div>
                <div>
                  <dt>Identificador</dt>
                  <dd class="order-id">{{ foundOrder.id }}</dd>
                </div>
              </dl>
            </div>
            <div v-else class="empty-state">
              <span class="empty-icon" aria-hidden="true">⌕</span>
              <h3>Qual pedido você procura?</h3>
              <p>
                Informe o identificador acima para visualizar<br
                  class="desktop-break"
                />
                o cliente, a descrição, o valor e o status.
              </p>
            </div>
          </section>
        </div>
        <footer class="page-footer">
          <span>CP5 · Arquitetura Orientada a Serviços</span
          ><span>Douglas dos Santos Melo · RM556439</span>
        </footer>
      </main>
    </div>
  </div>
</template>
