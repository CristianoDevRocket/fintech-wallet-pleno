<template>
  <header class="topbar">
    <span class="brand">Fintech Wallet</span>

    <nav>
      <RouterLink to="/dashboard">Dashboard</RouterLink>
      <RouterLink to="/transactions">Histórico</RouterLink>

      <div class="user-info">
        <span class="user-avatar">{{ initials }}</span>
        <span class="user-name">{{ auth.user?.name }}</span>
      </div>

      <button @click="handleLogout" class="btn-logout">Sair</button>
    </nav>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const initials = computed(() => {
  const name = auth.user?.name || ''
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
