const Product = require("../models/productModel");

const createProduct = async (req, res) => {
    try {
        const product = await Product.create(req.body);
        res.status(201).json(product);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};
const getProducts = async (req, res) => {
     try {
        const products = await Product.find({});
        res.status(200).json(products);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }  
};
const getProductById = async (req, res) => {
     try {
        const product=await Product.findById(req.params.id);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        res.status(200).json(product);
    } catch (error) {
        console.error("Error handling GET request:", error.message);
        res.status(500).json({error: 'Internal Server Error' });
    }  
};
const updateProduct = async (req, res) => {
    try {
        const product = await Product.findByIdAndUpdate(req.params.id, req.body);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        const updatedProduct = await Product.findById(req.params.id);
        res.status(200).json(updatedProduct);
    } catch (error) {
        console.error("Error handling PUT request:", error.message);
        res.status(500).json({ error: 'Internal Server Error' });
    }
};
const deleteProduct = async (req, res) => {
    try {
        const product = await Product.findByIdAndDelete(req.params.id);
        if (!product) {
            return res.status(404).json({ message: 'Product not found' });
        }
        res.status(200).json({ message: 'Product deleted successfully' });
    } catch (error) {
        console.error("Error handling DELETE request:", error);
        res.status(500).json({ error: 'Internal Server Error' });
    }  
};
const searchProducts = async (req, res) => {
    try {
        const { name } = req.query;
        if (!name) return res.status(400).json({ message: "Please provide a name to search" });

        const products = await Product.find({
            name: { $regex: name, $options: 'i' }
        });

        res.status(200).json(products);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};

module.exports = {
    createProduct,
    getProducts,
    getProductById,
    updateProduct,
    deleteProduct,
    searchProducts
};
