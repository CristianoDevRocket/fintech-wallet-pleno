<template>
  <div class="page">
    <AppTopbar />

    <main class="container">
      <h2>Histórico de transações</h2>

      <form class="filters" @submit.prevent="applyFilters">
        <select v-model="filters.type">
          <option value="">Todos os tipos</option>
          <option value="credit">Crédito</option>
          <option value="debit">Débito</option>
        </select>
        <input v-model="filters.start_date" type="date" />
        <input v-model="filters.end_date" type="date" />
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <button type="button" @click="clearFilters" class="btn btn-secondary">Limpar</button>
      </form>

      <div v-if="loading" class="loading">Carregando...</div>

      <template v-else>
        <div class="transactions-card">
          <div class="table-wrapper">
            <table v-if="transactions.length">
              <thead>
                <tr>
                  <th>Data/Hora</th>
                  <th>Tipo</th>
                  <th>Valor</th>
                  <th>Saldo após</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in transactions" :key="tx.id">
                  <td>{{ formatDate(tx.created_at) }}</td>
                  <td><span :class="['badge', tx.type]">{{ tx.type === 'credit' ? 'Crédito' : 'Débito' }}</span></td>
                  <td>{{ formatCurrency(tx.amount) }}</td>
                  <td>{{ formatCurrency(tx.balance_after) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="!transactions.length" class="empty">Nenhuma transação encontrada.</p>
        </div>

        <div v-if="pagination.last_page > 1" class="pagination">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="goToPage(page)"
            :class="['btn-page', { active: page === pagination.current_page }]"
          >
            {{ page }}
          </button>
        </div>
      </template>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWalletStore } from '@/stores/wallet'
import AppTopbar from '@/components/AppTopbar.vue'

const router = useRouter()
const walletStore = useWalletStore()

const loading = ref(true)
const transactions = ref([])
const pagination = ref({})
const filters = ref({ type: '', start_date: '', end_date: '' })

onMounted(() => fetch())

async function fetch(page = 1) {
  loading.value = true
  const params = { page, ...Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== '')) }
  await walletStore.fetchTransactions(params)
  transactions.value = walletStore.transactions
  pagination.value = walletStore.pagination
  loading.value = false
}

function applyFilters() { fetch(1) }

function clearFilters() {
  filters.value = { type: '', start_date: '', end_date: '' }
  fetch(1)
}

function goToPage(page) { fetch(page) }

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
}

function formatDate(iso) {
  return new Date(iso).toLocaleString('pt-BR')
}
</script>
