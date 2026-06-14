# Meal Request System
### Project Specification Document
**Laravel 11 · MySQL · Bootstrap 5 · Excel Export/Import**

---

## 1. Project Overview

A lightweight internal web application that allows employees to request meals (Breakfast, Lunch, Dinner, etc.) within defined cutoff times. Admins manage users via Excel upload. A Super Admin controls global system settings.

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Frontend | Blade + Bootstrap 5 (brand theme colors) |
| Database | MySQL |
| Auth | Laravel Breeze / custom session auth |
| Excel | Maatwebsite/Laravel-Excel (v3.x) |
| Encryption | Laravel `bcrypt` (password hashing) |

---

## 3. Theme / Branding

Brand visual identity:

| Token | Value |
|---|---|
| Primary Color | `#003087` (Navy Blue) |
| Accent / Highlight | `#E30613` (Red) |
| Background | `#F5F5F5` |
| Card Background | `#FFFFFF` |
| Text Primary | `#222222` |
| Text Muted | `#6C757D` |
| Font | `Roboto` / `Open Sans`, sans-serif |

> Update these values once the live site theme is confirmed.

---

## 4. User Roles

| Role | Description |
|---|---|
| `super_admin` | Full access + system settings |
| `admin` | User management + Excel upload/download |
| `user` | View request form + submit meal requests |

---

## 5. Database Schema

### `users`
```sql
id             BIGINT PK AUTO_INCREMENT
epf_no         VARCHAR(20) UNIQUE NOT NULL
name           VARCHAR(100) NOT NULL
username       VARCHAR(50) UNIQUE NOT NULL
password       VARCHAR(255) NOT NULL  -- bcrypt hashed
role           ENUM('super_admin','admin','user') DEFAULT 'user'
is_active      TINYINT(1) DEFAULT 1
created_at     TIMESTAMP
updated_at     TIMESTAMP
```

### `meal_types`
```sql
id             BIGINT PK AUTO_INCREMENT
name           VARCHAR(50) NOT NULL   -- e.g. Breakfast, Lunch, Dinner
slug           VARCHAR(50) NOT NULL   -- breakfast, lunch, dinner
is_active      TINYINT(1) DEFAULT 1
sort_order     INT DEFAULT 0
created_at     TIMESTAMP
updated_at     TIMESTAMP
```

### `meal_requests`
```sql
id             BIGINT PK AUTO_INCREMENT
user_id        BIGINT FK → users.id
meal_type_id   BIGINT FK → meal_types.id
request_date   DATE NOT NULL          -- the date the meal is FOR
submitted_at   TIMESTAMP NOT NULL     -- when the request was made
created_at     TIMESTAMP
updated_at     TIMESTAMP
UNIQUE(user_id, meal_type_id, request_date)
```

### `settings`
```sql
id             BIGINT PK AUTO_INCREMENT
key            VARCHAR(100) UNIQUE NOT NULL
value          TEXT
updated_by     BIGINT FK → users.id
updated_at     TIMESTAMP
```

**Default settings keys:**
```
breakfast_cutoff_time     → "23:59" (previous day)
lunch_cutoff_time         → "10:00" (same day)
dinner_cutoff_time        → "16:00" (same day)
advance_request_days      → "2"
form_disabled             → "0"    (0 = enabled, 1 = disabled)
form_disabled_message     → "The meal request form is currently disabled. Please contact the admin."
```

---

## 6. Meal Cutoff Rules

| Meal | Cutoff | Relative To |
|---|---|---|
| Breakfast | 11:59 PM | **Previous day** |
| Lunch | 10:00 AM | **Same day** |
| Dinner | 4:00 PM | **Same day** |

- If the current time **exceeds** the cutoff, that meal option is **disabled** (grayed out) for today.
- Users can request meals up to **N days in advance** (configurable via settings, default = 2).
- So on any given session, a user sees meal request options for: **today** (if cutoff not passed) **+ next N days**.

### Cutoff Logic (PHP pseudocode)
```php
function isMealAvailable(string $mealSlug, Carbon $requestDate): bool
{
    $now = Carbon::now();
    $cutoffs = [
        'breakfast' => $requestDate->copy()->subDay()->setTimeFromTimeString(
                            Setting::get('breakfast_cutoff_time', '23:59')),
        'lunch'     => $requestDate->copy()->setTimeFromTimeString(
                            Setting::get('lunch_cutoff_time', '10:00')),
        'dinner'    => $requestDate->copy()->setTimeFromTimeString(
                            Setting::get('dinner_cutoff_time', '16:00')),
    ];
    return $now->lessThan($cutoffs[$mealSlug]);
}
```

