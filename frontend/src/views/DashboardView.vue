<template>
  <div class="page">
    <AppTopbar />

    <main class="container">
      <div v-if="loading" class="loading">Carregando...</div>

      <template v-else>
        <section class="balance-card">
          <p class="balance-label">Saldo disponível</p>
          <p class="balance-value">{{ formatCurrency(dashboard?.wallet?.balance) }}</p>
        </section>

        <section class="summary-cards">
          <div class="summary-card credit">
            <p>Depositado no mês</p>
            <strong>{{ formatCurrency(dashboard?.monthly_deposited) }}</strong>
          </div>
          <div class="summary-card debit">
            <p>Sacado no mês</p>
            <strong>{{ formatCurrency(dashboard?.monthly_withdrawn) }}</strong>
          </div>
        </section>

        <section class="operations">
          <div class="operation-form">
            <h3>Depositar</h3>
            <div v-if="depositMsg.text" :class="['alert', depositMsg.type === 'success' ? 'alert-success' : 'alert-error']">
              {{ depositMsg.text }}
            </div>
            <form @submit.prevent="handleDeposit">
              <input v-model="depositAmount" type="number" step="0.01" min="0.01" placeholder="R$ 0,00" required />
              <button :disabled="depositLoading" class="btn btn-primary">
                {{ depositLoading ? 'Processando...' : 'Depositar' }}
              </button>
            </form>
          </div>

          <div class="operation-form">
            <h3>Sacar</h3>
            <div v-if="withdrawMsg.text" :class="['alert', withdrawMsg.type === 'success' ? 'alert-success' : 'alert-error']">
              {{ withdrawMsg.text }}
            </div>
            <form @submit.prevent="handleWithdraw">
              <input v-model="withdrawAmount" type="number" step="0.01" min="0.01" placeholder="R$ 0,00" required />
              <button :disabled="withdrawLoading || insufficientBalance" class="btn btn-secondary">
                {{ withdrawLoading ? 'Processando...' : 'Sacar' }}
              </button>
              <p v-if="insufficientBalance" class="hint-error">Saldo insuficiente para este valor.</p>
            </form>
          </div>
        </section>

        <section class="recent-transactions">
          <h3>Últimas transações</h3>
          <div class="table-wrapper">
            <table v-if="dashboard?.recent_transactions?.length">
              <thead>
                <tr>
                  <th>Data</th>
                  <th>Tipo</th>
                  <th>Valor</th>
                  <th>Saldo após</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in dashboard.recent_transactions" :key="tx.id">
                  <td>{{ formatDate(tx.created_at) }}</td>
                  <td><span :class="['badge', tx.type]">{{ tx.type === 'credit' ? 'Crédito' : 'Débito' }}</span></td>
                  <td>{{ formatCurrency(tx.amount) }}</td>
                  <td>{{ formatCurrency(tx.balance_after) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="!dashboard?.recent_transactions?.length" class="empty">Nenhuma transação ainda.</p>
        </section>
      </template>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useWalletStore } from '@/stores/wallet'
import AppTopbar from '@/components/AppTopbar.vue'
const walletStore = useWalletStore()

const loading = ref(true)

const dashboard = ref(null)

const depositAmount = ref('')
const depositLoading = ref(false)
const depositMsg = ref({ text: '', type: '' })

const withdrawAmount = ref('')
const withdrawLoading = ref(false)
const withdrawMsg = ref({ text: '', type: '' })

const insufficientBalance = computed(() => {
  if (!withdrawAmount.value || !dashboard.value?.wallet?.balance) return false
  return parseFloat(withdrawAmount.value) > parseFloat(dashboard.value.wallet.balance)
})

onMounted(async () => {
  await loadDashboard()
})

async function loadDashboard() {
  loading.value = true
  await walletStore.fetchDashboard()
  dashboard.value = walletStore.dashboard
  loading.value = false
}

async function handleDeposit() {
  depositMsg.value = { text: '', type: '' }
  depositLoading.value = true
  try {
    await walletStore.deposit(parseFloat(depositAmount.value))
    depositAmount.value = ''
    depositMsg.value = { text: 'Depósito realizado com sucesso!', type: 'success' }
    await loadDashboard()
  } catch (e) {
    depositMsg.value = { text: e.response?.data?.message || 'Erro ao realizar depósito.', type: 'error' }
  } finally {
    depositLoading.value = false
  }
}

async function handleWithdraw() {
  withdrawMsg.value = { text: '', type: '' }
  withdrawLoading.value = true
  try {
    await walletStore.withdraw(parseFloat(withdrawAmount.value))
    withdrawAmount.value = ''
    withdrawMsg.value = { text: 'Saque realizado com sucesso!', type: 'success' }
    await loadDashboard()
  } catch (e) {
    withdrawMsg.value = { text: e.response?.data?.message || 'Erro ao realizar saque.', type: 'error' }
  } finally {
    withdrawLoading.value = false
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
}

function formatDate(iso) {
  return new Date(iso).toLocaleString('pt-BR')
}
</script>
