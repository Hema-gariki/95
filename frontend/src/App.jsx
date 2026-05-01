import React, { useState, useEffect, useCallback } from "react";
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
} from "recharts";
import {
  getTransactions,
  getSummary,
  createTransaction,
  updateTransaction,
  deleteTransaction,
} from "./services/api";

const CATEGORIES = [
  "food", "transport", "housing", "entertainment",
  "healthcare", "education", "shopping",
  "salary", "freelance", "investment", "other",
];

const INCOME_CATEGORIES = ["salary", "freelance", "investment", "other"];
const EXPENSE_CATEGORIES = ["food", "transport", "housing", "entertainment", "healthcare", "education", "shopping", "other"];

const emptyForm = {
  title: "", amount: "", type: "expense",
  category: "food", date: new Date().toISOString().split("T")[0], note: "",
};

export default function App() {
  const [transactions, setTransactions] = useState([]);
  const [summary, setSummary] = useState({ income: 0, expense: 0, balance: 0, totalTransactions: 0 });
  const [form, setForm] = useState(emptyForm);
  const [editId, setEditId] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [filters, setFilters] = useState({ type: "all", category: "all", search: "" });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const [txRes, sumRes] = await Promise.all([
        getTransactions(filters.type !== "all" || filters.category !== "all" || filters.search
          ? { type: filters.type, category: filters.category, search: filters.search }
          : {}),
        getSummary(),
      ]);
      setTransactions(txRes.data.data);
      setSummary(sumRes.data.data);
    } catch {
      setError("Failed to load data. Is the backend running?");
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => { fetchAll(); }, [fetchAll]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    try {
      const payload = { ...form, amount: parseFloat(form.amount) };
      if (editId) {
        await updateTransaction(editId, payload);
      } else {
        await createTransaction(payload);
      }
      setForm(emptyForm);
      setEditId(null);
      setShowForm(false);
      fetchAll();
    } catch (err) {
      setError(err.response?.data?.message || "Something went wrong");
    }
  };

  const handleEdit = (tx) => {
    setForm({
      title: tx.title,
      amount: tx.amount,
      type: tx.type,
      category: tx.category,
      date: tx.date.split("T")[0],
      note: tx.note || "",
    });
    setEditId(tx._id);
    setShowForm(true);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleDelete = async (id) => {
    if (!window.confirm("Delete this transaction?")) return;
    await deleteTransaction(id);
    fetchAll();
  };

  const chartData = [
    { name: "Summary", Income: summary.income, Expense: summary.expense },
  ];

  const availableCategories = form.type === "income" ? INCOME_CATEGORIES : EXPENSE_CATEGORIES;

  return (
    <div className="app">
      <header className="header">
        <div className="header-content">
          <h1>💰 Expense Tracker</h1>
          <button className="btn btn-primary" onClick={() => { setShowForm(!showForm); setEditId(null); setForm(emptyForm); }}>
            {showForm ? "✕ Cancel" : "+ Add Transaction"}
          </button>
        </div>
      </header>

      <main className="container">
        {error && <div className="alert alert-error">{error}</div>}

        <div className="cards">
          <div className="card card-balance">
            <p className="card-label">Balance</p>
            <p className="card-value">${summary.balance.toFixed(2)}</p>
          </div>
          <div className="card card-income">
            <p className="card-label">Total Income</p>
            <p className="card-value">${summary.income.toFixed(2)}</p>
          </div>
          <div className="card card-expense">
            <p className="card-label">Total Expense</p>
            <p className="card-value">${summary.expense.toFixed(2)}</p>
          </div>
          <div className="card card-count">
            <p className="card-label">Transactions</p>
            <p className="card-value">{summary.totalTransactions}</p>
          </div>
        </div>

        <div className="chart-box">
          <h2>Income vs Expense</h2>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip formatter={(v) => `$${v.toFixed(2)}`} />
              <Legend />
              <Bar dataKey="Income" fill="#22c55e" radius={[6, 6, 0, 0]} />
              <Bar dataKey="Expense" fill="#ef4444" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {showForm && (
          <div className="form-box">
            <h2>{editId ? "Edit Transaction" : "Add Transaction"}</h2>
            <form onSubmit={handleSubmit} className="form">
              <div className="form-row">
                <div className="form-group">
                  <label>Title *</label>
                  <input type="text" required placeholder="e.g. Grocery"
                    value={form.title}
                    onChange={(e) => setForm({ ...form, title: e.target.value })}
                  />
                </div>
                <div className="form-group">
                  <label>Amount *</label>
                  <input type="number" required min="0.01" step="0.01" placeholder="0.00"
                    value={form.amount}
                    onChange={(e) => setForm({ ...form, amount: e.target.value })}
                  />
                </div>
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label>Type *</label>
                  <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value, category: e.target.value === "income" ? "salary" : "food" })}>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Category *</label>
                  <select value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })}>
                    {availableCategories.map((c) => (
                      <option key={c} value={c}>{c.charAt(0).toUpperCase() + c.slice(1)}</option>
                    ))}
                  </select>
                </div>
                <div className="form-group">
                  <label>Date *</label>
                  <input type="date" required value={form.date}
                    onChange={(e) => setForm({ ...form, date: e.target.value })}
                  />
                </div>
              </div>
              <div className="form-group">
                <label>Note</label>
                <input type="text" placeholder="Optional note"
                  value={form.note}
                  onChange={(e) => setForm({ ...form, note: e.target.value })}
                />
              </div>
              <button type="submit" className="btn btn-primary btn-full">
                {editId ? "Update Transaction" : "Add Transaction"}
              </button>
            </form>
          </div>
        )}

        <div className="filters">
          <input type="text" placeholder="🔍 Search by title..."
            value={filters.search}
            onChange={(e) => setFilters({ ...filters, search: e.target.value })}
          />
          <select value={filters.type} onChange={(e) => setFilters({ ...filters, type: e.target.value })}>
            <option value="all">All Types</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
          </select>
          <select value={filters.category} onChange={(e) => setFilters({ ...filters, category: e.target.value })}>
            <option value="all">All Categories</option>
            {CATEGORIES.map((c) => (
              <option key={c} value={c}>{c.charAt(0).toUpperCase() + c.slice(1)}</option>
            ))}
          </select>
          <button className="btn btn-outline" onClick={() => setFilters({ type: "all", category: "all", search: "" })}>
            Clear
          </button>
        </div>

        <div className="transactions">
          <h2>Transactions {loading && <span className="loading">Loading...</span>}</h2>
          {transactions.length === 0 && !loading ? (
            <p className="empty">No transactions found. Add one above!</p>
          ) : (
            <ul className="tx-list">
              {transactions.map((tx) => (
                <li key={tx._id} className={`tx-item ${tx.type}`}>
                  <div className="tx-left">
                    <span className="tx-category">{tx.category}</span>
                    <div>
                      <p className="tx-title">{tx.title}</p>
                      <p className="tx-meta">{new Date(tx.date).toLocaleDateString()} {tx.note && `• ${tx.note}`}</p>
                    </div>
                  </div>
                  <div className="tx-right">
                    <span className={`tx-amount ${tx.type}`}>
                      {tx.type === "income" ? "+" : "-"}${tx.amount.toFixed(2)}
                    </span>
                    <div className="tx-actions">
                      <button className="btn-icon" onClick={() => handleEdit(tx)} title="Edit">✏️</button>
                      <button className="btn-icon" onClick={() => handleDelete(tx._id)} title="Delete">🗑️</button>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </div>
      </main>
    </div>
  );
}