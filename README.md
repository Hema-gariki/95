# SurveyFlow (Slim 4) 📋

Anonymous CSV-powered quiz platform built with **Slim 4** PHP framework.

Upload a CSV → get a shareable quiz URL → collect anonymous responses → download results.

---

## Tech Stack

| | |
|---|---|
| Framework | Slim 4 |
| Language | PHP 8.1+ |
| Templates | Twig 3 |
| Database | SQLite (default) or MySQL |
| ORM | Eloquent (Illuminate Database) |
| Auth | PHP native sessions |
| CSS | Tailwind CSS (CDN) |

---

## CSV Format

```
Question, CorrectAnswer, WrongOption1, WrongOption2, ...
```

Example:
```
What is the capital of France?,Paris,London,Berlin,Madrid
What is 2+2?,4,3,5,6
```

- Column 1 → question text
- Column 2 → correct answer
- Column 3+ → wrong options (one or more)
- Optional header row is auto-skipped
- Options are shuffled for each participant

---

## Installation — Step by Step

### Step 1 — Check requirements

You need:
- PHP 8.1+ → `php --version`
- Composer  → `composer --version`
- SQLite extension (usually built in) — or MySQL

### Step 2 — Unzip and enter the folder

```bash
unzip surveyflow-slim.zip
cd surveyflow-slim
```

### Step 3 — Install dependencies

```bash
composer install
```

This downloads Slim, Twig, Eloquent, and all other libraries into `/vendor`.

### Step 4 — Create your .env file

```bash
cp .env.example .env
```

Open `.env` in any text editor. The default uses SQLite — no changes needed for local dev.

For MySQL, change these lines:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=surveyflow
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 5 — Create the database tables and admin user

```bash
php database/migrate.php --fresh --seed
```

This creates all 4 tables and the default admin account.

### Step 6 — Start the server

```bash
php -S localhost:8000 -t public
```

### Step 7 — Open in browser

Go to: **http://localhost:8000**

You will be redirected to the login page.

---

## Default Admin Login

```
Email:    admin@surveyflow.test
Password: password
```

> Change these after first login by editing `.env` and re-running the seed.

---

## How to Use

### As Admin

1. Log in at `/admin/login`
2. Click **Upload CSV** → enter a topic name → upload your `.csv` file
3. The survey is created with a unique URL like `/survey/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
4. Share that URL with participants
5. Toggle any survey on/off from the dashboard
6. Click **Results** to view all submissions
7. Click **Download CSV** to export results

### As Participant

1. Open the survey URL in any browser
2. Answer all questions (multiple choice)
3. Click **Submit**
4. See your score — no login, no personal data collected

---

## Project Structure

```
surveyflow-slim/
├── public/
│   ├── index.php          ← Entry point (all requests go here)
│   └── .htaccess          ← URL rewriting for Apache
├── src/
│   ├── bootstrap/
│   │   ├── database.php   ← Eloquent ORM connection
│   │   └── container.php  ← Twig dependency injection
│   ├── routes.php         ← All URL routes
│   ├── Controllers/
│   │   ├── AuthController.php    ← Login / logout
│   │   ├── AdminController.php   ← Dashboard, upload, results
│   │   └── SurveyController.php  ← Public quiz + submission
│   ├── Models/
│   │   ├── Survey.php
│   │   ├── Question.php
│   │   ├── SurveyResponse.php
│   │   └── User.php
│   └── Middleware/
│       └── AuthMiddleware.php    ← Protects admin routes
├── templates/
│   ├── layouts/base.twig         ← Shared admin layout
│   ├── auth/login.twig
│   ├── admin/
│   │   ├── dashboard.twig
│   │   ├── upload.twig
│   │   └── results.twig
│   ├── survey/
│   │   ├── show.twig             ← Public quiz page
│   │   └── thankyou.twig         ← Score after submission
│   └── error.twig
├── database/
│   └── migrate.php               ← Run this to set up tables
├── storage/uploads/               ← Uploaded CSV files stored here
├── sample_survey.csv              ← Ready-to-use test CSV
├── composer.json
├── .env.example
└── README.md
```

---

## URL Map

| URL | Who | What |
|---|---|---|
| `/` | Anyone | Redirects to login |
| `/admin/login` | Admin | Login form |
| `/admin/dashboard` | Admin | List all surveys |
| `/admin/upload` | Admin | Upload CSV |
| `/admin/toggle/{id}` | Admin | Turn survey on/off |
| `/admin/delete/{id}` | Admin | Delete survey |
| `/admin/results/{id}` | Admin | View responses |
| `/admin/results/{id}/download` | Admin | Download CSV |
| `/survey/{token}` | Participant | Take the quiz |
| `/survey/{token}/thankyou` | Participant | See score |

---

## Security

- Admin routes protected by session auth + `AuthMiddleware`
- Survey tokens are UUID v4 (unguessable)
- Participant submissions fully anonymous — only a random UUID stored per session
- No IP addresses, names, or personal data ever collected
- Passwords hashed with `password_hash()` using bcrypt

---

## License

MIT
