<template>
  <LayoutMain>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Bookings</h1>
        <router-link
          to="/bookings/new"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          New Booking
        </router-link>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-500">Bookings list coming soon...</p>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import LayoutMain from '../components/LayoutMain.vue'
</script>
