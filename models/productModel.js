const mongoose  = require('mongoose');
const ProductSchema = mongoose.Schema(
    {
        name: {
            type: String,
            required: [true, "product name is mandatory"],
        },
        quantity: {
            type: Number,
            require: true,
            default: 0,
        },
        price: {
            type: Number,
            require: true,
            default: 0,
        },
        Image:{
            type: String,
            require:false,
        }
    },
    {
        timestamps: true
    }
);
const Product = mongoose.model("Product", ProductSchema);
module.exports = Product;