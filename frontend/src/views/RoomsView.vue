<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header with Stats -->
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Room Management</h1>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage hotel rooms and availability</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">Total Rooms</div>
          <div class="text-2xl font-bold text-gray-900">{{ statistics.total_rooms || 0 }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4">
          <div class="text-sm text-green-600">Available</div>
          <div class="text-2xl font-bold text-green-700">{{ statistics.available || 0 }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4">
          <div class="text-sm text-blue-600">Occupied</div>
          <div class="text-2xl font-bold text-blue-700">{{ statistics.occupied || 0 }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4">
          <div class="text-sm text-yellow-600">Need Cleaning</div>
          <div class="text-2xl font-bold text-yellow-700">{{ statistics.dirty || 0 }}</div>
        </div>
        <div class="bg-purple-50 rounded-lg shadow p-4">
          <div class="text-sm text-purple-600">Occupancy Rate</div>
          <div class="text-2xl font-bold text-purple-700">{{ statistics.occupancy_rate || 0 }}%</div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col md:flex-row gap-4 items-end">
          <!-- Status Filter -->
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
            <select 
              v-model="filters.status" 
              @change="loadRooms"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Status</option>
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="dirty">Dirty</option>
              <option value="cleaning">Cleaning</option>
              <option value="out_of_order">Out of Order</option>
            </select>
          </div>

          <!-- Room Type Filter -->
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
            <select 
              v-model="filters.room_type_id" 
              @change="loadRooms"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Types</option>
              <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>

          <!-- Floor Filter -->
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Floor</label>
            <select 
              v-model="filters.floor" 
              @change="loadRooms"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Floors</option>
              <option value="1">Floor 1</option>
              <option value="2">Floor 2</option>
              <option value="3">Floor 3</option>
            </select>
          </div>

          <!-- Add Room Button -->
          <button
            @click="openAddModal"
            class="w-full md:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
          >
            + Add Room
          </button>
        </div>
      </div>

      <!-- Rooms Grid -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Loading rooms...</p>
      </div>

      <div v-else-if="rooms.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">No rooms found</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="room in rooms"
          :key="room.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-4 cursor-pointer"
          @click="openEditModal(room)"
        >
          <!-- Room Number and Status Badge -->
          <div class="flex items-start justify-between mb-3">
            <div>
              <div class="text-2xl font-bold text-gray-900">{{ room.room_number }}</div>
              <div class="text-sm text-gray-500">{{ room.room_type?.name }}</div>
            </div>
            <span 
              :class="getStatusBadgeClass(room.status)"
              class="px-2 py-1 text-xs font-semibold rounded-full"
            >
              {{ getStatusLabel(room.status) }}
            </span>
          </div>

          <!-- Room Details -->
          <div class="space-y-2 text-sm">
            <div class="flex items-center text-gray-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Capacity: {{ room.room_type?.capacity }} guests
            </div>
            <div class="flex items-center text-gray-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Floor {{ room.floor || 'N/A' }}
            </div>
            <div class="flex items-center text-gray-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ formatCurrency(room.room_type?.base_price) }} / night
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
            <button
              v-if="room.status !== 'available'"
              @click.stop="updateStatus(room.id, 'available')"
              class="flex-1 px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors"
            >
              Set Available
            </button>
            <button
              v-if="room.status !== 'out_of_order'"
              @click.stop="updateStatus(room.id, 'out_of_order')"
              class="flex-1 px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
            >
              Out of Order
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Room Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Room' : 'Add New Room' }}
        </h2>

        <form @submit.prevent="saveRoom" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
            <input
              v-model="formData.room_number"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="e.g., 101"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
            <select
              v-model="formData.room_type_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select type</option>
              <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                {{ type.name }} - {{ formatCurrency(type.base_price) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Floor</label>
            <select
              v-model="formData.floor"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select floor</option>
              <option value="1">Floor 1</option>
              <option value="2">Floor 2</option>
              <option value="3">Floor 3</option>
              <option value="4">Floor 4</option>
            </select>
          </div>

          <div v-if="isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="formData.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="dirty">Dirty</option>
              <option value="cleaning">Cleaning</option>
              <option value="out_of_order">Out of Order</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="formData.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Any special notes about this room"
            ></textarea>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              v-if="isEditing"
              type="button"
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Delete
            </button>
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="cancelDelete"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Room?</h2>
        
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete room <strong>{{ roomToDelete?.room_number }}</strong>?
          This action cannot be undone.
        </p>

        <div class="flex gap-3">
          <button
            type="button"
            @click="cancelDelete"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteRoom"
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
import { ref, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { roomApi } from '../api'
import axios from 'axios'

const rooms = ref([])
const roomTypes = ref([])
const statistics = ref({})
const loading = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const roomToDelete = ref(null)

const filters = ref({
  status: '',
  room_type_id: '',
  floor: '',
})

const formData = ref({
  room_number: '',
  room_type_id: '',
  floor: '',
  status: 'available',
  notes: '',
})

onMounted(async () => {
  // Ensure CSRF cookie is set first
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadRooms()
  loadRoomTypes()
  loadStatistics()
})

async function loadRooms() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.room_type_id) params.room_type_id = filters.value.room_type_id
    if (filters.value.floor) params.floor = filters.value.floor

    rooms.value = await roomApi.getRooms(params)
  } catch (err) {
    console.error('Failed to load rooms:', err)
  } finally {
    loading.value = false
  }
}

async function loadRoomTypes() {
  try {
    roomTypes.value = await roomApi.getRoomTypes()
  } catch (err) {
    console.error('Failed to load room types:', err)
  }
}

async function loadStatistics() {
  try {
    statistics.value = await roomApi.getRoomStatistics()
  } catch (err) {
    console.error('Failed to load statistics:', err)
  }
}

function openAddModal() {
  isEditing.value = false
  formData.value = {
    room_number: '',
    room_type_id: '',
    floor: '',
    status: 'available',
    notes: '',
  }
  error.value = ''
  showModal.value = true
}

function openEditModal(room) {
  isEditing.value = true
  formData.value = {
    id: room.id,
    room_number: room.room_number,
    room_type_id: room.room_type_id,
    floor: room.floor || '',
    status: room.status,
    notes: room.notes || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

function confirmDelete() {
  roomToDelete.value = { ...formData.value }
  showModal.value = false
  showDeleteConfirm.value = true
}

function cancelDelete() {
  showDeleteConfirm.value = false
  roomToDelete.value = null
}

async function deleteRoom() {
  if (!roomToDelete.value?.id) return
  
  deleting.value = true
  try {
    await roomApi.deleteRoom(roomToDelete.value.id)
    showDeleteConfirm.value = false
    roomToDelete.value = null
    await loadRooms()
    await loadStatistics()
  } catch (err) {
    console.error('Failed to delete room:', err)
    alert('Failed to delete room. It may have active bookings.')
  } finally {
    deleting.value = false
  }
}

async function saveRoom() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await roomApi.updateRoom(formData.value.id, formData.value)
    } else {
      await roomApi.createRoom(formData.value)
    }
    
    closeModal()
    await loadRooms()
    await loadStatistics()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save room'
  } finally {
    saving.value = false
  }
}

async function updateStatus(roomId, status) {
  try {
    await roomApi.updateRoomStatus(roomId, status)
    await loadRooms()
    await loadStatistics()
  } catch (err) {
    console.error('Failed to update status:', err)
  }
}

function getStatusBadgeClass(status) {
  const classes = {
    available: 'bg-green-100 text-green-800',
    occupied: 'bg-blue-100 text-blue-800',
    dirty: 'bg-yellow-100 text-yellow-800',
    cleaning: 'bg-purple-100 text-purple-800',
    out_of_order: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
  const labels = {
    available: 'Available',
    occupied: 'Occupied',
    dirty: 'Dirty',
    cleaning: 'Cleaning',
    out_of_order: 'Out of Order',
  }
  return labels[status] || status
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}
</script>
