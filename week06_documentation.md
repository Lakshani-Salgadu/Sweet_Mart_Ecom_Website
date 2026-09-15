# Sweet Mart – Week 06 Project Documentation & Report
## User Authentication & Profile Management System

---

## 1. Authentication Architecture

The user authentication and session management flow is designed following industry standard security practices and the existing Sweet Mart architecture.

### Architectural Flowchart

```
[User / Guest]
      │
      ├───► 1. Registration (login.php?tab=register OR POST /api/register.php)
      │         │
      │         ├── Input Validation (Full Name, Email, Password Complexity, Password Match)
      │         ├── Duplicate Email Check (Prepared SQL query)
      │         ├── Secure Password Hashing (password_hash() with PASSWORD_BCRYPT)
      │         └── Database Insertion (users + user_profiles tables)
      │                 │
      │                 └──► Success Flash Message & Redirect to Login Tab
      │
      ├───► 2. Login (login.php?tab=login OR POST /api/login.php)
      │         │
      │         ├── Prepared SQL Query (Lookup account by submitted email)
      │         ├── Cryptographic Verification (password_verify() against stored bcrypt hash)
      │         │     ├── Failure ──► Generic Error: "Invalid email or password."
      │         │     └── Success ──► Proceed to Session Creation
      │         │
      │         ├── Session Regeneration: session_regenerate_id(true) [Fixation Prevention]
      │         ├── Store Session Data: user_id, full_name, email, role (No password stored)
      │         └── Redirection: Return to preserved $redirect target (e.g. checkout.php),
      │                          admin dashboard (if admin), or homepage (index.php)
      │
      ├───► 3. Dynamic Navigation (includes/header.php)
      │         │
      │         ├── Unauthenticated: Displays public links + "Login" button + Cart
      │         └── Authenticated: Displays user name with account dropdown:
      │                 ├── 👤 My Profile (profile.php)
      │                 ├── 📦 My Orders (my_orders.php)
      │                 ├── ⚙️ Admin Panel (admin/index.php - admin role only)
      │                 └── 🚪 Logout (logout.php)
      │
      ├───► 4. Protected Routes
      │         ├── Profile Dashboard (profile.php)
      │         ├── Checkout (checkout.php)
      │         └── My Orders (my_orders.php)
      │                 │
      │                 └──► Unauthenticated requests redirected to login with ?redirect=...
      │
      ├───► 5. Profile Management (profile.php OR /api/profile.php)
      │         ├── Displays: Name, Email, Phone, Shipping Address, Billing Address, Member Since, Status
      │         ├── Edits: Name, Phone, Shipping Address, Billing Address (via prepared statements)
      │         └── Password Update: Current password check + new password bcrypt hashing
      │
      └───► 6. Logout (logout.php OR POST /api/logout.php)
                ├── Clear $_SESSION array ($_SESSION = [])
                ├── Expire and clear session cookie
                ├── Invalidate session: session_destroy()
                └── Redirect to public Homepage with confirmation flash message
```

---

## 2. Debugging Notes

The following real issues were encountered and resolved during development:

### Issue 1: Plaintext Passwords in Existing Database Records
* **Problem**: In the pre-existing database, records for users (`mdlsalgadu01@gmail.com` and `omethrathisagi@gmail.com`) contained plaintext string values (`customer`) in the `password` column instead of cryptographic hashes.
* **Cause**: Sample accounts had been inserted during early development without running them through PHP's `password_hash()` function.
* **Solution**: Developed a non-destructive database migration script (`scratch/migrate_db.php`) that identified password values not matching bcrypt hash signatures (`$2y$`) and automatically upgraded them using `password_hash($pwd, PASSWORD_BCRYPT)`.
* **Result**: 100% of passwords stored in the database are now securely hashed with Bcrypt. No plain text passwords remain.

