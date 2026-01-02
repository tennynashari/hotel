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
  async getRooms(params = {}) {
    const response = await api.get('/rooms', { params })
    return response.data
  },

  async getRoomTypes() {
    const response = await api.get('/room-types')
    return response.data
  },

  async getRoomStatistics() {
    const response = await api.get('/rooms-statistics')
    return response.data
  },

  async createRoom(data) {
    const response = await api.post('/rooms', data)
    return response.data
  },

  async updateRoom(roomId, data) {
    const response = await api.put(`/rooms/${roomId}`, data)
    return response.data
  },

  async updateRoomStatus(roomId, status) {
    const response = await api.patch(`/rooms/${roomId}/status`, { status })
    return response.data
  },

  async deleteRoom(roomId) {
    const response = await api.delete(`/rooms/${roomId}`)
    return response.data
  },
}

export const guestApi = {
  async getGuests(params = {}) {
    const response = await api.get('/guests', { params })
    return response.data
  },

  async getGuest(guestId) {
    const response = await api.get(`/guests/${guestId}`)
    return response.data
  },

  async createGuest(data) {
    const response = await api.post('/guests', data)
    return response.data
  },

  async updateGuest(guestId, data) {
    const response = await api.put(`/guests/${guestId}`, data)
    return response.data
  },

  async deleteGuest(guestId) {
    const response = await api.delete(`/guests/${guestId}`)
    return response.data
  },

  async searchGuest(query) {
    const response = await api.get(`/guests/search/${query}`)
    return response.data
  },
}

export const bookingApi = {
  async getBookings(params = {}) {
    const response = await api.get('/bookings', { params })
    return response.data
  },

  async getBooking(bookingId) {
    const response = await api.get(`/bookings/${bookingId}`)
    return response.data
  },

  async createBooking(data) {
    const response = await api.post('/bookings', data)
    return response.data
  },

  async updateBooking(bookingId, data) {
    const response = await api.put(`/bookings/${bookingId}`, data)
    return response.data
  },

  async deleteBooking(bookingId) {
    const response = await api.delete(`/bookings/${bookingId}`)
    return response.data
  },

  async confirm(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/confirm`)
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

  async cancel(bookingId) {
    const response = await api.post(`/bookings/${bookingId}/cancel`)
    return response.data
  },

  async checkAvailability(params) {
    const response = await api.get('/bookings/check-availability', { params })
    return response.data
  },
}

export const housekeepingApi = {
  async getTasks(params = {}) {
    const response = await api.get('/housekeeping', { params })
    return response.data
  },

  async getTask(taskId) {
    const response = await api.get(`/housekeeping/${taskId}`)
    return response.data
  },

  async createTask(data) {
    const response = await api.post('/housekeeping', data)
    return response.data
  },

  async updateTask(taskId, data) {
    const response = await api.put(`/housekeeping/${taskId}`, data)
    return response.data
  },

  async deleteTask(taskId) {
    const response = await api.delete(`/housekeeping/${taskId}`)
    return response.data
  },

  async updateTaskStatus(taskId, status) {
    const response = await api.patch(`/housekeeping/${taskId}/status`, { status })
    return response.data
  },

  async getStatistics() {
    const response = await api.get('/housekeeping-statistics')
    return response.data
  },
}

export const dashboardApi = {
  async getDashboard() {
    const response = await api.get('/dashboard')
    return response.data
  },
}

export const userApi = {
  async getUsers(params) {
    const response = await api.get('/users', { params })
    return response.data
  },

  async getUser(userId) {
    const response = await api.get(`/users/${userId}`)
    return response.data
  },
}

export const paymentApi = {
  async getPayments(params) {
    const response = await api.get('/payments', { params })
    return response.data
  },

  async getPayment(paymentId) {
    const response = await api.get(`/payments/${paymentId}`)
    return response.data
  },

  async createPayment(data) {
    const response = await api.post('/payments', data)
    return response.data
  },

  async updatePayment(paymentId, data) {
    const response = await api.put(`/payments/${paymentId}`, data)
    return response.data
  },

  async deletePayment(paymentId) {
    const response = await api.delete(`/payments/${paymentId}`)
    return response.data
  },

  async getBookingPayments(bookingId) {
    const response = await api.get(`/bookings/${bookingId}/payments`)
    return response.data
  },
}
