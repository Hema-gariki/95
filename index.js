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
require('dotenv').config();
const express = require('express');
const Product = require("./models/productModel.js");
const mongoose = require('mongoose');
const connectDB = require('./config/db');
const product = require('./routes/productroutes.js');


const app = express();
const PORT = 5000 || process.env.PORT;
app.use(express.json())

//Middleware
app.use(express.urlencoded({ extended: false}));

//connect to MongoDv
connectDB();

//routes
app.use('/api/products', productRoutes);

//start the server
app.listen(PORT, () => {
    console.log(`server is running on https://localhost:${PORT}`);
    
});



