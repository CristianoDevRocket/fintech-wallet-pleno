import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useWalletStore = defineStore('wallet', () => {
  const wallet = ref(null)
  const dashboard = ref(null)
  const transactions = ref([])
  const pagination = ref({})

  async function fetchDashboard() {
    const { data } = await api.get('/dashboard')
    dashboard.value = data
    wallet.value = data.wallet
  }

  async function fetchTransactions(params = {}) {
    const { data } = await api.get('/transactions', { params })
    transactions.value = data.data
    pagination.value = data.meta
  }

  async function deposit(amount) {
    const { data } = await api.post('/wallet/deposit', { amount })
    if (wallet.value) wallet.value.balance = data.balance_after
    return data
  }

  async function withdraw(amount) {
    const { data } = await api.post('/wallet/withdraw', { amount })
    if (wallet.value) wallet.value.balance = data.balance_after
    return data
  }

  return { wallet, dashboard, transactions, pagination, fetchDashboard, fetchTransactions, deposit, withdraw }
})
