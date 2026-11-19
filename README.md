![Tests](https://github.com/ihsanqolbun/Gym-Management/actions/workflows/tests.yml/badge.svg)

# Muay Thai Gym Management System

A gym management app built to practice modern backend engineering: role-based access control, server-side business rules, pricing logic, timezone handling, and automated tests.

**Stack:** Laravel 13 (PHP 8.3), MySQL, Sanctum (SPA cookie auth), Inertia.js + React 18 + TypeScript, Vite, Tailwind.

API reference: [docs/API.md](docs/API.md)

## Features

- **Roles:** `developer`, `owner` (read-only), `admin`, `member`. Coaches have no login; they are profile records managed by admin.
- **Coaches & class schedules:** full CRUD, restricted to admin/developer.
- **Bookings:** members book classes for themselves; admin can book on behalf of a member.
- **Check-in:** admin/developer mark attendance on the booking date.
- **Memberships:** price is calculated on the server from age category and package. Members can self-register; admin confirms payment.
- **Private sessions:** members request, admin approves or rejects, members can cancel their own.
- **Dashboard:** one endpoint, two views. Staff get today's classes with free slots and pending work; members get their own membership and upcoming bookings.

## Business rules (enforced on the server)

**Booking**
- Class must be active, and the date must match the class day of week.
- Date cannot be in the past; class capacity is checked in real time from booking data (`booked` and `attended` bookings take a slot, `cancelled` ones do not).
- One booking per user, class and date (validated, and backed by a DB unique constraint).
- Members can cancel only up to 4 hours before class start; admin/developer can always cancel.
- Cancellation is final: a cancelled booking still counts toward the unique rule, so the same class and date cannot be booked again.
- Booking status changes only through the dedicated cancel and check-in endpoints; a general update can only edit notes.

**Check-in**
- Only `booked` bookings, only on the booking date. "Today" is calculated in WIB (Asia/Jakarta) while the app runs in UTC.

**Membership pricing**
- Category (`pelajar`, `reguler`, `dewasa`) derives from age at start date; private categories are chosen manually.
- Prices live in one place, `config/pricelist.php`, and are resolved by `App\Services\MembershipPricing`.
- A one-time registration fee applies only to a user's first membership.

**Private sessions**
- Start at or after 06:00, fixed 180 minutes, must end by 20:00.
- Simple state machine: only a `requested` session can be approved or rejected; `cancelled`, `approved` and `rejected` cannot be changed to another decision.
- Members can cancel only up to 4 hours before the session starts (computed in WIB); admin/developer can always cancel.

## Design decisions

- **Authorization vs. business rules:** policies answer "who may do this" (403); controllers and form requests answer "is this action valid right now" (422).
- **Single source of truth for pricing:** controllers, factories and seeders all use the same pricing service and config.
- **Timezone:** stored in UTC, business-day logic computed in WIB.
- **Real-time capacity:** available slots are computed from bookings, not a counter column that can drift.
- **Consistent seed data:** factories derive dates, statuses and prices from the same rules the API enforces, so seeded data never contradicts the business logic.

## Getting started

```bash
git clone https://github.com/ihsanqolbun/Gym-Management.git
cd Gym-Management
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set your MySQL credentials in `.env`, then:

```bash
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

Note: `migrate:fresh` drops all tables in the configured database before recreating them. Only run it against a database you are happy to reset.

Open `http://localhost:8000`.

### Demo accounts (password: `password`)

| Role | Email |
|------|-------|
| Developer | developer@gym.test |
| Owner | owner@gym.test |
| Admin | admin@gym.test |
| Member | member@gym.test |

## Tests

```bash
php artisan test
```

Feature tests run against in-memory SQLite and cover booking validation, check-in rules (including the UTC/WIB boundary), private session approval and cancellation rules, dashboard summaries, and role permissions. Requirements:

- The PHP `pdo_sqlite` extension must be enabled.
- Keep `npm run dev` running (or run `npm run build` first), because the default Breeze page tests render Inertia pages that need the Vite assets.

GitHub Actions runs `npm run build` and the full test suite on every push to `main`.

## Status

Backend is complete for the resources above. The frontend currently uses plain HTML to verify logic; UI design is planned as a separate pass.

## Roadmap

- Navigation and layout, then UI design
- Create Booking page