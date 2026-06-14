# 🍽️ Meal Request System

A lightweight internal web application that lets employees request meals (Breakfast, Lunch, Dinner) within configurable daily cutoff times. Admins manage users via Excel import/export, and a Super Admin controls global system settings such as cutoff times and advance-booking windows.

Built with **Laravel 12**, **Bootstrap 5**, and **Laravel Excel**.

<p align="left">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License: MIT">
</p>

---

## ✨ Features

- **Role-based access** — `super_admin`, `admin`, and `user` roles with route-level middleware enforcement.
- **Time-aware meal requests** — meal options are automatically disabled once their cutoff time passes.
- **Advance booking** — users can request meals for today plus a configurable number of days ahead.
- **Excel-powered user management** — bulk import users from a spreadsheet and download a ready-made template.
- **Reports & exports** — admins view request reports and download them as Excel files.
- **Configurable settings** — Super Admins manage cutoff times, advance-request days, and can disable the request form with a custom message.
- **Secure auth** — username/password login with bcrypt password hashing and active/inactive account control.

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Bootstrap 5 |
| Database | MySQL / SQLite (default) |
| Excel | [maatwebsite/excel](https://laravel-excel.com) 3.x |
| Auth | Session-based, bcrypt hashing |
| Build tooling | Vite + Tailwind (asset pipeline) |

---

## 👥 User Roles

| Role | Capabilities |
|---|---|
| `super_admin` | Everything below **+** global system settings |
| `admin` | User management, Excel import/export, reports |
| `user` | View the request form and submit meal requests |

---

## ⏰ Meal Cutoff Rules

| Meal | Default Cutoff | Relative To |
|---|---|---|
| Breakfast | 11:59 PM | Previous day |
| Lunch | 10:00 AM | Same day |
| Dinner | 4:00 PM | Same day |

- When the current time exceeds a meal's cutoff, that option is disabled for the day.
- Users can book up to **N days in advance** (`advance_request_days`, default `2`).
- All cutoff times and the advance window are editable in **Admin → Settings**.

---

## 🚀 Getting Started

### Prerequisites

- PHP **8.2+**
- [Composer](https://getcomposer.org/)
- Node.js **18+** & npm
- A database (SQLite works out of the box; MySQL supported)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/svdilshan/meal_request.git
cd meal_request

# 2. Install PHP & JS dependencies
composer install
npm install

# 3. Set up the environment file
cp .env.example .env
php artisan key:generate

# 4. (SQLite) create the database file — skip if using MySQL
touch database/database.sqlite

# 5. Run migrations and seed default data
php artisan migrate --seed

# 6. Build front-end assets
npm run build      # or `npm run dev` for hot reloading

# 7. Start the development server
php artisan serve
```

The app will be available at **http://localhost:8000**.

### Using MySQL instead of SQLite

Update your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meal_request
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate --seed`.

---

## 🔑 Default Login

Seeding creates a Super Admin account:

| Username | Password | Role |
|---|---|---|
| `admin` | `Password123` | `super_admin` |

> ⚠️ **Change this password immediately** in any non-local environment.

---

## 🗄️ Database Overview

| Table | Purpose |
|---|---|
| `users` | Accounts with `epf_no`, `username`, `role`, `is_active` |
| `meal_types` | Configurable meal options (Breakfast, Lunch, Dinner, …) |
| `meal_requests` | One row per user/meal/date (unique constraint prevents duplicates) |
| `settings` | Key/value store for cutoff times, advance days, and form state |

Default seeders populate meal types, baseline settings, and the Super Admin user.

---

## 🛣️ Key Routes

| Method | URI | Access | Description |
|---|---|---|---|
| `GET` | `/login` | Public | Login form |
| `GET/POST` | `/request` | Authenticated | View & submit meal requests |
| `GET` | `/admin/dashboard` | admin, super_admin | Summary dashboard |
| `GET/POST` | `/admin/users` | admin, super_admin | Manage & import users |
| `GET` | `/admin/users/template` | admin, super_admin | Download import template |
| `GET` | `/admin/reports` | admin, super_admin | View reports |
| `GET` | `/admin/reports/download` | admin, super_admin | Export reports to Excel |
| `GET/POST` | `/admin/settings` | super_admin | Manage global settings |

---

## 📁 Project Structure

```
app/
├── Exports/          # Excel report exports
├── Helpers/          # MealCutoffHelper (cutoff logic)
├── Http/
│   ├── Controllers/  # Auth, MealRequest, Admin (Users, Reports, Settings)
│   └── Middleware/   # CheckRole (role-based access)
├── Imports/          # UsersImport (Excel user import)
└── Models/           # User, MealType, MealRequest, Setting
database/
├── migrations/       # Schema
└── seeders/          # MealType, Settings, SuperAdmin
resources/views/      # Blade templates (Bootstrap 5)
routes/web.php        # Application routes
```

A full functional specification is available in [project-spec.md](project-spec.md).

---

## 🧪 Testing

```bash
php artisan test
```

---

## 🤝 Contributing

Contributions are welcome! Please open an issue to discuss significant changes before submitting a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes
4. Push and open a pull request

---

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).
