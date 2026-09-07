# 🎓 PractiHub API REST
 
A Laravel REST API for managing student internships at companies. Companies can post internship offers and students can apply to them, with token-based authentication (Laravel Passport) and role-based access control.
 
Built as a conversion of an MVC application (Sprint 4) into a REST API architecture.
 
## 🛠️ Tecnologies
 
- **PHP** ^8.3
- **Laravel** ^13.8
- **Laravel Passport** ^13.0 — OAuth2 token authentication
- **Pest** ^5.0 — testing
- **SQLite** — database (configurable)
- **Scribe** — automatic API documentation generation
## ✨ Features
 
### Student
- Register, log in and log out
- View, edit and delete their own profile
- Browse internship offers (filterable by category and location)
- Apply and withdraw applications
- Check the status of their applications
### Company
- Register, log in and log out
- View, edit and delete their own profile
- Create, edit and delete their own internship offers
- View applicants for their offers and check their CVs
- Accept or reject applications
- Check the company ranking by acceptance rate
## Main resources
 
| Resource | Description |
|---|---|
| `users` | Students and companies (differentiated by the `role` field) |
| `offers` | Internship offers posted by companies |
| `applications` | Student applications to offers |
| `categories` | Categories used to classify and filter offers |
 
## Key business rules
 
- **Application lifecycle:** `pending` → `read` (once the company opens the CV) → `accepted` / `rejected`. An application can only be accepted or rejected once it's in the `read` state.
- **Withdrawing an application:** a student can only withdraw an application while it's still `pending` and within the first 30 minutes of creation.
- **Company ranking:** the acceptance rate (`accepted / (accepted + rejected) * 100`) is calculated using only resolved applications, across all of the company's offers (active and inactive). Companies with no resolved applications are excluded from the ranking.
- **Offer visibility:** students only see active offers; companies see all of their own offers, active and inactive.
## Installation
 
### Requirements
- PHP 8.3+
- Composer
- [Laravel Herd](https://herd.laravel.com/) (or an equivalent local environment)
### Steps
 
1. Clone the repository and install dependencies:
```bash
   git clone <PractiHub-API-REST>
   cd PractiHub-API-REST
   composer install
```
 
2. Copy the environment file and generate the application key:
```bash
   cp .env.example .env
   php artisan key:generate
```
 
3. Set up the database in `.env` (SQLite by default):
```bash
   touch database/database.sqlite
```
 
4. Run migrations and seed the database with sample data:
```bash
   php artisan migrate --seed
```
 
5. Install Passport's personal access client (required to be able to issue tokens):
```bash
   php artisan passport:client --personal
```
 
6. Start the server (use Laravel's built-in server if you're not using Herd):
```bash
   php artisan serve
```
 
## Test users (seeder)
 
After running `php artisan migrate --seed`, the following users are created automatically, along with random companies, students, offers and applications:
 
| Role | Email | Password |
|---|---|---|
| Company | `empresa@test.com` | `password123` |
| Student | `estudiante@test.com` | `password123` |
 
## Testing
 
The project includes functional tests (Pest) covering authentication, role-based authorization, validation, and the main business rules (applications, ranking, etc.):
 
```bash
php artisan test
```
 
## API Documentation
 
Full documentation for every endpoint (parameters, request/response examples, and an interactive "Try it out" explorer) is generated with [Scribe](https://scribe.knuckles.wtf/laravel/):
 
```bash
php artisan scribe:generate
```
 
Once generated, it's available at:
 
- **Interactive documentation:** `/docs`
- **Postman collection:** `storage/app/private/scribe/collection.json`
- **OpenAPI specification:** `storage/app/private/scribe/openapi.yaml`
## Main endpoints
 
| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Register as a student or company |
| `POST` | `/api/login` | Log in |
| `POST` | `/api/logout` | Log out |
| `GET/PUT/DELETE` | `/api/users/{user}` | Student profile |
| `GET/PUT/DELETE` | `/api/companies/{company}` | Company profile |
| `GET` | `/api/companies/ranking` | Company ranking by acceptance rate |
| `GET` | `/api/categories` | List of categories |
| `GET/POST/PUT/DELETE` | `/api/offers` / `/api/offers/{offer}` | Internship offers |
| `POST` | `/api/offers/{offer}/applications` | Apply to an offer |
| `GET` | `/api/offers/{offer}/applications` | View applicants for an offer (company) |
| `GET/PUT/DELETE` | `/api/applications/{application}` | Application detail / management |
| `GET` | `/api/applications` | List own applications |
| `GET` | `/api/applications/{application}/cv` | View an application's CV |
 
> For the full list with parameters and examples, see the generated documentation at `/docs`.
 
## Authentication
 
All endpoints (except `register` and `login`) require authentication via a Passport Bearer token:
 
```
Authorization: Bearer {your_token}
```
 
The token is returned in the response of `/api/register` or `/api/login`.
