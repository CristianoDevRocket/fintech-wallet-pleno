<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <h1>Fintech Wallet</h1>
      <h2>Criar conta</h2>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nome</label>
          <input v-model="form.name" type="text" required placeholder="Seu nome" />
        </div>
        <div class="form-group">
          <label>E-mail</label>
          <input v-model="form.email" type="email" required placeholder="seu@email.com" />
        </div>
        <div class="form-group">
          <label>Senha</label>
          <input v-model="form.password" type="password" required placeholder="Mínimo 8 caracteres" />
        </div>
        <div class="form-group">
          <label>Confirmar senha</label>
          <input v-model="form.password_confirmation" type="password" required placeholder="Repita a senha" />
        </div>
        <button type="submit" :disabled="loading" class="btn btn-primary">
          {{ loading ? 'Criando...' : 'Criar conta' }}
        </button>
      </form>

      <p class="auth-link">Já tem conta? <RouterLink to="/login">Entrar</RouterLink></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  error.value = ''
  loading.value = true
  try {
    await auth.register(form.value)
    router.push('/dashboard')
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Erro ao criar conta.'
    }
  } finally {
    loading.value = false
  }
}
</script>
