-- ============================================
-- Tu Yango Repostero - Base de Datos
-- ============================================

CREATE DATABASE IF NOT EXISTS tu_yango_reposteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tu_yango_reposteria;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) DEFAULT NULL,
    orden INT DEFAULT 0,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    imagen VARCHAR(255) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    unidad_medida VARCHAR(50) DEFAULT 'pieza',
    disponible TINYINT(1) DEFAULT 1,
    destacado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de admin users
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    rol ENUM('admin', 'editor') DEFAULT 'editor',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de historial de inventario
CREATE TABLE IF NOT EXISTS historial_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    cantidad_anterior INT NOT NULL,
    cantidad_nueva INT NOT NULL,
    motivo VARCHAR(255),
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Datos iniciales
-- ============================================

-- Categorías por defecto
INSERT INTO categorias (nombre, descripcion, orden) VALUES
('Tortas Grandes', 'Tortas completas para celebraciones grandes, disponibles en 15 a 30 porciones', 1),
('Tortas Medianas', 'Tortas medianas ideales para reuniones familiares, de 8 a 12 porciones', 2),
('Tortas Pequeñas', 'Tortas individuales o para 2-4 personas', 3),
('Porciones', 'Porciones individuales de nuestras tortas y postres', 4),
('Dulces y Postres', 'Galletas, brownies, cupcakes, flan, gelatinas y más', 5);

-- Usuario admin por defecto (contraseña: admin123)
INSERT INTO usuarios (username, password, nombre_completo, rol) VALUES
('admin', '$2y$10$wNCIhKn2n6N2Inmrpo5RWutgfVyFs11thLNnlqAYgfQ50ygtdZBO6', 'Administrador', 'admin');

-- Productos de ejemplo
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, unidad_medida, destacado) VALUES
(1, 'Torta de Chocolate Premium', 'Deliciosa torta de chocolate belga con ganache y decoración artesanal. 20 porciones.', 850.00, 5, 'pieza', 1),
(1, 'Torta de Vainilla Especial', 'Torta clásica de vainilla con betún de mantequilla y flores de azúcar. 20 porciones.', 780.00, 3, 'pieza', 1),
(1, 'Torta Tres Leches', 'Nuestra famosa torta tres leches con canela y merengue italiano. 20 porciones.', 900.00, 4, 'pieza', 1),
(2, 'Torta Red Velvet', 'Torta red velvet con frosting de queso crema. 10 porciones.', 550.00, 6, 'pieza', 1),
(2, 'Torta de Zanahoria', 'Torta de zanahoria con nuez y frosting de crema de queso. 10 porciones.', 500.00, 4, 'pieza', 0),
(3, 'Mini Pastel de Fresa', 'Pastel individual de fresa con crema chantilly.', 85.00, 15, 'pieza', 1),
(3, 'Mini Torta de Limón', 'Torcito de limón con merengue flambeado.', 80.00, 12, 'pieza', 0),
(4, 'Porción de Chocolate', 'Porción generosa de torta de chocolate con ganache.', 55.00, 30, 'porción', 1),
(4, 'Porción de Tres Leches', 'Porción de nuestra famosa torta tres leches.', 60.00, 25, 'porción', 1),
(4, 'Porción de Red Velvet', 'Porción de red velvet con frosting de queso crema.', 55.00, 20, 'porción', 0),
(5, 'Cupcakes de Vainilla (6 pzas)', 'Cupcakes decorados de vainilla con buttercream.', 150.00, 20, 'pieza', 1),
(5, 'Brownies de Chocolate (8 pzas)', 'Brownies intensos de chocolate con nuez.', 180.00, 18, 'pieza', 0),
(5, 'Flan Napolitano', 'Flan casero con caramelo y vainilla.', 120.00, 10, 'pieza', 1),
(5, 'Gelatina de Mosaico', 'Gelatina decorada con leche condensada.', 95.00, 14, 'pieza', 0),
(5, 'Galletas Decoradas (12 pzas)', 'Galletas de mantequilla decoradas con royal icing.', 200.00, 10, 'pieza', 1);