---

## 7. Module Breakdown

---

### 7.1 Authentication

- Route: `/login`
- Fields: `Username`, `Password`
- On success → redirect by role:
  - `user` → `/request`
  - `admin` → `/admin/dashboard`
  - `super_admin` → `/admin/dashboard`
- Logout: `/logout`
- No self-registration. Accounts created by admin only.

---

### 7.2 User — Meal Request Form

**Route:** `GET /request`

**Display Logic:**
1. Check `form_disabled` setting. If `1`, show disabled message and hide form.
2. Build available date tabs: Today + next N days (from `advance_request_days` setting).
3. For each date tab, show meal type radio buttons.
4. Disable (grey out) any meal whose cutoff has passed.
5. Pre-check if user already has a request for that date+meal (show as submitted).

**Form Fields:**

| Field | Type | Notes |
|---|---|---|
| Name | Text (read-only) | Auto-filled from logged-in user |
| EPF No | Text (read-only) | Auto-filled from logged-in user |
| Date | Date selector / Tab | Selectable from today to +N days |
| Meal Type | Radio buttons | Breakfast / Lunch / Dinner (disabled if cutoff passed) |

**Submit Rules:**
- Cannot submit if meal cutoff has passed.
- Cannot submit duplicate (same user + meal + date).
- On success → show success toast/message.

---

### 7.3 Admin — User Management

**Route:** `GET /admin/users`

#### 7.3.1 Upload Users via Excel

- Route: `POST /admin/users/import`
- Accept `.xlsx` / `.xls` file
- Expected columns:

| Column | Notes |
|---|---|
| EPF No | Unique identifier |
| Name | Full name |
| Username | Login username |
| Password | Plain text in Excel → bcrypt hashed on import |

- On import:
  - If EPF No exists → **update** name, username, password (if password column is not blank).
  - If new → **insert** with role `user`.
- Show import summary: `X created, Y updated, Z failed`.

#### 7.3.2 Password Reset

- Route: `POST /admin/users/{id}/reset-password`
- Admin sets a new temporary password.
- Password bcrypt hashed before saving.

#### 7.3.3 User List Table

Columns: `EPF No | Name | Username | Role | Status | Actions`

Actions: Edit | Reset Password | Activate/Deactivate

---

### 7.4 Admin — Excel Report Download

**Route:** `GET /admin/reports/download`

Filter by **date range** (date picker).

**Output:** `.xlsx` file named `YYYY-MM-DD_HH-mm-ss.xlsx`

**Sheets:**

| Sheet | Content |
|---|---|
| `Breakfast` | Table: Name, EPF No — all breakfast requests in range |
| `Lunch` | Table: Name, EPF No — all lunch requests in range |
| `Dinner` | Table: Name, EPF No — all dinner requests in range |
| `Summary` | Breakfast count, Lunch count, Dinner count, **Total** |

**Summary Sheet Format:**

| Meal Type | Count |
|---|---|
| Breakfast | 12 |
| Lunch | 34 |
| Dinner | 8 |
| **Total** | **54** |

---

### 7.5 Super Admin — Settings

**Route:** `GET /admin/settings`

| Setting | Field Type | Default |
|---|---|---|
| Breakfast Cutoff Time | Time input | `23:59` (previous day) |
| Lunch Cutoff Time | Time input | `10:00` (same day) |
| Dinner Cutoff Time | Time input | `16:00` (same day) |
| Advance Request Days | Number input | `2` |
| Disable Request Form | Toggle (On/Off) | Off |
| Form Disabled Message | Textarea | "Contact admin..." |

> Only `super_admin` role can access this page.
> Settings saved to `settings` table. Read via `Setting::get($key)` helper.

---

## 8. Routes Summary

```
GET   /                          → redirect to /login
GET   /login                     → Login page
POST  /login                     → Auth handler
POST  /logout                    → Logout

-- User --
GET   /request                   → Meal request form
POST  /request                   → Submit meal request

-- Admin --
GET   /admin/dashboard           → Admin dashboard
GET   /admin/users               → User list
POST  /admin/users/import        → Import users from Excel
POST  /admin/users/{id}/reset    → Reset user password
GET   /admin/reports/download    → Download Excel report

-- Super Admin --
GET   /admin/settings            → Settings page
POST  /admin/settings            → Save settings
```

