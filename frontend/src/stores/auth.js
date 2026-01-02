import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authApi } from '../api'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isAuthenticated = ref(false)
  const loading = ref(false)

  async function login(credentials) {
    loading.value = true
    try {
      const data = await authApi.login(credentials)
      user.value = data.user
      isAuthenticated.value = true
      return data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      await authApi.logout()
      user.value = null
      isAuthenticated.value = false
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  async function checkAuth() {
    loading.value = true
    try {
      // Ensure CSRF cookie is set
      await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
        withCredentials: true
      })
      
      const data = await authApi.getUser()
      user.value = data.user
      isAuthenticated.value = true
    } catch (error) {
      user.value = null
      isAuthenticated.value = false
      throw error
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    isAuthenticated,
    loading,
    login,
    logout,
    checkAuth,
  }
})
