<?php
function getConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }
    return $pdo;
}

function query($sql, $params = []) {
    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

function insertId($sql, $params = []) {
    query($sql, $params);
    return getConnection()->lastInsertId();
}

function execute($sql, $params = []) {
    return query($sql, $params)->rowCount();
}
