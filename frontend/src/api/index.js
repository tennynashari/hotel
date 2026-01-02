import api from './axios'

export const authApi = {
  async login(credentials) {
    // Get CSRF cookie first
    await api.get('http://localhost:8000/sanctum/csrf-cookie')
    // Then login
    const response = await api.post('/login', credentials)
    return response.data
  },

  async logout() {
    const response = await api.post('/logout')
    return response.data
  },

  async getUser() {
    const response = await api.get('/user')
    return response.data
  },
}

export const roomApi = {
  async getRooms() {
    const response = await api.get('/rooms')
    return response.data
  },

  async getRoomTypes() {
    const response = await api.get('/room-types')
    return response.data
  },

  async createRoom(data) {
    const response = await api.post('/rooms', data)
    return response.data
  },

  async updateRoomStatus(roomId, status) {
    const response = await api.patch(`/rooms/${roomId}/status`, { status })
    return response.data
  },
}

export const guestApi = {
  async getGuests() {
    const response = await api.get('/guests')
    return response.data
  },

  async createGuest(data) {
    const response = await api.post('/guests', data)
    return response.data
  },

  async searchGuest(query) {
    const response = await api.get(`/guests/search/${query}`)
    return response.data
  },
}

export const bookingApi = {
  async getBookings() {
    const response = await api.get('/bookings')
    return response.data
  },

  async createBooking(data) {
    const response = await api.post('/bookings', data)
    return response.data
  },

  async checkIn(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/check-in`)
    return response.data
  },

  async checkOut(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/check-out`)
    return response.data
  },
}

export const dashboardApi = {
  async getDashboard() {
    const response = await api.get('/dashboard')
    return response.data
  },
}
