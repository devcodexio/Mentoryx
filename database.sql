DROP DATABASE IF EXISTS simulador_examenes;
CREATE DATABASE simulador_examenes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simulador_examenes;

-- Tabla de administradores
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    puntaje_maximo INT NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de preguntas
CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    imagen_resolucion VARCHAR(255) DEFAULT NULL,
    resolucion TEXT,
    puntaje DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de alternativas
CREATE TABLE IF NOT EXISTS alternativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    alternativa TEXT NOT NULL,
    es_correcta TINYINT(1) DEFAULT 0,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de resultados
CREATE TABLE IF NOT EXISTS resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    usuario_nombre VARCHAR(100) NOT NULL,
    puntaje INT NOT NULL,
    correctas INT NOT NULL,
    incorrectas INT NOT NULL,
    total_preguntas INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de PDFs generados
CREATE TABLE IF NOT EXISTS pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    archivo_pdf VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar administrador por defecto (usuario: admin, contraseña: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$oiFh1BDg3.7FW7MA4KwSaurSc4vxZNTDFE00Y7GFGv7h1QDvZ6oDa')
ON DUPLICATE KEY UPDATE username=username;

-- Insertar Categorías de ejemplo
INSERT INTO categorias (id, nombre, descripcion) VALUES
(1, 'Matemáticas', 'Evaluación de álgebra, aritmética, geometría y razonamiento matemático.'),
(2, 'Ciencia y Tecnología', 'Preguntas sobre biología, química, física y el método científico.'),
(3, 'Historia y Geografía', 'Examen sobre acontecimientos históricos mundiales y geografía general.')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar Preguntas de ejemplo (Matemáticas)
INSERT INTO preguntas (id, categoria_id, pregunta, imagen, resolucion) VALUES
(1, 1, '¿Cuál es el valor de x en la ecuación de primer grado: 3x + 5 = 20?', NULL, 'Para resolver la ecuación 3x + 5 = 20:\n1. Restamos 5 en ambos lados: 3x = 20 - 5 => 3x = 15.\n2. Dividimos entre 3: x = 15 / 3 => x = 5.\nPor lo tanto, el valor de x es 5.'),
(2, 1, 'Si un triángulo rectángulo tiene catetos de longitud 3 cm y 4 cm, ¿cuál es la longitud de su hipotenusa?', 'triangulo.png', 'Usando el Teorema de Pitágoras:\nh² = a² + b²\nDonde h es la hipotenusa y a, b son los catetos.\nh² = 3² + 4²\nh² = 9 + 16\nh² = 25\nh = √25 = 5 cm.\nPor lo tanto, la hipotenusa mide 5 cm.'),
(3, 1, '¿Cuál es el resultado de simplificar el siguiente producto notable: (x + 2)(x - 2)?', NULL, 'Este ejercicio corresponde al producto notable de "Diferencia de Cuadrados":\n(a + b)(a - b) = a² - b²\nAplicando la fórmula:\n(x + 2)(x - 2) = x² - 2² = x² - 4.\nPor lo tanto, el resultado es x² - 4.')
ON DUPLICATE KEY UPDATE pregunta=pregunta;

-- Insertar Alternativas (Matemáticas)
INSERT INTO alternativas (pregunta_id, alternativa, es_correcta) VALUES
-- Pregunta 1 (3x + 5 = 20)
(1, 'x = 3', 0),
(1, 'x = 5', 1),
(1, 'x = 6', 0),
(1, 'x = 4', 0),
-- Pregunta 2 (Triángulo rectángulo)
(2, '5 cm', 1),
(2, '6 cm', 0),
(2, '7 cm', 0),
(2, '8 cm', 0),
-- Pregunta 3 ((x+2)(x-2))
(3, 'x² + 4', 0),
(3, 'x² - 4', 1),
(3, 'x² - 4x + 4', 0),
(3, 'x² + 4x + 4', 0);

-- Insertar Preguntas de ejemplo (Ciencia y Tecnología)
INSERT INTO preguntas (id, categoria_id, pregunta, imagen, resolucion) VALUES
(4, 2, '¿Cuál es la unidad básica estructural y funcional de todos los seres vivos?', NULL, 'La célula es la unidad mínima de vida capaz de realizar de manera autónoma las funciones de nutrición, relación y reproducción. Todos los organismos vivos están formados por una o más células.'),
(5, 2, '¿Cuál es el elemento químico más abundante y ligero en el universo?', NULL, 'El hidrógeno es el elemento químico de número atómico 1. Es el más abundante en el universo, constituyendo aproximadamente el 75% de la materia bariónica por masa.'),
(6, 2, '¿Qué ley de la física postula que "a toda acción se opone una reacción igual y opuesta"?', NULL, 'La Tercera Ley de Newton (o principio de acción y reacción) establece que cuando un cuerpo ejerce una fuerza sobre otro, este último ejerce una fuerza de igual magnitud pero en sentido contrario sobre el primero.')
ON DUPLICATE KEY UPDATE pregunta=pregunta;

-- Insertar Alternativas (Ciencia y Tecnología)
INSERT INTO alternativas (pregunta_id, alternativa, es_correcta) VALUES
-- Pregunta 4 (Célula)
(4, 'El Átomo', 0),
(4, 'La Molécula', 0),
(4, 'La Célula', 1),
(4, 'El Órgano', 0),
-- Pregunta 5 (Hidrógeno)
(5, 'Oxígeno', 0),
(5, 'Hidrógeno', 1),
(5, 'Helio', 0),
(5, 'Carbono', 0),
-- Pregunta 6 (Tercera Ley)
(6, 'Primera Ley de Newton', 0),
(6, 'Segunda Ley de Newton', 0),
(6, 'Tercera Ley de Newton', 1),
(6, 'Ley de la Gravitación Universal', 0);


-- Tabla de configuracion global
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion (clave, valor) VALUES 
('nombre_sitio', 'AutoEvaluación'),
('correo', 'contacto@autoevaluacion.com'),
('celular', '51994269463'),
('whatsapp_msg', 'Hola, me interesa el banco de preguntas en PDF del examen:'),
('facebook', 'https://facebook.com'),
('tiktok', 'https://tiktok.com'),
('logo', '');
