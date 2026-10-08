// Permitir llamadas HTTPS locales sin certificado confiable
process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = 0;

const express = require('express');
const axios = require('axios');
const path = require('path');

const app = express();
const PORT = 3000;

// Configuración de middlewares
app.use(express.static('public'));
app.use(express.urlencoded({ extended: true }));
app.set('view engine', 'html');
app.set('views', path.join(__dirname, 'views'));

// Ruta principal
app.get('/', (req, res) => {
  res.render('index', { cliente: null, productos: [], error: null });
});

// Ruta para búsqueda de clientes
app.post('/buscar', async (req, res) => {
  const nombre = req.body.nombre;

  try {
    const clientesRes = await axios.get(`https://localhost:7168/api/clientes`);
    const cliente = clientesRes.data.find(c => c.nombre.toLowerCase() === nombre.toLowerCase());

    if (!cliente) throw new Error('Cliente no encontrado');

    const comprasRes = await axios.get(`https://localhost:7085/api/compras?idCliente=${cliente.idCliente}`);
    const compras = comprasRes.data;

    const productos = [];
    for (const compra of compras) {
      const productoRes = await axios.get(`https://localhost:7041/api/productos/${compra.idProducto}`);
      productos.push(productoRes.data.nombre);
    }

    res.render('index', { cliente, productos, error: null });
  } catch (error) {
    res.render('index', { cliente: null, productos: [], error: error.message });
  }
});

// Iniciar servidor
app.listen(PORT, () => {
  console.log(`✅ Frontend corriendo en http://localhost:${PORT}`);
});
