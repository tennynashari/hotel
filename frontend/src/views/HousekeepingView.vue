<template>
  <LayoutMain>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Housekeeping</h1>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage room cleaning and maintenance tasks</p>
        </div>
        <button
          @click="openCreateModal"
          class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
        >
          + New Task
        </button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">Total Tasks</div>
          <div class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4">
          <div class="text-sm text-yellow-600">Pending</div>
          <div class="text-2xl font-bold text-yellow-700">{{ statistics.pending || 0 }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4">
          <div class="text-sm text-blue-600">In Progress</div>
          <div class="text-2xl font-bold text-blue-700">{{ statistics.in_progress || 0 }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4">
          <div class="text-sm text-green-600">Completed Today</div>
          <div class="text-2xl font-bold text-green-700">{{ statistics.completed_today || 0 }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4">
          <div class="text-sm text-red-600">High Priority</div>
          <div class="text-2xl font-bold text-red-700">{{ statistics.high_priority || 0 }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="loadTasks"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select
              v-model="filters.priority"
              @change="loadTasks"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="normal">Normal</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
            <select
              v-model="filters.assigned_to"
              @change="loadTasks"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Staff</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="text-gray-500 mt-2">Loading tasks...</p>
        </div>

        <div v-else-if="tasks.length === 0" class="text-center py-12">
          <p class="text-gray-500">No housekeeping tasks found</p>
        </div>

        <div v-else>
          <!-- Mobile Card View -->
          <div class="block md:hidden">
            <div v-for="task in tasks" :key="task.id" class="p-4 border-b border-gray-200 last:border-b-0">
              <div class="space-y-2">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ task.room?.room_number }}</div>
                    <div class="text-sm text-gray-500">{{ task.room?.room_type?.name }}</div>
                  </div>
                  <span :class="getPriorityBadgeClass(task.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPriorityLabel(task.priority) }}
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <span :class="getTaskTypeBadgeClass(task.task_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getTaskTypeLabel(task.task_type) }}
                  </span>
                  <span :class="getStatusBadgeClass(task.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(task.status) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ task.assigned_user?.name || 'Unassigned' }}
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                  <button
                    v-if="task.status === 'pending'"
                    @click="updateStatus(task.id, 'in_progress')"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                  >
                    Start
                  </button>
                  <button
                    v-if="task.status === 'in_progress'"
                    @click="updateStatus(task.id, 'completed')"
                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    Complete
                  </button>
                  <button
                    v-if="task.status !== 'completed'"
                    @click="openEditModal(task)"
                    class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                  >
                    Edit
                  </button>
                  <button
                    v-if="task.status === 'pending'"
                    @click="confirmDelete(task)"
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
                  Room
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Task Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Priority
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Assigned To
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status & Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ task.room?.room_number }}</div>
                  <div class="text-sm text-gray-500">{{ task.room?.room_type?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTaskTypeBadgeClass(task.task_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getTaskTypeLabel(task.task_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPriorityBadgeClass(task.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPriorityLabel(task.priority) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ task.assigned_user?.name || 'Unassigned' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="mb-2">
                    <span :class="getStatusBadgeClass(task.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(task.status) }}
                    </span>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-if="task.status === 'pending'"
                      @click="updateStatus(task.id, 'in_progress')"
                      class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                    >
                      Start
                    </button>
                    <button
                      v-if="task.status === 'in_progress'"
                      @click="updateStatus(task.id, 'completed')"
                      class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200"
                    >
                      Complete
                    </button>
                    <button
                      v-if="task.status !== 'completed'"
                      @click="openEditModal(task)"
                      class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                    >
                      Edit
                    </button>
                    <button
                      v-if="task.status === 'pending'"
                      @click="confirmDelete(task)"
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

    <!-- Create/Edit Task Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-4 md:p-6 my-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4">
          {{ isEditing ? 'Edit Task' : 'New Housekeeping Task' }}
        </h2>

        <form @submit.prevent="saveTask" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Room *</label>
            <select
              v-model="formData.room_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select room</option>
              <option v-for="room in rooms" :key="room.id" :value="room.id">
                {{ room.room_number }} - {{ room.room_type?.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Task Type *</label>
            <select
              v-model="formData.task_type"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="cleaning">Cleaning</option>
              <option value="maintenance">Maintenance</option>
              <option value="inspection">Inspection</option>
              <option value="deep_clean">Deep Clean</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
            <select
              v-model="formData.priority"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="low">Low</option>
              <option value="normal">Normal</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
            <select
              v-model="formData.assigned_to"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Unassigned</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
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
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Task?</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete this housekeeping task for room <strong>{{ taskToDelete?.room?.room_number }}</strong>?
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
import { housekeepingApi, roomApi, userApi } from '../api'
import axios from 'axios'

const tasks = ref([])
const rooms = ref([])
const users = ref([])
const statistics = ref({})
const loading = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const taskToDelete = ref(null)

const filters = ref({
  status: '',
  priority: '',
  assigned_to: '',
})

const formData = ref({
  room_id: '',
  task_type: 'cleaning',
  priority: 'normal',
  assigned_to: '',
  notes: '',
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
  
  loadTasks()
  loadRooms()
  loadUsers()
  loadStatistics()
})

async function loadTasks() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.priority) params.priority = filters.value.priority
    if (filters.value.assigned_to) params.assigned_to = filters.value.assigned_to

    tasks.value = await housekeepingApi.getTasks(params)
  } catch (err) {
    console.error('Failed to load tasks:', err)
  } finally {
    loading.value = false
  }
}

async function loadRooms() {
  try {
    rooms.value = await roomApi.getRooms()
  } catch (err) {
    console.error('Failed to load rooms:', err)
  }
}

async function loadUsers() {
  try {
    // Get all users or filter by role (e.g., housekeeping staff)
    users.value = await userApi.getUsers()
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

async function loadStatistics() {
  try {
    statistics.value = await housekeepingApi.getStatistics()
  } catch (err) {
    console.error('Failed to load statistics:', err)
  }
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    room_id: '',
    task_type: 'cleaning',
    priority: 'normal',
    assigned_to: '',
    notes: '',
  }
  error.value = ''
  showModal.value = true
}

function openEditModal(task) {
  isEditing.value = true
  formData.value = {
    id: task.id,
    room_id: task.room_id,
    task_type: task.task_type,
    priority: task.priority,
    assigned_to: task.assigned_to || '',
    notes: task.notes || '',
  }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  error.value = ''
}

async function saveTask() {
  saving.value = true
  error.value = ''

  try {
    if (isEditing.value) {
      await housekeepingApi.updateTask(formData.value.id, formData.value)
    } else {
      await housekeepingApi.createTask(formData.value)
    }
    
    closeModal()
    await loadTasks()
    await loadStatistics()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save task'
  } finally {
    saving.value = false
  }
}

async function updateStatus(taskId, status) {
  try {
    await housekeepingApi.updateTaskStatus(taskId, status)
    await loadTasks()
    await loadStatistics()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update status')
  }
}

function confirmDelete(task) {
  taskToDelete.value = task
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!taskToDelete.value) return
  
  deleting.value = true
  try {
    await housekeepingApi.deleteTask(taskToDelete.value.id)
    showDeleteConfirm.value = false
    taskToDelete.value = null
    await loadTasks()
    await loadStatistics()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete task')
  } finally {
    deleting.value = false
  }
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
  const labels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
  }
  return labels[status] || status
}

function getPriorityBadgeClass(priority) {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    normal: 'bg-blue-100 text-blue-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

function getPriorityLabel(priority) {
  const labels = {
    low: 'Low',
    normal: 'Normal',
    high: 'High',
    urgent: 'Urgent',
  }
  return labels[priority] || priority
}

function getTaskTypeBadgeClass(type) {
  const classes = {
    cleaning: 'bg-blue-100 text-blue-800',
    maintenance: 'bg-orange-100 text-orange-800',
    inspection: 'bg-purple-100 text-purple-800',
    deep_clean: 'bg-indigo-100 text-indigo-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

function getTaskTypeLabel(type) {
  const labels = {
    cleaning: 'Cleaning',
    maintenance: 'Maintenance',
    inspection: 'Inspection',
    deep_clean: 'Deep Clean',
  }
  return labels[type] || type
}
</script>
