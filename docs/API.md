# API Reference

Base URL (local): `http://localhost:8000/api`

All endpoints return JSON. "Staff" below means `developer`, `owner` and `admin`.

## Authentication

Cookie-based Sanctum SPA authentication. Log in through the web login page (`/login`), then call the API from the same origin with:

- `credentials: 'same-origin'`
- `Accept: application/json` and `Content-Type: application/json`
- `X-XSRF-TOKEN`: the URL-decoded value of the `XSRF-TOKEN` cookie (needed for POST/PUT/PATCH/DELETE)

```js
const xsrf = decodeURIComponent(
  document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN=')).split('=')[1]
);

const api = (method, url, body) =>
  fetch(url, {
    method,
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': xsrf,
    },
    body: body ? JSON.stringify(body) : undefined,
  }).then(async r => ({ status: r.status, body: await r.json().catch(() => null) }));
```

`GET /api/user` returns the currently logged-in user (includes `role`).

## Conventions

**Status codes**

| Code | Meaning |
|------|---------|
| 200 / 201 / 204 | Success / created / deleted (no body) |
| 401 | Not logged in |
| 403 | Logged in, but the role is not allowed (`{"message": "This action is unauthorized."}`) |
| 404 | Record not found |
| 422 | Validation or business-rule failure |

**422 shape**

```json
{
  "message": "This class is full on the selected date.",
  "errors": { "class_schedule_id": ["This class is full on the selected date."] }
}
```

Rule failures from custom actions (cancel, check-in) return `{"message": "..."}` without `errors`.

**Authorization vs. business rules:** 403 means "your role may not do this"; 422 means "you may, but not under the current conditions". Authorization is checked first.

**Dates and times**
- Date fields are ISO datetime strings (e.g. `"2026-11-23T00:00:00.000000Z"`); use the first 10 characters (`YYYY-MM-DD`).
- Time fields are `HH:MM:SS`; use the first 5 characters. Requests send `HH:MM`.
- The server runs in UTC, but the gym operates in WIB (Asia/Jakarta). "Today" and all time-window rules are computed in WIB.

**Money (IDR)**
- `memberships.price` is a decimal string (e.g. `"450000.00"`).
- `memberships.register_fee` and `private_sessions.price` are integers.

**Pagination** (list endpoints that paginate, 10 per page, `?page=N`): the response has `data`, `links` (`first`, `last`, `prev`, `next`) and `meta` (`current_page`, `last_page`, `per_page`, `total`, ...).

---

## Bookings

| Method | Path | Who |
|--------|------|-----|
| GET | `/bookings` | All roles. Members see only their own. Paginated. |
| POST | `/bookings` | developer, admin, member |
| GET | `/bookings/{id}` | Staff; member only for own booking |
| PUT/PATCH | `/bookings/{id}` | developer, admin |
| DELETE | `/bookings/{id}` | developer, admin (204) |
| PATCH | `/bookings/{id}/cancel` | developer, admin always; member only own, at least 4 hours before class start |
| PATCH | `/bookings/{id}/check-in` | developer, admin |

**Booking object**

```json
{
  "id": 12,
  "booking_date": "2026-11-23T00:00:00.000000Z",
  "status": "booked",
  "checked_in_at": null,
  "notes": null,
  "user": { "id": 4, "name": "Demo Member", "email": "member@gym.test", "role": "member", "phone": "0812..." },
  "class_schedule": {
    "id": 3, "class_name": "Muay Thai Basic", "level": "beginner", "day_of_week": "senin",
    "start_time": "18:00:00", "end_time": "19:00:00", "capacity": 15, "location": "Main Ring",
    "is_active": true,
    "coach": { "id": 1, "name": "Coach A", "email": null, "specialty": null, "phone": null, "created_at": "..." }
  }
}
```

`status` is one of `booked`, `cancelled`, `attended`.

**POST /bookings**

| Field | Required | Notes |
|-------|----------|-------|
| `class_schedule_id` | yes | Must exist |
| `booking_date` | yes | `YYYY-MM-DD`, today or later |
| `user_id` | staff only | Required for admin/developer. Ignored for members (forced to the logged-in user) |
| `notes` | no | String |

