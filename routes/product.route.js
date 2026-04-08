const express = require('express');
const router = express.Router();

const {createProduct,getProducts,getProductById,updateProduct,deleteProduct,searchProducts
} = require('../controllers/product.controller');

router.post('/', createProduct);
router.get('/search', searchProducts);
router.get('/', getProducts);
router.get('/:id', getProductById);
router.put('/:id', updateProduct);
router.delete('/:id', deleteProduct);

module.exports = router;