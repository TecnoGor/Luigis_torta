<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'luigis_torta');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', "Luigi's Tortas");
define('SITE_URL', 'http://localhost/Catalogo_reposteria');
define('ADMIN_URL', SITE_URL . '/admin');

define('UPLOAD_DIR', __DIR__ . '/../assets/images/products/');
define('UPLOAD_URL', SITE_URL . '/assets/images/products/');

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
