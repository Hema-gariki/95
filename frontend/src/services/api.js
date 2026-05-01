import axios from "axios";

const API = axios.create({ baseURL: "http://localhost:3000/api" });

export const getTransactions = (params) =>
  API.get("/transactions", { params });

export const getSummary = () => API.get("/transactions/summary");

export const createTransaction = (data) =>
  API.post("/transactions", data);

export const updateTransaction = (id, data) =>
  API.put(`/transactions/${id}`, data);

export const deleteTransaction = (id) =>
  API.delete(`/transactions/${id}`);
