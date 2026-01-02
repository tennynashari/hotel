<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Bookings</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage hotel reservations</p>
        </div>
        <button
          @click="openCreateModal"
          class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + New Booking
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              @input="loadBookings"
              type="text"
              placeholder="Booking number or guest name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="loadBookings"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="checked_in">Checked In</option>
              <option value="checked_out">Checked Out</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Bookings Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">Loading bookings...</p>
        </div>

        <div v-else-if="bookings.length === 0" class="text-center py-12">
          <p class="text-gray-500">No bookings found</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="booking in bookings" :key="booking.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ booking.booking_number }}</div>
                    <div class="text-sm text-gray-600">{{ booking.guest?.name }}</div>
                  </div>
                  <span :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(booking.status) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <div>{{ formatDate(booking.check_in_date) }} - {{ formatDate(booking.check_out_date) }}</div>
                  <div>{{ booking.nights }} night(s) • {{ booking.adults }} adult(s)</div>
                  <div class="font-semibold text-gray-900">{{ formatCurrency(booking.subtotal) }}</div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="booking.status === 'pending'"
                    @click="confirmBooking(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                  >
                    Confirm
                  </button>
                  <button
                    v-if="booking.status === 'confirmed'"
                    @click="checkIn(booking.id)"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    Check In
                  </button>
                  <button
                    v-if="booking.status === 'checked_in'"
                    @click="checkOut(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                  >
                    Check Out
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(booking.status)"
                    @click="openEditModal(booking)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    Edit
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(booking.status)"
                    @click="cancelBooking(booking.id)"
                    class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                  >
                    Cancel
                  </button>
                  <button
                    v-if="['cancelled', 'checked_out'].includes(booking.status)"
                    @click="confirmDelete(booking)"
                    class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <table class="hidden md:table min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Booking
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Guest
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Check In / Out
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rooms
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status & Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ booking.booking_number }}</div>
                <div class="text-sm text-gray-500">{{ booking.nights }} night(s)</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ booking.guest?.name }}
                </div>
                <div class="text-sm text-gray-500">{{ booking.guest?.email || booking.guest?.phone }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(booking.check_in_date) }}</div>
                <div class="text-sm text-gray-500">{{ formatDate(booking.check_out_date) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <span v-for="(room, idx) in booking.rooms" :key="room.id">
                    {{ room.room_number }}<span v-if="idx < booking.rooms.length - 1">, </span>
                  </span>
                </div>
                <div class="text-sm text-gray-500">{{ booking.adults }} adult(s), {{ booking.children }} child(ren)</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ formatCurrency(booking.total_amount) }}</div>
                <div v-if="booking.deposit_amount > 0" class="text-sm text-gray-500">
                  Deposit: {{ formatCurrency(booking.deposit_amount) }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="mb-2">
                  <span :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(booking.status) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="booking.status === 'pending'"
                    @click="handleConfirm(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                    title="Confirm Booking"
                  >
                    Confirm
                  </button>
                  <button
                    v-if="booking.status === 'confirmed'"
                    @click="handleCheckIn(booking.id)"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                    title="Check In"
                  >
                    Check In
                  </button>
                  <button
                    v-if="booking.status === 'checked_in'"
                    @click="handleCheckOut(booking.id)"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                    title="Check Out"
                  >
                    Check Out
                  </button>
                  <button
                    v-if="booking.status === 'pending'"
                    @click="openEditModal(booking)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                    title="Edit"
                  >
                    Edit
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(booking.status)"
                    @click="confirmCancel(booking)"
                    class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                    title="Cancel"
                  >
                    Cancel
                  </button>
                  <button
                    v-if="['pending', 'cancelled'].includes(booking.status)"
                    @click="confirmDeleteBooking(booking)"
                    class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                    title="Delete"
                  >
                    Delete
                  </button>
                  <button
                    @click="viewBooking(booking)"
                    class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                    title="View Details"
                  >
                    View
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Booking Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Booking' : 'New Booking' }}
        </h2>

        <form @submit.prevent="saveBooking" class="space-y-4">
          <!-- Guest Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Guest *</label>
            <select
              v-model="formData.guest_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select guest</option>
              <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                {{ guest.name }} - {{ guest.email || guest.phone }}
              </option>
            </select>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check In *</label>
              <input
                v-model="formData.check_in_date"
                type="date"
                required
                :min="today"
                @change="checkAvailability"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check Out *</label>
              <input
                v-model="formData.check_out_date"
                type="date"
                required
                :min="formData.check_in_date"
                @change="checkAvailability"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Guests Count -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Adults *</label>
              <input
                v-model.number="formData.adults"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Children</label>
              <input
                v-model.number="formData.children"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Available Rooms -->
          <div v-if="availableRooms.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Rooms *</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-2">
              <label
                v-for="room in availableRooms"
                :key="room.id"
                class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
              >
                <input
                  type="checkbox"
                  :value="room.id"
                  v-model="formData.room_ids"
                  class="mr-2"
                />
                <span class="text-sm">
                  {{ room.room_number }} - {{ room.room_type?.name }}
                  ({{ formatCurrency(room.room_type?.base_price) }}/night)
                </span>
              </label>
            </div>
          </div>
          <div v-else-if="formData.check_in_date && formData.check_out_date" class="text-sm text-amber-600">
            No rooms available for selected dates
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
            <textarea
              v-model="formData.special_requests"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Any special requests from guest"
            ></textarea>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving || formData.room_ids.length === 0"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update Booking' : 'Create Booking') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div
      v-if="showCancelConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showCancelConfirm = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">Cancel Booking?</h2>
        <p class="text-gray-600 mb-6 text-sm md:text-base">
          Are you sure you want to cancel booking <strong>{{ bookingToCancel?.booking_number }}</strong>?
          The booking status will be changed to cancelled.
        </p>
        <div class="flex gap-3">
          <button
            @click="showCancelConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            No, Keep It
          </button>
          <button
            @click="handleCancel"
            :disabled="cancelling"
            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
          >
            {{ cancelling ? 'Cancelling...' : 'Yes, Cancel' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">Delete Booking?</h2>
        <p class="text-gray-600 mb-6 text-sm md:text-base">
          Are you sure you want to permanently delete booking <strong>{{ bookingToDelete?.booking_number }}</strong>?
          This action cannot be undone.
        </p>
        <div class="flex gap-3">
          <button
            @click="showDeleteConfirm = false"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="handleDeleteBooking"
            :disabled="deleting"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { bookingApi, guestApi, roomApi } from '../api'
import axios from 'axios'

const bookings = ref([])
const guests = ref([])
const availableRooms = ref([])
const loading = ref(false)
const showModal = ref(false)
const showCancelConfirm = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const cancelling = ref(false)
const deleting = ref(false)
const error = ref('')
const bookingToCancel = ref(null)
const bookingToDelete = ref(null)

const filters = ref({
  search: '',
  status: '',
})

const formData = ref({
  guest_id: '',
  check_in_date: '',
  check_out_date: '',
  adults: 1,
  children: 0,
  room_ids: [],
  special_requests: '',
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

onMounted(async () => {
  // Ensure CSRF cookie
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadBookings()
  loadGuests()
})

async function loadBookings() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status

    bookings.value = await bookingApi.getBookings(params)
  } catch (err) {
    console.error('Failed to load bookings:', err)
  } finally {
    loading.value = false
  }
}

async function loadGuests() {
  try {
    guests.value = await guestApi.getGuests()
  } catch (err) {
    console.error('Failed to load guests:', err)
  }
}

async function checkAvailability() {
  if (!formData.value.check_in_date || !formData.value.check_out_date) return
  
  try {
    availableRooms.value = await bookingApi.checkAvailability({
      check_in_date: formData.value.check_in_date,
      check_out_date: formData.value.check_out_date,
    })
  } catch (err) {
    console.error('Failed to check availability:', err)
  }
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    guest_id: '',
    check_in_date: '',
    check_out_date: '',
    adults: 1,
    children: 0,
    room_ids: [],
    special_requests: '',
  }
  availableRooms.value = []
  error.value = ''
  showModal.value = true
}

function openEditModal(booking) {
  isEditing.value = true
  formData.value = {
    id: booking.id,
    guest_id: booking.guest_id,
    check_in_date: booking.check_in_date,
    check_out_date: booking.check_out_date,
    adults: booking.adults,
    children: booking.children,
    room_ids: booking.rooms.map(r => r.id),
    special_requests: booking.special_requests || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

async function saveBooking() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await bookingApi.updateBooking(formData.value.id, formData.value)
    } else {
      await bookingApi.createBooking(formData.value)
    }
    closeModal()
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save booking'
  } finally {
    saving.value = false
  }
}

async function handleConfirm(bookingId) {
  try {
    await bookingApi.confirm(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to confirm booking')
  }
}

async function handleCheckIn(bookingId) {
  try {
    await bookingApi.checkIn(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to check in')
  }
}

async function handleCheckOut(bookingId) {
  try {
    await bookingApi.checkOut(bookingId)
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to check out')
  }
}

function confirmCancel(booking) {
  bookingToCancel.value = booking
  showCancelConfirm.value = true
}

async function handleCancel() {
  if (!bookingToCancel.value) return
  
  cancelling.value = true
  try {
    await bookingApi.cancel(bookingToCancel.value.id)
    showCancelConfirm.value = false
    bookingToCancel.value = null
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to cancel booking')
  } finally {
    cancelling.value = false
  }
}

function confirmDeleteBooking(booking) {
  bookingToDelete.value = booking
  showDeleteConfirm.value = true
}

async function handleDeleteBooking() {
  if (!bookingToDelete.value) return
  
  deleting.value = true
  try {
    await bookingApi.deleteBooking(bookingToDelete.value.id)
    showDeleteConfirm.value = false
    bookingToDelete.value = null
    await loadBookings()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete booking')
  } finally {
    deleting.value = false
  }
}

function viewBooking(booking) {
  alert(`Booking Details:\n\nBooking #: ${booking.booking_number}\nGuest: ${booking.guest?.name}\nStatus: ${getStatusLabel(booking.status)}`)
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    checked_in: 'bg-green-100 text-green-800',
    checked_out: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
  const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    checked_in: 'Checked In',
    checked_out: 'Checked Out',
    cancelled: 'Cancelled',
  }
  return labels[status] || status
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}
</script>
