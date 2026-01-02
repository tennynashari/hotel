import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Don't set Referer header - it's unsafe and browser won't allow it
    // Sanctum will work with CORS and credentials already configured
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle 419 CSRF token mismatch
    if (error.response?.status === 419) {
      console.error('CSRF token mismatch')
    }
    
    // Handle 401 Unauthenticated
    if (error.response?.status === 401) {
      // Only redirect if not already on login page
      if (window.location.pathname !== '/login') {
        console.error('Unauthenticated - redirecting to login')
        localStorage.removeItem('user')
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
