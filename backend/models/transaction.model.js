const mongoose = require("mongoose");

const transactionSchema = new mongoose.Schema(
  {
    title: {
      type: String,
      required: [true, "Title is required"],
      trim: true,
    },
    amount: {
      type: Number,
      required: [true, "Amount is required"],
      min: [0.01, "Amount must be positive"],
    },
    type: {
      type: String,
      enum: ["income", "expense"],
      required: [true, "Type must be income or expense"],
    },
    category: {
      type: String,
      enum: [
        "food",
        "transport",
        "housing",
        "entertainment",
        "healthcare",
        "education",
        "shopping",
        "salary",
        "freelance",
        "investment",
        "other",
      ],
      required: [true, "Category is required"],
    },
    date: {
      type: Date,
      required: [true, "Date is required"],
    },
    note: {
      type: String,
      trim: true,
      default: "",
    },
  },
  { timestamps: true }
);

module.exports = mongoose.model("Transaction", transactionSchema);
