<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Welcome back! Here's what's happening today.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-500 mt-2">Loading dashboard...</p>
      </div>

      <!-- Stats Cards -->
      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Available Rooms -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Available Rooms</dt>
                  <dd class="text-2xl font-semibold text-gray-900">
                    {{ dashboard.rooms?.available || 0 }}/{{ dashboard.rooms?.total || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Check-ins -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Today's Check-ins</dt>
                  <dd class="text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.today_check_ins || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Check-outs -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Today's Check-outs</dt>
                  <dd class="text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.today_check_outs || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Today's Revenue -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Today's Full Payments</dt>
                  <dd class="text-2xl font-semibold text-gray-900">
                    {{ formatCurrency(dashboard.revenue?.today_full_payments || 0) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Occupied Rooms -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Occupied Rooms</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.rooms?.occupied || 0 }}</div>
          </div>

          <!-- Pending Bookings -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Pending Bookings</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.bookings?.pending || 0 }}</div>
          </div>

          <!-- Pending Tasks -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Pending Tasks</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ dashboard.housekeeping?.pending || 0 }}</div>
          </div>

          <!-- Month Revenue -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Month Full Payments</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">
              {{ formatCurrency(dashboard.revenue?.month_full_payments || 0) }}
            </div>
          </div>
        </div>

        <!-- Payment Report Section -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Today's Payment Report</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
            <!-- Full Payments Today -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
              <div class="text-xs font-medium text-green-600 uppercase">Full Payment</div>
              <div class="mt-2 text-xl font-bold text-green-700">
                {{ formatCurrency(dashboard.revenue?.today_full_payments || 0) }}
              </div>
              <div class="text-xs text-green-600 mt-1">
                {{ dashboard.payments?.today_by_type?.full?.count || 0 }} transactions
              </div>
            </div>

            <!-- Deposit Today -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
              <div class="text-xs font-medium text-blue-600 uppercase">Deposit</div>
              <div class="mt-2 text-xl font-bold text-blue-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.deposit?.total || 0) }}
              </div>
              <div class="text-xs text-blue-600 mt-1">
                {{ dashboard.payments?.today_by_type?.deposit?.count || 0 }} transactions
              </div>
            </div>

            <!-- Partial Today -->
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
              <div class="text-xs font-medium text-yellow-600 uppercase">Partial</div>
              <div class="mt-2 text-xl font-bold text-yellow-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.partial?.total || 0) }}
              </div>
              <div class="text-xs text-yellow-600 mt-1">
                {{ dashboard.payments?.today_by_type?.partial?.count || 0 }} transactions
              </div>
            </div>

            <!-- Refund Today -->
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
              <div class="text-xs font-medium text-red-600 uppercase">Refund</div>
              <div class="mt-2 text-xl font-bold text-red-700">
                {{ formatCurrency(dashboard.payments?.today_by_type?.refund?.total || 0) }}
              </div>
              <div class="text-xs text-red-600 mt-1">
                {{ dashboard.payments?.today_by_type?.refund?.count || 0 }} transactions
              </div>
            </div>

            <!-- Total Today -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
              <div class="text-xs font-medium text-purple-600 uppercase">Total Today</div>
              <div class="mt-2 text-xl font-bold text-purple-700">
                {{ formatCurrency(dashboard.revenue?.today_all_payments || 0) }}
              </div>
              <div class="text-xs text-purple-600 mt-1">All payments</div>
            </div>
          </div>

          <!-- Monthly Summary -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">This Month Summary</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-600">Full Payments (Month)</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                  {{ formatCurrency(dashboard.revenue?.month_full_payments || 0) }}
                </div>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-600">All Payments (Month)</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                  {{ formatCurrency(dashboard.revenue?.month_all_payments || 0) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Bookings</h2>
          </div>
          <div v-if="!dashboard.recent_bookings || dashboard.recent_bookings.length === 0" class="p-6">
            <div class="text-center text-gray-500 py-8">
              <p>No recent bookings</p>
            </div>
          </div>
          <div v-else>
            <!-- Mobile View -->
            <div class="block md:hidden">
              <div v-for="booking in dashboard.recent_bookings" :key="booking.id" class="p-4 border-b border-gray-200 last:border-b-0">
                <div class="space-y-2">
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="font-medium text-gray-900">{{ booking.guest?.name }}</div>
                      <div class="text-sm text-gray-500">{{ booking.guest?.email }}</div>
                    </div>
                    <span :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(booking.status) }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-600">
                    <div>Room {{ booking.room?.room_number }} - {{ booking.room?.room_type?.name }}</div>
                    <div class="mt-1">{{ formatDate(booking.check_in_date) }} - {{ formatDate(booking.check_out_date) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="booking in dashboard.recent_bookings" :key="booking.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ booking.guest?.name }}</div>
                    <div class="text-sm text-gray-500">{{ booking.guest?.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ booking.room?.room_number }}</div>
                    <div class="text-sm text-gray-500">{{ booking.room?.room_type?.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(booking.check_in_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(booking.check_out_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusBadgeClass(booking.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(booking.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LayoutMain from '../components/LayoutMain.vue'
import { dashboardApi } from '../api'
import axios from 'axios'

const dashboard = ref({})
const loading = ref(false)

onMounted(async () => {
  // Ensure CSRF cookie
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    })
  } catch (err) {
    console.error('Failed to get CSRF cookie:', err)
  }
  
  loadDashboard()
})

async function loadDashboard() {
  loading.value = true
  try {
    dashboard.value = await dashboardApi.getDashboard()
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  } finally {
    loading.value = false
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
    month: 'short',
    day: 'numeric'
  })
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
</script>