---

## 9. Middleware

| Middleware | Applied To |
|---|---|
| `auth` | All `/request`, `/admin/*` routes |
| `role:admin,super_admin` | All `/admin/*` routes |
| `role:super_admin` | `/admin/settings` only |

---

## 10. Folder Structure (Key Files)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── MealRequestController.php
│   │   ├── Admin/
│   │   │   ├── UserController.php
│   │   │   ├── ReportController.php
│   │   │   └── SettingsController.php
│   ├── Middleware/
│   │   └── CheckRole.php
├── Models/
│   ├── User.php
│   ├── MealType.php
│   ├── MealRequest.php
│   └── Setting.php
├── Imports/
│   └── UsersImport.php          ← Maatwebsite import class
├── Exports/
│   └── MealReportExport.php     ← Maatwebsite multi-sheet export
├── Helpers/
│   └── MealCutoffHelper.php     ← cutoff time logic

resources/views/
├── layouts/
│   ├── app.blade.php            ← main layout (brand theme)
│   └── auth.blade.php           ← login layout
├── auth/
│   └── login.blade.php
├── request/
│   └── index.blade.php          ← user meal request form
├── admin/
│   ├── dashboard.blade.php
│   ├── users/
│   │   └── index.blade.php
│   ├── reports/
│   │   └── index.blade.php
│   └── settings/
│       └── index.blade.php

database/migrations/
├── create_users_table.php
├── create_meal_types_table.php
├── create_meal_requests_table.php
└── create_settings_table.php

database/seeders/
├── SuperAdminSeeder.php
├── MealTypeSeeder.php
└── SettingsSeeder.php
```

---

## 11. Excel Import Format (Template)

File: `user_import_template.xlsx`

| EPF No | Name | Username | Password |
|---|---|---|---|
| 1001 | John Silva | john.silva | Pass@1234 |
| 1002 | Nimal Perera | nimal.p | Pass@5678 |

**Rules:**
- All columns required for new users.
- Password column may be blank for existing users (password unchanged).
- EPF No is the unique identifier for upsert logic.

---

## 12. Excel Export Format

**Filename:** `2025-06-11_14-30-00.xlsx`

### Sheet 1 — Breakfast

| Name | EPF No |
|---|---|
| John Silva | 1001 |

### Sheet 2 — Lunch

| Name | EPF No |
|---|---|
| Nimal Perera | 1002 |

### Sheet 3 — Dinner

| Name | EPF No |
|---|---|
| Kamal Fernando | 1003 |

### Sheet 4 — Summary

| Meal Type | Count |
|---|---|
| Breakfast | 12 |
| Lunch | 34 |
| Dinner | 8 |
| **Total** | **54** |

---

## 13. UI Behavior Notes

| Scenario | Behavior |
|---|---|
| Cutoff time passed for a meal | Radio button disabled + greyed out label |
| Form globally disabled | Hide form, show admin message |
| Already submitted a meal | Show "Already Requested" badge on that option |
| Duplicate submit attempt | Show validation error |
| Excel import success | Show summary: created / updated / failed |
| Excel import column mismatch | Show error with expected format |

---

## 14. Packages to Install

```bash
composer require maatwebsite/excel
composer require laravel/breeze --dev   # or custom auth
php artisan breeze:install blade
```

`config/app.php` — add provider:
```php
Maatwebsite\Excel\ExcelServiceProvider::class,
```

---

## 15. Development Phases

| Phase | Scope |
|---|---|
| Phase 1 | Auth + user meal request form + cutoff logic |
| Phase 2 | Admin user management + Excel import |
| Phase 3 | Excel report download (4-sheet) |
| Phase 4 | Super Admin settings panel |
| Phase 5 | UI polish to match brand theme + testing |

---

## 16. Deployment Notes

- Domain: configurable (set in `.env`)
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Set `SESSION_DRIVER=database` or `file`
- Configure `.env` with production DB credentials
- Run: `php artisan migrate --seed`
- Run: `php artisan config:cache && php artisan route:cache`
- Ensure `storage/` and `bootstrap/cache/` are writable

---

*Document version 1.0 — Laravel development*
