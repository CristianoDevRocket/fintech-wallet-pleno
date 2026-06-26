import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)

  async function register(payload) {
    const { data } = await api.post('/register', payload)
    setSession(data)
  }

  async function login(payload) {
    const { data } = await api.post('/login', payload)
    setSession(data)
  }

  async function logout() {
    await api.post('/logout')
    clearSession()
  }

  function setSession(data) {
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
  }

  function clearSession() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, register, login, logout }
})
