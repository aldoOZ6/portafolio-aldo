
-- ACTUALIZACIÓN: Eliminar productos anteriores e insertar nuevos productos según imágenes en uploads/products

-- Eliminar productos anteriores
DELETE FROM products;

-- Insertar nuevos productos con imágenes
INSERT INTO products (category_id, name, description, price, stock, image) VALUES
(1, 'Arena sanitaria para gatos', 'Arena aglomerante de alta absorción.', 149.00, 50, 'uploads/products/arena_gato.jpg'),
(2, 'Arnés para perros', 'Arnés ajustable y cómodo para paseos.', 259.00, 35, 'uploads/products/arnrnes_perro.jpg'),
(2, 'Bolsa de adiestramiento para perros', 'Accesorio para paseos y entrenamiento.', 99.00, 90, 'uploads/products/bolsa_perro.jpg'),
(1, 'Cama ortopédica para gatos', 'Cama cómoda y lavable.', 699.00, 15, 'uploads/products/cama_gato.jpg'),
(2, 'Cama para perros', 'Cama acolchada con funda lavable.', 799.00, 12, 'uploads/products/cama_perro.jpg'),
(1, 'Catnip natural', 'Hierba para estimular a tu gato.', 59.00, 120, 'uploads/products/catnip.jpg'),
(2, 'Cepillo de goma para perros', 'Elimina pelo suelto y masajea.', 129.00, 80, 'uploads/products/cdepillo_perro.jpg'),
(1, 'Collar reflectante para gatos', 'Ajustable y seguro.', 89.00, 70, 'uploads/products/collar_gato.jpg'),
(1, 'Comida premium para gatos', 'Alimento balanceado para gatos adultos.', 399.00, 25, 'uploads/products/COMIDA_GATO.jpg'),
(2, 'Comida premium para perros', 'Alimento para perros adultos de raza mediana.', 699.00, 20, 'uploads/products/comida_perro.jpg'),
(2, 'Correa retráctil para perros', 'Correa hasta 5m, resistente.', 249.00, 40, 'uploads/products/correa_perro.jpg'),
(2, 'Juguete resistente para perros', 'Ideal para perros fuertes.', 199.00, 50, 'uploads/products/juguete_perro.jpg'),
(1, 'Juguete láser para gatos', 'Entretenimiento para tu gato.', 129.00, 40, 'uploads/products/juguetelase_gato.jpg'),
(1, 'Plato anti-voracidad para gatos', 'Reduce ingesta rápida.', 199.00, 30, 'uploads/products/pato_gato.jpg'),
(2, 'Placa de identificación para perros', 'Incluye grabado personalizable.', 149.00, 100, 'uploads/products/placa_perro.jpg'),
(2, 'Plato antideslizante para perros', 'Acero inoxidable.', 179.00, 60, 'uploads/products/plato_perro.jpg'),
(2, 'Tratamiento antipulgas para perros', 'Efecto por 30 días.', 299.00, 25, 'uploads/products/pulgas_perro.jpg'),
(1, 'Rascador para gatos', 'Rascador con plataforma y juguete.', 899.00, 10, 'uploads/products/rascador_gato.jpg'),
(1, 'Shampoo para gatos', 'Suave para pieles sensibles.', 99.00, 60, 'uploads/products/shampoo_gato.jpg'),
(1, 'Transportadora para gatos', 'Transporta a tu gato con seguridad.', 459.00, 8, 'uploads/products/trasportadora_gato.jpg');
CREATE DATABASE IF NOT EXISTS ecommerce DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rfc VARCHAR(20) DEFAULT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) NOT NULL,
  stock INT DEFAULT 0,
  image VARCHAR(255),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('pending','paid','shipped','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  quantity INT,
  unit_price DECIMAL(12,2),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  uuid VARCHAR(80),
  xml_path VARCHAR(255),
  pdf_path VARCHAR(255),
  timbre_status ENUM('not_sent','sent','timbrado','rejected') DEFAULT 'not_sent',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Actualiza el usuario y contraseña del administrador
DELETE FROM users WHERE id = 1;
INSERT INTO users (id, name, email, password, rfc, is_admin) VALUES
  (1, 'Administrador', 'admin@mitienda.local', '$2y$10$QeQwQwQwQwQwQwQwQwQwQeQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'AAA010101AAA', 1);

INSERT IGNORE INTO categories (id, name) VALUES (1, 'Gatos'), (2, 'Perros');

INSERT INTO products (category_id, name, description, price, stock) VALUES
(1, 'Comida premium para gatos 2kg', 'Alimento balanceado para gatos adultos.', 399.00, 25),
(1, 'Arena sanitaria para gatos 10L', 'Arena aglomerante de alta absorción.', 149.00, 50),
(1, 'Rascador para gatos grande', 'Rascador con plataforma y juguete.', 899.00, 10),
(1, 'Catnip natural 50g', 'Hierba para estimular a tu gato.', 59.00, 120),
(1, 'Cama ortopédica para gatos', 'Cama cómoda y lavable.', 699.00, 15),
(1, 'Juguete interactivo con láser', 'Entretenimiento para tu gato.', 129.00, 40),
(1, 'Plato anti-voracidad para gatos', 'Reduce ingesta rápida.', 199.00, 30),
(1, 'Shampoo para gatos 250ml', 'Suave para pieles sensibles.', 99.00, 60),
(1, 'Transportadora para gatos mediana', 'Transporta a tu gato con seguridad.', 459.00, 8),
(1, 'Collar reflectante para gatos', 'Ajustable y seguro.', 89.00, 70),
(2, 'Comida premium para perros 5kg', 'Alimento para perros adultos de raza mediana.', 699.00, 20),
(2, 'Correa retráctil para perros', 'Correa hasta 5m, resistente.', 249.00, 40),
(2, 'Cama para perros tamaño mediano', 'Cama acolchada con funda lavable.', 799.00, 12),
(2, 'Juguete resistente para mordida', 'Ideal para perros fuertes.', 199.00, 50),
(2, 'Plato antideslizante para perros', 'Acero inoxidable.', 179.00, 60),
(2, 'Arnés para paseo ajustable', 'Cómodo y seguro.', 259.00, 35),
(2, 'Cepillo de goma para pelo', 'Elimina pelo suelto y masajea.', 129.00, 80),
(2, 'Bolsa de adiestramiento', 'Accesorio para paseos y entrenamiento.', 99.00, 90),
(2, 'Tratamiento antipulgas para perros', 'Efecto por 30 días.', 299.00, 25),
(2, 'Placa de identificación personalizable', 'Incluye grabado.', 149.00, 100);
INSERT INTO products (category_id, name, description, price, stock, image) VALUES
(1, 'Comida premium para gatos 1kg', 'Alimento balanceado para gatos adultos. Ingredientes naturales y alto en proteína.', 159.00, 25, 'frontend/public/uploads/products/comida_gatos_1kg.jpg');
