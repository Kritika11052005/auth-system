# Organic Ink Auth System

A premium, full-stack authentication system built with the **Organic Ink** design system — a theme that feels like a boutique developer notebook: grounded, calm, and typographically rich.

## Tech Stack
- **Frontend:** HTML5, CSS3 (Vanilla + Bootstrap 5 Grid), jQuery (AJAX)
- **Backend:** PHP 8.1+
- **Auth DB:** MySQL (Prepared Statements)
- **Profile DB:** MongoDB (Atlas)
- **Sessions:** Redis (Redis Cloud)
- **Security:** CSRF-safe AJAX, timing-attack safe login, bcrypt password hashing, Bearer Token middleware.

## Directory Structure
- `assets/css/`: Main design system and page-specific styles.
- `assets/js/`: Utilities, registration, login, and profile logic.
- `php/config/`: Database and environment configurations.
- `php/middleware/`: Authentication guard.
- `php/`: Backend API endpoints.

## Setup
1. Rename `.env.example` to `.env` and fill in your credentials.
2. Import `schema.sql` into your MySQL database.
3. Run `composer require mongodb/mongodb` to install PHP dependencies.
4. Ensure Redis and MongoDB are running and accessible.
5. Serve the project using a PHP server (e.g., `php -S localhost:8000`).

## Design Highlights
- **Typography:** Outfit for UI, JetBrains Mono for data, Cormorant Garamond for hero titles.
- **Animations:** Smooth slide-ins, staggered child entrance, and shake animations for errors.
- **Theme:** Seamless toggle between "Warm Parchment" (Light) and "Charcoal Ink" (Dark) modes.
- **UX:** Deterministic avatar gradients based on user initials, real-time password strength bar.
