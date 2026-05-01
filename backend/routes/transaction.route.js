const express = require("express");
const router = express.Router();
const {
  createTransaction,
  getAllTransactions,
  getSummary,
  getTransactionById,
  updateTransaction,
  deleteTransaction,
} = require("../controllers/transaction.controller");

// Summary must come before /:id so it's not treated as an id param
router.get("/summary", getSummary);

router.route("/").get(getAllTransactions).post(createTransaction);
router
  .route("/:id")
  .get(getTransactionById)
  .put(updateTransaction)
  .delete(deleteTransaction);

module.exports = router;
