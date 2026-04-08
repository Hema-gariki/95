// // console.log("Hello World");
// const http = require('http');
// const PORT = 3000;

// const server = http.createServer((req, res) => {
//     res.write("Hello World");
//         res.end();
// })

// server.listen(PORT,() =>{
//     console.log(`Server running of ${PORT}`);
// });
const dotenv = require('dotenv').config();
const express = require('express');
const ProductRoutes = require('./routes/product.route.js');
const connectDB = require('./config/db.js');

const PORT =  3000 || process.env.PORT;
const app = express();


// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: false }));

// Connect to MongoDB
connectDB();

// Routes
app.use('/api/products', ProductRoutes);


// Server start
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});



