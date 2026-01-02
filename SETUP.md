# Setup Instructions for HotelOne

## Quick Start Guide

### 1. Setup PostgreSQL Database

```sql
-- Login to PostgreSQL as superuser
psql -U postgres

-- Create database
CREATE DATABASE hotel;

-- Create user
CREATE USER hotel WITH PASSWORD 'hotel';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE hotel TO hotel;

-- Exit
\q
```

### 2. Backend Setup

```bash
# Navigate to backend
cd backend

# Install dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start server
php artisan serve
```

Backend will be available at: http://localhost:8000

### 3. Frontend Setup

```bash
# Navigate to frontend
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

Frontend will be available at: http://localhost:5173

### 4. Login

Open browser and navigate to: http://localhost:5173

Use these credentials:
- **Email:** owner@hotel.com
- **Password:** password

## Database Configuration

Make sure your `.env` file in the backend folder has these settings:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=hotel
DB_USERNAME=hotel
DB_PASSWORD=hotel

FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DRIVER=cookie
```

## Default Users

After running seeders, you'll have these users:

1. **Owner**
   - Email: owner@hotel.com
   - Password: password
   - Role: Full access

2. **Front Desk**
   - Email: frontdesk@hotel.com
   - Password: password
   - Role: Front Office

3. **Housekeeping**
   - Email: housekeeping@hotel.com
   - Password: password
   - Role: Housekeeping

## Sample Data

The seeder will create:
- 5 roles (Owner, Manager, Front Office, Housekeeping, Accounting)
- 3 users
- 3 room types (Standard, Deluxe, Suite)
- 10 rooms total:
  - 5 Standard rooms (101-105)
  - 3 Deluxe rooms (201-203)
  - 2 Suite rooms (301-302)

## Troubleshooting

### Database Connection Error
Make sure PostgreSQL is running and the database/user exists:
```bash
# Check PostgreSQL status (Windows)
Get-Service postgresql*

# Check PostgreSQL status (Linux/Mac)
sudo systemctl status postgresql
```

### Frontend Cannot Connect to Backend
1. Check CORS settings in `backend/config/cors.php`
2. Verify `FRONTEND_URL` in backend `.env` file
3. Make sure both servers are running

### Session/Cookie Issues
1. Clear browser cookies
2. Check that `SESSION_DRIVER=cookie` in `.env`
3. Verify `SANCTUM_STATEFUL_DOMAINS` includes your frontend URL

## API Testing

You can test the API using tools like Postman or curl:

```bash
# Get CSRF cookie first
curl -X GET http://localhost:8000/sanctum/csrf-cookie -c cookies.txt

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@hotel.com","password":"password"}' \
  -b cookies.txt -c cookies.txt

# Get rooms
curl -X GET http://localhost:8000/api/rooms \
  -H "Accept: application/json" \
  -b cookies.txt
```

## Development

### Backend Commands
```bash
# Create new migration
php artisan make:migration create_something_table

# Create new model
php artisan make:model ModelName

# Create new controller
php artisan make:controller Api/ControllerName

# Run specific seeder
php artisan db:seed --class=SeederName

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Frontend Commands
```bash
# Install new package
npm install package-name

# Build for production
npm run build

# Preview production build
npm run preview
```

## Production Deployment Notes

### Backend
1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Set proper database credentials
5. Configure proper domain in `SANCTUM_STATEFUL_DOMAINS`

### Frontend
1. Build: `npm run build`
2. Deploy `dist` folder to web server
3. Configure proper API URL
4. Ensure HTTPS for production

## Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- Vue.js Documentation: https://vuejs.org/guide
- Tailwind CSS: https://tailwindcss.com/docs
