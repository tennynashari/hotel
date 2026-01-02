<template>
  <LayoutMain>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">New Booking</h1>
        <router-link
          to="/bookings"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
        >
          Back
        </router-link>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-500">Booking form coming soon...</p>
      </div>
    </div>
  </LayoutMain>
</template>

<script setup>
import LayoutMain from '../components/LayoutMain.vue'
</script>