### Issue 2: Destination Loss on Login Redirects
* **Problem**: When an unauthenticated customer clicked "Proceed to Checkout" on `cart.php`, they were redirected to `login.php`, but after logging in, they were redirected to `index.php` instead of back to `checkout.php`, disrupting the purchase flow.
* **Cause**: The previous `login.php` did not inspect or propagate destination parameters, and `checkout.php` lacked an authentication check with redirect preservation.
* **Solution**: Updated `requireLogin()` in `includes/functions.php` to append `?redirect=` with the current page URL (`checkout.php`). Updated `login.php` and `checkout.php` to preserve and validate `$redirect` against an allowlist of local routes.
* **Result**: Customers are smoothly redirected to `checkout.php` immediately upon successful authentication, with their session cart fully intact.

### Issue 3: Missing Profile and Address Columns in Schema
* **Problem**: The existing `users` table did not contain dedicated fields for shipping and billing addresses or a `password_hash` column specified in the Week 06 requirements.
* **Cause**: Initial table schema only accounted for basic contact information (`full_name`, `email`, `phone`).
* **Solution**: Safely executed `ALTER TABLE` queries to add `password_hash VARCHAR(255)`, `shipping_address TEXT`, `billing_address TEXT`, and a virtual generated column `user_id INT GENERATED ALWAYS AS (id) VIRTUAL` to maintain compatibility with queries expecting either `id` or `user_id`. Also established a synchronized `user_profiles` table.
* **Result**: User profile addresses are stored and pre-filled during checkout, and all existing table foreign key references (`orders`, `cart`, `custom_boxes`) remain intact.

---

## 3. Security Considerations

The following security controls and best practices were implemented:

1. **Password Hashing**:
   - Implemented using PHP's standard `password_hash($password, PASSWORD_BCRYPT)`.
   - Never stores plain text passwords.
   - Authenticates via `password_verify($password, $storedHash)` which uses a constant-time comparison to prevent timing attacks.

2. **Session Security & Fixation Prevention**:
   - Calls `session_regenerate_id(true)` immediately after successful authentication to eliminate session fixation vulnerabilities.
   - Stores only minimal user identity information in `$_SESSION` (`user_id`, `full_name`, `email`, `role`).
   - Sensitive password hashes and credentials are never stored in session storage.
   - On logout, clears `$_SESSION`, expires the session cookie with past timestamp, and executes `session_destroy()`.

3. **Prepared SQL Statements (SQL Injection Defense)**:
   - All user-supplied inputs across registration, login, profile updates, and order queries are bound using parameterized prepared statements (`$conn->prepare()`, `$stmt->bind_param()`, `$stmt->execute()`).
   - Eliminated direct string concatenation in queries (e.g. replaced `WHERE user_id=$uid` in `my_orders.php` with `$stmt->bind_param("i", $uid)`).

4. **Prevention of User Enumeration**:
   - Login failure messages use generic wording: `"Invalid email or password."`.
   - Never indicates whether an email address exists or whether only the password was incorrect.

5. **Server-Side & Client-Side Validation**:
   - Server-side validation enforces required fields, RFC-compliant email formatting (`filter_var(..., FILTER_VALIDATE_EMAIL)`), and password complexity (minimum 8 characters, at least one letter and at least one number).
   - Passwords and confirmation passwords must match.
   - Duplicate email checks prevent account collisions.

6. **Open Redirect Protection**:
   - The `$redirect` parameter in `login.php` is strictly validated against an internal allowlist (`checkout.php`, `profile.php`, `my_orders.php`, `cart.php`, `custom_box.php`, `shop.php`, `index.php`) to prevent open redirect vulnerabilities.

7. **Role-Based Access Control (RBAC)**:
   - Evaluates `$_SESSION['role']` against `'admin'` in `isAdmin()` and `requireAdmin()`.
   - Customers cannot access admin routes or dashboard controls.

---

## 4. API & Backend Endpoints

The following authentication-related endpoints and handlers were implemented:

