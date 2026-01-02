<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Guests</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage hotel guests</p>
        </div>
        <button
          @click="openAddModal"
          class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + Add Guest
        </button>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-lg shadow p-4">
        <input
          v-model="searchQuery"
          @input="loadGuests"
          type="text"
          placeholder="Search by name, email, phone, or ID number..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <!-- Guests Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">Loading guests...</p>
        </div>

        <div v-else-if="guests.length === 0" class="text-center py-12">
          <p class="text-gray-500">No guests found</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="guest in guests" :key="guest.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ guest.name }}</div>
                    <div class="text-sm text-gray-600">{{ guest.email }}</div>
                  </div>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <div>{{ guest.phone }}</div>
                  <div>ID: {{ guest.id_card_type }} - {{ guest.id_card_number }}</div>
                  <div class="text-xs text-gray-500">{{ guest.address }}</div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    @click="openEditModal(guest)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(guest)"
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
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Contact
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID Card
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nationality
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Bookings
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="guest in guests" :key="guest.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ guest.name }}</div>
                <div v-if="guest.birth_date" class="text-sm text-gray-500">
                  Born: {{ formatDate(guest.birth_date) }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ guest.phone }}</div>
                <div v-if="guest.email" class="text-sm text-gray-500">{{ guest.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="guest.id_card_type" class="text-sm text-gray-900">
                  {{ getIdTypeLabel(guest.id_card_type) }}
                </div>
                <div v-if="guest.id_card_number" class="text-sm text-gray-500">
                  {{ guest.id_card_number }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ guest.nationality || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ guest.bookings_count || 0 }} booking(s)</div>
                <div v-if="guest.is_repeat_guest" class="text-xs text-green-600">
                  Repeat Guest
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openEditModal(guest)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(guest)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Guest Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Guest' : 'Add New Guest' }}
        </h2>

        <form @submit.prevent="saveGuest" class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="e.g., John Doe"
            />
          </div>

          <!-- Contact -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
              <input
                v-model="formData.phone"
                type="tel"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="+62 812-3456-7890"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="formData.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="john@example.com"
              />
            </div>
          </div>

          <!-- ID Card -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ID Card Type</label>
              <select
                v-model="formData.id_card_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select type</option>
                <option value="ktp">KTP</option>
                <option value="passport">Passport</option>
                <option value="sim">SIM</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ID Card Number</label>
              <input
                v-model="formData.id_card_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="1234567890123456"
              />
            </div>
          </div>

          <!-- Nationality & Birth Date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
              <input
                v-model="formData.nationality"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Indonesia"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
              <input
                v-model="formData.birth_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea
              v-model="formData.address"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Full address"
            ></textarea>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              v-if="isEditing"
              type="button"
              @click="confirmDelete(formData)"
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
      @click.self="showDeleteConfirm = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Guest?</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ guestToDelete?.name }}</strong>?
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
            @click="handleDelete"
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
import { guestApi } from '../api'
import axios from 'axios'

const guests = ref([])
const loading = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const searchQuery = ref('')
const guestToDelete = ref(null)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  id_card_type: '',
  id_card_number: '',
  nationality: 'Indonesia',
  birth_date: '',
  address: '',
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
  
  loadGuests()
})

async function loadGuests() {
  loading.value = true
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value

    guests.value = await guestApi.getGuests(params)
  } catch (err) {
    console.error('Failed to load guests:', err)
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  formData.value = {
    name: '',
    email: '',
    phone: '',
    id_card_type: '',
    id_card_number: '',
    nationality: 'Indonesia',
    birth_date: '',
    address: '',
  }
  error.value = ''
  showModal.value = true
}

function openEditModal(guest) {
  isEditing.value = true
  formData.value = {
    id: guest.id,
    name: guest.name,
    email: guest.email || '',
    phone: guest.phone,
    id_card_type: guest.id_card_type || '',
    id_card_number: guest.id_card_number || '',
    nationality: guest.nationality || 'Indonesia',
    birth_date: guest.birth_date || '',
    address: guest.address || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

async function saveGuest() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await guestApi.updateGuest(formData.value.id, formData.value)
    } else {
      await guestApi.createGuest(formData.value)
    }
    
    closeModal()
    await loadGuests()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save guest'
  } finally {
    saving.value = false
  }
}

function confirmDelete(guest) {
  guestToDelete.value = guest
  showModal.value = false
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!guestToDelete.value) return
  
  deleting.value = true
  try {
    await guestApi.deleteGuest(guestToDelete.value.id)
    showDeleteConfirm.value = false
    guestToDelete.value = null
    await loadGuests()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete guest')
  } finally {
    deleting.value = false
  }
}

function getIdTypeLabel(type) {
  const labels = {
    ktp: 'KTP',
    passport: 'Passport',
    sim: 'SIM',
    other: 'Other'
  }
  return labels[type] || type
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
</script>
