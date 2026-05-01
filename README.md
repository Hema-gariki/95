# 💰 MERN Expense Tracker

A full-stack expense tracking web application built with the **MERN stack** (MongoDB, Express.js, React.js, Node.js). Track your daily income and expenses with real-time summaries, charts, filters, and full CRUD functionality.

---

## 📸 Features

| Feature | Description |
|---|---|
| ➕ Add Transaction | Add income or expense with title, amount, category, date, and note |
| ✏️ Edit Transaction | Update any existing transaction inline |
| 🗑️ Delete Transaction | Remove transactions with a confirmation prompt |
| 🔍 Search | Search transactions by title in real time |
| 🔽 Filter | Filter by type (income/expense) and category |
| 📊 Dashboard | Cards showing total income, expense, balance, and transaction count |
| 📈 Chart | Bar chart comparing total income vs total expense (Recharts) |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | React.js (Vite), Recharts, Axios |
| Backend | Node.js, Express.js |
| Database | MongoDB (Mongoose ODM) |
| Dev Tools | Nodemon, dotenv |

---

## 📁 Folder Structure

```
mern-expense-tracker/
├── backend/
│   ├── config/
│   │   └── db.js                  # MongoDB connection
│   ├── controllers/
│   │   └── transaction.controller.js  # Route handlers
│   ├── models/
│   │   └── transaction.model.js   # Mongoose schema
│   ├── routes/
│   │   └── transaction.route.js   # API routes
│   ├── .env.example               # Environment variable template
│   ├── index.js                   # Express app entry point
│   └── package.json
└── frontend/
    ├── src/
    │   ├── services/
    │   │   └── api.js             # Axios API calls
    │   ├── App.jsx                # Main React component
    │   ├── main.jsx               # React DOM entry point
    │   └── style.css              # All styles
    ├── index.html
    ├── vite.config.js
    └── package.json
```

---

## 🚀 Getting Started

### Prerequisites

- [Node.js](https://nodejs.org/) v18+
- [MongoDB](https://www.mongodb.com/) running locally **or** a free [MongoDB Atlas](https://www.mongodb.com/atlas) cluster

---

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/mern-expense-tracker.git
cd mern-expense-tracker
```

---

### 2. Set Up the Backend

```powershell
cd backend
npm install
copy .env.example .env
```

Edit the `.env` file:

```env
PORT=3000
MONGODB_URI=mongodb://127.0.0.1:27017/expense_tracker
```

> For **MongoDB Atlas**, replace `MONGODB_URI` with your Atlas connection string.

Start the backend server:

```powershell
npm run dev
```

Backend runs at → `http://localhost:3000`

---

### 3. Set Up the Frontend

Open a **new terminal**:

```powershell
cd frontend
npm install
npm run dev
```

Frontend runs at → `http://localhost:5173`

---

## 🔌 API Endpoints

All endpoints are prefixed with `/api/transactions`.

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/transactions` | Create a new transaction |
| `GET` | `/api/transactions` | Get all transactions (supports filters) |
| `GET` | `/api/transactions/summary` | Get income/expense totals and balance |
| `GET` | `/api/transactions/:id` | Get a single transaction by ID |
| `PUT` | `/api/transactions/:id` | Update a transaction by ID |
| `DELETE` | `/api/transactions/:id` | Delete a transaction by ID |

### Query Parameters for GET `/api/transactions`

| Param | Values | Description |
|---|---|---|
| `type` | `income`, `expense` | Filter by transaction type |
| `category` | e.g. `food`, `salary` | Filter by category |
| `search` | any string | Search by title (case-insensitive) |

---

## 📦 Example Transaction JSON

```json
{
  "title": "Grocery",
  "amount": 45.00,
  "type": "expense",
  "category": "food",
  "date": "2026-04-28",
  "note": "Weekly groceries"
}
```

### Supported Categories

**Expense:** `food`, `transport`, `housing`, `entertainment`, `healthcare`, `education`, `shopping`, `other`

**Income:** `salary`, `freelance`, `investment`, `other`

---

## 🧪 Testing the API (with curl)

```bash
# Add a transaction
curl -X POST http://localhost:3000/api/transactions \
  -H "Content-Type: application/json" \
  -d '{"title":"Salary","amount":3000,"type":"income","category":"salary","date":"2026-04-01"}'

# Get all transactions
curl http://localhost:3000/api/transactions

# Get summary
curl http://localhost:3000/api/transactions/summary

# Filter by type
curl "http://localhost:3000/api/transactions?type=expense"

# Search by title
curl "http://localhost:3000/api/transactions?search=grocery"
```

---

## 🏗️ How It Works

1. **MongoDB** stores all transactions as documents using a Mongoose schema with validation.
2. **Express.js** exposes RESTful API endpoints with full CRUD and an aggregation-based `/summary` endpoint.
3. **React** (via Vite) fetches data from the API using **Axios**, manages state locally, and renders the dashboard with **Recharts**.
4. Vite's dev proxy forwards `/api` calls to `localhost:3000`, so no CORS issues during development.

---

## 🤝 Contributing

Pull requests are welcome. For major changes, open an issue first to discuss what you would like to change.

---

## 📄 License

MIT