### 1. User Registration
* **Web Route**: `POST login.php` (form field: `action_register`)
* **REST API**: `POST api/register.php`
* **Request Payload**:
  - `full_name` (string, required)
  - `email` (string, required, valid email format)
  - `phone` (string, optional)
  - `password` (string, required, min 8 chars, letters + numbers)
  - `confirm_password` (string, required, must match password)
* **Response**:
  - Web: Flash success message + redirect to `login.php?tab=login`
  - API: `201 Created` with JSON: `{"success": true, "message": "Registration successful. Please log in.", "user_id": <id>}`
  - Errors: `400 Bad Request` or `409 Conflict` (if email already registered)

### 2. User Login
* **Web Route**: `POST login.php` (form field: `action_login`)
* **REST API**: `POST api/login.php`
* **Request Payload**:
  - `email` (string, required)
  - `password` (string, required)
  - `redirect` (string, optional, internal path)
* **Response**:
  - Web: Regenerates session ID, stores session keys, redirects to target or homepage
  - API: `200 OK` with JSON: `{"success": true, "message": "Login successful", "user": {"user_id": <id>, "full_name": "...", "email": "...", "role": "..."}}`
  - Errors: `401 Unauthorized` with generic message `{"success": false, "message": "Invalid email or password."}`

### 3. Profile Retrieval
* **Web Route**: `GET profile.php`
* **REST API**: `GET api/profile.php`
* **Access**: Authenticated users only (`isLoggedIn()`)
* **Response**:
  - Web: Renders Sweet Mart Profile Dashboard showing user info, phone, addresses, registration date, and status.
  - API: `200 OK` with JSON:
    ```json
    {
      "success": true,
      "profile": {
        "user_id": 1,
        "full_name": "Nimal Perera",
        "email": "nimal@example.com",
        "phone": "+94 71 234 5678",
        "shipping_address": "45/A, Galle Road, Wellawatte",
        "billing_address": "45/A, Galle Road, Wellawatte",
        "role": "customer",
        "created_at": "2026-08-12 12:40:33"
      }
    }
    ```

### 4. Profile Update
* **Web Route**: `POST profile.php` (form field: `action_update_profile`)
* **REST API**: `POST / PUT api/profile.php`
* **Request Payload**:
  - `full_name` (string, required)
  - `phone` (string, optional)
  - `shipping_address` (string, optional)
  - `billing_address` (string, optional)
* **Response**:
  - Web: Flash success message + redirect to `profile.php`
  - API: `200 OK` with JSON: `{"success": true, "message": "Profile updated successfully.", "profile": {...}}`

### 5. Password Change
* **Web Route**: `POST profile.php` (form field: `action_change_password`)
* **Request Payload**:
  - `current_password` (string, required)
  - `new_password` (string, required, min 8 chars, letter + number)
  - `confirm_new_password` (string, required)
* **Response**: Validates current password, updates database with new bcrypt hash, sets flash message.

### 6. User Logout
* **Web Route**: `GET / POST logout.php`
* **REST API**: `POST api/logout.php`
* **Action**: Clears `$_SESSION`, expires cookie, destroys session.
* **Response**:
  - Web: Redirects to `index.php` with logout confirmation alert.
  - API: `200 OK` with JSON: `{"success": true, "message": "Logged out successfully."}`

---

## 5. Summary of Preserved Features
- **Branding & Visuals**: Sweet Mart logo, color palette (`#FFF8F2`, `#E8A0A0`, `#6B3F2A`, `#3B2212`), Playfair Display & Poppins typography, and card stylings.
- **Cart & Guest Shopping**: Guest cart items stored in `$_SESSION['cart']` and `$_SESSION['custom_box']` remain 100% functional and persist after login.
- **Shop & Filtering**: Product listings, categories, search, price filters, custom sweet box builder, about us, and contact pages continue working as designed.
- **Admin Panel**: Preserved administrative functionality and seed admin account (`admin@sweetmart.lk` / `password`).
