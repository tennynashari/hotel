<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Payments & Billing</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage payment transactions and print invoices</p>
        </div>
        <button
          @click="openCreateModal"
          class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + New Payment
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type</label>
            <select
              v-model="filters.payment_type"
              @change="loadPayments"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Types</option>
              <option value="deposit">Deposit</option>
              <option value="partial">Partial</option>
              <option value="full">Full Payment</option>
              <option value="refund">Refund</option>
              <option value="extra_charge">Extra Charge</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
            <select
              v-model="filters.payment_method"
              @change="loadPayments"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Methods</option>
              <option value="cash">Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
              <option value="card">Card</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              @input="loadPayments"
              type="text"
              placeholder="Payment number..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Payments List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">Loading payments...</p>
        </div>

        <div v-else-if="payments.length === 0" class="text-center py-12">
          <p class="text-gray-500">No payments found</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="payment in payments" :key="payment.id" class="p-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50">
              <div class="space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ payment.payment_number }}</div>
                    <div class="text-sm text-gray-600">{{ payment.booking?.booking_number }}</div>
                    <div class="text-sm text-gray-600">{{ payment.booking?.guest?.name }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-semibold text-gray-900">{{ formatCurrency(payment.amount) }}</div>
                  </div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentTypeLabel(payment.payment_type) }}
                  </span>
                  <span :class="getPaymentMethodBadgeClass(payment.payment_method)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentMethodLabel(payment.payment_method) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ formatDate(payment.created_at) }}
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    @click="printInvoice(payment)"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    Print
                  </button>
                  <button
                    @click="openEditModal(payment)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(payment)"
                    class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Payment Number
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Booking / Guest
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type / Method
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ payment.payment_number }}</div>
                  <div class="text-sm text-gray-500">{{ payment.reference_number || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ payment.booking?.guest?.name }}</div>
                  <div class="text-sm text-gray-500">Room {{ payment.booking?.room?.room_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <span :class="getPaymentTypeBadgeClass(payment.payment_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getPaymentTypeLabel(payment.payment_type) }}
                    </span>
                  </div>
                  <div class="mt-1 text-xs text-gray-500">{{ getPaymentMethodLabel(payment.payment_method) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex gap-2">
                    <button
                      @click="printInvoice(payment)"
                      class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200"
                    >
                      Print
                    </button>
                    <button
                      @click="openEditModal(payment)"
                      class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(payment)"
                      class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
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
    </div>

    <!-- Create/Edit Payment Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Payment' : 'New Payment' }}
        </h2>

        <form @submit.prevent="savePayment" class="space-y-4">
          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Booking *</label>
            <div class="relative">
              <input
                v-model="bookingSearch"
                @input="filterBookings"
                @focus="showBookingDropdown = true"
                type="text"
                required
                placeholder="Search booking number or guest name..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <div
                v-if="showBookingDropdown && filteredBookings.length > 0"
                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
              >
                <div
                  v-for="booking in filteredBookings"
                  :key="booking.id"
                  @click="selectBooking(booking)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                >
                  <div class="font-medium text-gray-900">{{ booking.booking_number }}</div>
                  <div class="text-sm text-gray-600">
                    {{ booking.guest?.name }} - Room {{ booking.room?.room_number }}
                    <span class="text-xs text-gray-500">({{ getStatusLabel(booking.status) }})</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
            <select
              v-model="formData.payment_type"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="deposit">Deposit</option>
              <option value="partial">Partial Payment</option>
              <option value="full">Full Payment</option>
              <option value="refund">Refund</option>
              <option value="extra_charge">Extra Charge</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
            <select
              v-model="formData.payment_method"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="cash">Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
              <option value="card">Card</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
            <input
              v-model.number="formData.amount"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="formData.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Transaction ID, transfer number..."
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="formData.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Additional notes..."
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
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Payment?</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete payment <strong>{{ paymentToDelete?.payment_number }}</strong>?
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

    <!-- Print Invoice Template (hidden) -->
    <div ref="invoiceTemplate" class="hidden print:block">
      <div v-if="selectedPayment" class="p-8 bg-white max-w-4xl mx-auto">
        <div class="border-2 border-gray-800 p-8">
          <!-- Header -->
          <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">INVOICE</h1>
            <p class="text-gray-600 mt-2">Payment Receipt</p>
          </div>

          <!-- Company & Customer Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
              <h3 class="font-bold text-gray-900 mb-2">From:</h3>
              <p class="text-gray-700 font-semibold">Your Hotel Name</p>
              <p class="text-gray-600">Jl. Hotel Address</p>
              <p class="text-gray-600">City, Province 12345</p>
              <p class="text-gray-600">Phone: (021) 1234-5678</p>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2">To:</h3>
              <p class="text-gray-700 font-semibold">{{ selectedPayment.booking?.guest?.name }}</p>
              <p class="text-gray-600">{{ selectedPayment.booking?.guest?.email }}</p>
              <p class="text-gray-600">{{ selectedPayment.booking?.guest?.phone }}</p>
            </div>
          </div>

          <!-- Invoice Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 bg-gray-50 p-4">
            <div>
              <p class="text-sm text-gray-600">Payment Number</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.payment_number }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Payment Date</p>
              <p class="font-bold text-gray-900">{{ formatDate(selectedPayment.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Room Number</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.booking?.room?.room_number }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Room Type</p>
              <p class="font-bold text-gray-900">{{ selectedPayment.booking?.room?.room_type?.name }}</p>
            </div>
          </div>

          <!-- Payment Details Table -->
          <table class="w-full mb-8">
            <thead>
              <tr class="border-b-2 border-gray-800">
                <th class="text-left py-2 px-4">Description</th>
                <th class="text-right py-2 px-4">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-gray-300">
                <td class="py-3 px-4">
                  <p class="font-medium">{{ getPaymentTypeLabel(selectedPayment.payment_type) }}</p>
                  <p class="text-sm text-gray-600">Payment Method: {{ getPaymentMethodLabel(selectedPayment.payment_method) }}</p>
                  <p v-if="selectedPayment.reference_number" class="text-sm text-gray-600">
                    Ref: {{ selectedPayment.reference_number }}
                  </p>
                </td>
                <td class="py-3 px-4 text-right font-bold">{{ formatCurrency(selectedPayment.amount) }}</td>
              </tr>
              <tr class="border-t-2 border-gray-800">
                <td class="py-3 px-4 text-right font-bold">TOTAL PAID</td>
                <td class="py-3 px-4 text-right font-bold text-xl">{{ formatCurrency(selectedPayment.amount) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Notes -->
          <div v-if="selectedPayment.notes" class="mb-8">
            <h3 class="font-bold text-gray-900 mb-2">Notes:</h3>
            <p class="text-gray-600">{{ selectedPayment.notes }}</p>
          </div>

          <!-- Footer -->
          <div class="text-center mt-8 pt-8 border-t border-gray-300">
            <p class="text-gray-600">Thank you for your payment!</p>
            <p class="text-sm text-gray-500 mt-2">This is a computer-generated receipt.</p>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { paymentApi, bookingApi } from '../api'
import axios from 'axios'

const payments = ref([])
const bookings = ref([])
const filteredBookings = ref([])
const selectedPayment = ref(null)
const loading = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const showBookingDropdown = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const paymentToDelete = ref(null)
const invoiceTemplate = ref(null)
const bookingSearch = ref('')

const filters = ref({
  payment_type: '',
  payment_method: '',
  search: '',
})

const formData = ref({
  booking_id: '',
  payment_type: 'full',
  payment_method: 'cash',
  amount: 0,
  reference_number: '',
  notes: '',
})

onMounted(async () => {
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadPayments()
  loadBookings()
})

async function loadPayments() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.payment_type) params.payment_type = filters.value.payment_type
    if (filters.value.payment_method) params.payment_method = filters.value.payment_method
    if (filters.value.search) params.search = filters.value.search

    payments.value = await paymentApi.getPayments(params)
  } catch (err) {
    console.error('Failed to load payments:', err)
  } finally {
    loading.value = false
  }
}

async function loadBookings() {
  try {
    // Load all bookings for search
    const allBookings = await bookingApi.getBookings()
    bookings.value = allBookings
    filteredBookings.value = allBookings
  } catch (err) {
    console.error('Failed to load bookings:', err)
  }
}

function filterBookings() {
  const search = bookingSearch.value.toLowerCase()
  if (!search) {
    filteredBookings.value = bookings.value
  } else {
    filteredBookings.value = bookings.value.filter(booking => {
      const bookingNumber = booking.booking_number?.toLowerCase() || ''
      const guestName = booking.guest?.name?.toLowerCase() || ''
      const roomNumber = booking.room?.room_number?.toString() || ''
      return bookingNumber.includes(search) || 
             guestName.includes(search) || 
             roomNumber.includes(search)
    })
  }
  showBookingDropdown.value = true
}

function selectBooking(booking) {
  formData.value.booking_id = booking.id
  bookingSearch.value = `${booking.booking_number} - ${booking.guest?.name}`
  showBookingDropdown.value = false
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    booking_id: '',
    payment_type: 'full',
    payment_method: 'cash',
    amount: 0,
    reference_number: '',
    notes: '',
  }
  bookingSearch.value = ''
  error.value = ''
  showModal.value = true
}

function openEditModal(payment) {
  isEditing.value = true
  formData.value = {
    id: payment.id,
    booking_id: payment.booking_id,
    payment_type: payment.payment_type,
    payment_method: payment.payment_method,
    amount: payment.amount,
    reference_number: payment.reference_number || '',
    notes: payment.notes || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  showBookingDropdown.value = false
  error.value = ''
}

async function savePayment() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await paymentApi.updatePayment(formData.value.id, formData.value)
    } else {
      await paymentApi.createPayment(formData.value)
    }
    
    closeModal()
    await loadPayments()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save payment'
  } finally {
    saving.value = false
  }
}

function confirmDelete(payment) {
  paymentToDelete.value = payment
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!paymentToDelete.value) return
  
  deleting.value = true
  try {
    await paymentApi.deletePayment(paymentToDelete.value.id)
    showDeleteConfirm.value = false
    paymentToDelete.value = null
    await loadPayments()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete payment')
  } finally {
    deleting.value = false
  }
}

