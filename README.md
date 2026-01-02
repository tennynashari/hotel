# HotelOne - Hotel Management System

Aplikasi manajemen perhotelan berbasis web untuk mengelola reservasi, kamar, tamu, pembayaran, housekeeping, dan laporan operasional.

## 🚀 Tech Stack

### Backend
- Laravel 10
- PostgreSQL
- Laravel Sanctum (Cookie-based authentication)

### Frontend
- Vue.js 3
- Tailwind CSS
- Vue Router
- Pinia (State Management)
- Axios

## 📋 Prerequisites

- PHP 8.1+
- Composer
- PostgreSQL
- Node.js 18+
- npm

## 🛠️ Installation

### Backend Setup

1. Navigate to backend directory:
```bash
cd backend
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment variables in `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=hotel
DB_USERNAME=hotel
DB_PASSWORD=hotel
```

4. Run migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

5. Start Laravel development server:
```bash
php artisan serve
```

Backend will run on: `http://localhost:8000`

### Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Start development server:
```bash
npm run dev
```

Frontend will run on: `http://localhost:5173`

## 👤 Default Login Credentials

**Owner Account:**
- Email: `owner@hotel.com`
- Password: `password`

**Front Office:**
- Email: `frontdesk@hotel.com`
- Password: `password`

**Housekeeping:**
- Email: `housekeeping@hotel.com`
- Password: `password`

## 📁 Project Structure

```
hotel/
├── backend/              # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/Api/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       └── api.php
├── frontend/             # Vue.js SPA
│   ├── src/
│   │   ├── api/         # API services
│   │   ├── components/  # Vue components
│   │   ├── router/      # Vue Router
│   │   ├── stores/      # Pinia stores
│   │   └── views/       # Page views
│   └── tailwind.config.js
└── README.md
```

## 🎯 Features

### Phase 1 (MVP) - Implemented
- ✅ User Authentication (Cookie-based with Sanctum)
- ✅ Role Management (Owner, Manager, Front Office, Housekeeping, Accounting)
- ✅ Room Management
- ✅ Room Types Configuration
- ✅ Guest Management
- ✅ Booking Management
- ✅ Payment Processing
- ✅ Housekeeping Task Management
- ✅ Audit Logs

### Database Schema
- **users** - System users with roles
- **roles** - User roles and permissions
- **room_types** - Room type configurations
- **rooms** - Hotel rooms inventory
- **guests** - Guest information
- **bookings** - Reservations
- **booking_rooms** - Booking-room relationships
- **payments** - Payment transactions
- **housekeeping_tasks** - Cleaning and maintenance tasks
- **audit_logs** - System activity logs

## 🔐 Authentication Flow

1. Frontend requests CSRF cookie from `/sanctum/csrf-cookie`
2. Login request with credentials to `/api/login`
3. Laravel creates session (HTTP-only cookie)
4. Subsequent requests authenticated via session cookie
5. No localStorage or manual token management needed

## 🌐 API Endpoints

### Authentication
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/user` - Get authenticated user

### Rooms
- `GET /api/rooms` - List rooms
- `GET /api/room-types` - List room types
- `POST /api/rooms` - Create room
- `PATCH /api/rooms/{id}/status` - Update room status

### Bookings
- `GET /api/bookings` - List bookings
- `POST /api/bookings` - Create booking
- `POST /api/bookings/{id}/check-in` - Check-in
- `POST /api/bookings/{id}/check-out` - Check-out

### Guests
- `GET /api/guests` - List guests
- `POST /api/guests` - Create guest
- `GET /api/guests/search/{query}` - Search guests

### Housekeeping
- `GET /api/housekeeping-tasks` - List tasks
- `PATCH /api/housekeeping-tasks/{id}/status` - Update task status

## 🔧 Development

### Backend Commands
```bash
# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName

# Create controller
php artisan make:controller Api/ControllerName

# Run tests
php artisan test
```

### Frontend Commands
```bash
# Run development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

## 📝 Notes

- Backend API runs on port 8000
- Frontend runs on port 5173
- CORS is configured for localhost:5173
- Sessions use HTTP-only cookies for security
- Database uses PostgreSQL (not MySQL)

## 🚧 Upcoming Features (Phase 2)

- OTA Integration
- Online Booking Engine
- Payment Gateway Integration
- Multi-property Support
- Advanced Reporting
- Email Notifications

## 📄 License

This project is part of a hotel management system PRD implementation.
