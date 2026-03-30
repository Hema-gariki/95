// console.log("Hello World");
const http = require('http');
const PORT = 3000;

const server = http.createServer((req, res) => {
    res.write("Hello World");
        res.end();
})

server.listen(PORT,() =>{
    console.log(`Server running of ${PORT}`);
});