`status` is always set to `booked` by the server. Business rules (422):
- The class schedule must be active.
- `booking_date` must fall on the schedule's `day_of_week`.
- One booking per user, class and date (cancelled bookings also count).
- The class must have free capacity on that date. `booked` and `attended` bookings take a slot; `cancelled` ones do not.

**PUT/PATCH /bookings/{id}** (admin/developer): only `notes` can be changed (string, nullable, max 255). Other fields (`status`, `booking_date`, `class_schedule_id`) are ignored, not rejected: the request returns 200 and those fields stay unchanged. Status changes go through `/cancel` and `/check-in`. To move a booking to another class or date, cancel it and create a new one.

**PATCH /bookings/{id}/cancel**: sets `status` to `cancelled`. Member outside the 4-hour window gets 422: `Cancellation failed. Member can only cancel at least 4 hours before class start time.` Cancellation is final: the same user cannot book the same class on the same date again (422 on `booking_date`), because cancelled bookings still count toward the one-booking-per-user-class-date rule. To restore it, contact an admin.

**PATCH /bookings/{id}/check-in**: no body. Sets `status` to `attended` and fills `checked_in_at`. 422 if the status is not `booked`, or the booking date is not today (WIB).

---

## Memberships

| Method | Path | Who |
|--------|------|-----|
| GET | `/memberships` | Staff see all; members see their own. Paginated. |
| POST | `/memberships` | Member self-register, or staff on behalf of a member |
| GET | `/memberships/{id}` | Staff; member own (policy) |
| PUT/PATCH | `/memberships/{id}` | developer, admin (policy) |
| DELETE | `/memberships/{id}` | developer, admin (policy) |

**Membership object**

```json
{
  "id": 7,
  "type": "bulanan",
  "category": "dewasa",
  "package": "all_day_month",
  "price": "450000.00",
  "register_fee": 75000,
  "payment_status": "pending",
  "start_date": "2026-11-16T00:00:00.000000Z",
  "end_date": "2026-12-16T00:00:00.000000Z",
  "status": "active",
  "user": { "id": 4, "name": "Demo Member", "email": "member@gym.test", "role": "member", "phone": "0812..." }
}
```

**POST /memberships as member:** body is only `{ "package": "visit_1x" | "visit_10x" | "all_day_month" }`. The server fills everything else: dates (today in WIB; +1 day for `visit_1x`, +1 month otherwise), `payment_status: pending` (an admin confirms payment), and `status: active`.

**POST /memberships as staff:** `user_id`, `package`, `payment_status` (`pending`|`paid`), `start_date`, `end_date` (after `start_date`), `status` (`active`|`expired`), and optional `private_category` (`private_siang`|`private_malam`).

Pricing is always computed on the server:
- `category` comes from the member's date of birth at the start date (`pelajar` up to 17, `reguler` 18-24, `dewasa` 25+; minimum age 7), unless `private_category` is given.
- `price` comes from `config/pricelist.php` by category and package. Private categories only offer `visit_1x` and `visit_10x`.
- `register_fee` (Rp 75.000) applies only to the user's first membership.
- 422 if the member has no date of birth (`user_id`) or the package is unavailable for the category (`package`).

**PUT/PATCH /memberships/{id}:** only `payment_status`, `status`, `end_date` (must be after the start date) can change.

---

## Private sessions

| Method | Path | Who |
|--------|------|-----|
| GET | `/private-sessions` | All roles. Members see only their own. Paginated. |
| POST | `/private-sessions` | developer, admin, member |
| GET | `/private-sessions/{id}` | Staff; member own |
| PUT/PATCH | `/private-sessions/{id}` | developer, admin (approve / reject) |
| DELETE | `/private-sessions/{id}` | developer, admin (204) |
| PATCH | `/private-sessions/{id}/cancel` | developer, admin always; member only own, status `requested`/`approved`, at least 4 hours before start |

**Private session object**

```json
{
  "id": 5,
  "session_date": "2026-11-20T00:00:00.000000Z",
  "start_time": "08:00:00",
  "time_slot": "private_malam",
  "location_type": "gym",
  "address": null,
  "price": 125000,
  "status": "requested",
  "admin_note": null,
  "user": { "id": 4, "name": "Demo Member", "email": "member@gym.test", "role": "member", "phone": "0812..." },
  "coach": null
}
```