async function printInvoice(payment) {
  try {
    // Load full payment details
    selectedPayment.value = await paymentApi.getPayment(payment.id)
    
    // Wait for DOM update
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Print
    window.print()
  } catch (err) {
    console.error('Failed to load payment details:', err)
    alert('Failed to load payment details for printing')
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getPaymentTypeBadgeClass(type) {
  const classes = {
    deposit: 'bg-blue-100 text-blue-800',
    partial: 'bg-yellow-100 text-yellow-800',
    full: 'bg-green-100 text-green-800',
    refund: 'bg-red-100 text-red-800',
    extra_charge: 'bg-purple-100 text-purple-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getPaymentTypeLabel(type) {
  const labels = {
    deposit: 'Deposit',
    partial: 'Partial',
    full: 'Full Payment',
    refund: 'Refund',
    extra_charge: 'Extra Charge',
  }
  return labels[type] || type
}

function getPaymentMethodBadgeClass(method) {
  const classes = {
    cash: 'bg-green-100 text-green-800',
    transfer: 'bg-blue-100 text-blue-800',
    qris: 'bg-purple-100 text-purple-800',
    card: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800',
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

function getPaymentMethodLabel(method) {
  const labels = {
    cash: 'Cash',
    transfer: 'Bank Transfer',
    qris: 'QRIS',
    card: 'Card',
    other: 'Other',
  }
  return labels[method] || method
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

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    showBookingDropdown.value = false
  }
})
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  
  .print\:block,
  .print\:block * {
    visibility: visible;
  }
  
  .print\:block {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