`status` is one of `requested`, `approved`, `rejected`, `cancelled`. `time_slot` is `private_siang` or `private_malam` (the latter is the morning/evening tariff).

**POST /private-sessions**

| Field | Required | Notes |
|-------|----------|-------|
| `session_date` | yes | `YYYY-MM-DD`, today or later |
| `start_time` | yes | `HH:MM` |
| `location_type` | yes | `gym` or `rumah` |
| `address` | if `rumah` | Max 255. Ignored when `gym` |
| `coach_id` | no | Must exist |
| `user_id` | staff only | Required for admin/developer; forced to self for members |

The server derives `time_slot` and `price` from `start_time` and sets `status: requested`. Rules (422 on `start_time`): start at or after 06:00, fixed 180-minute duration, end no later than 20:00. Slot `private_siang` starts between 11:00 and 17:00; any other valid start is `private_malam`.

**PUT/PATCH /private-sessions/{id}** (admin/developer): `status` (`approved`|`rejected`), `coach_id`, `admin_note` (max 255).

**PATCH /private-sessions/{id}/cancel:** member outside the window gets 422: `Cancellation failed. Member can only cancel at least 4 hours before the session starts.` Past sessions are always outside the window.

---

## Class schedules

| Method | Path | Who |
|--------|------|-----|
| GET | `/class-schedules`, `/class-schedules/{id}` | All roles |
| POST | `/class-schedules` | developer, admin |
| PUT/PATCH | `/class-schedules/{id}` | developer, admin |
| DELETE | `/class-schedules/{id}` | developer, admin |

Object: same as the `class_schedule` object inside a booking. The list is paginated (10 per page).

**POST body:** `class_name` (required), `level` (`beginner`|`intermediate`|`advanced`), `day_of_week` (`senin`...`minggu`), `start_time` (`HH:MM`), `end_time` (after start), `capacity` (integer, at least 1), optional `coach_id`, `location`, `is_active`. PUT/PATCH accepts the same fields, all optional, except that `end_time` is validated against `start_time` in the same request, so send both together when changing times.

---

## Coaches

Coaches are profile records only; they do not log in.

| Method | Path | Who |
|--------|------|-----|
| GET | `/coaches`, `/coaches/{id}` | Staff |
| POST | `/coaches` | developer, admin |
| PUT/PATCH | `/coaches/{id}` | developer, admin |
| DELETE | `/coaches/{id}` | developer, admin |

The list is paginated (10 per page).

Object: `id`, `name`, `email`, `specialty`, `phone`, `created_at`. Writable fields: `name`, `email`, `specialty`, `phone`. Validation: `name` required (max 255); `email` nullable, valid email (max 255); `specialty` nullable (max 255); `phone` nullable (max 20). On PUT/PATCH, all fields are optional.

---

## Users

`GET /users`: all users with role `member`, ordered by name, for dropdowns (developer, admin only). Not paginated: the response is `{ "data": [ ... ] }`. Each item has `id`, `name`, `email`, `role`, `phone` (the same shape as the `user` object inside a booking).

---

## Dashboard

`GET /dashboard`: any logged-in role. The response depends on the role. "Today" is computed in WIB.

**Staff (developer, owner, admin)**

```json
{
  "data": {
    "role_view": "staff",
    "date": "2026-11-16",
    "classes_today": [
      { "id": 3, "class_name": "Muay Thai Basic", "start_time": "18:00", "end_time": "19:00",
        "coach": "Coach A", "capacity": 15, "available_slots": 11 }
    ],
    "active_memberships": 24,
    "pending_payments": 5,
    "pending_private_sessions": 2
  }
}
```

`coach` is `null` when the schedule has no coach.

**Member**

```json
{
  "data": {
    "role_view": "member",
    "date": "2026-11-16",
    "membership": {
      "id": 7, "package": "all_day_month", "category": "dewasa",
      "end_date": "2026-12-16", "payment_status": "paid"
    },
    "upcoming_bookings": [
      { "id": 12, "booking_date": "2026-11-23", "class_name": "Muay Thai Basic", "start_time": "18:00" }
    ],
    "upcoming_private_sessions": [
      { "id": 5, "session_date": "2026-11-20", "start_time": "08:00", "status": "requested" }
    ]
  }
}
```

`membership` is `null` when the member has no active membership. Upcoming lists hold at most 5 items.