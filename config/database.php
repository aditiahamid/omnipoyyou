<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'omnichannel_app');

// Create connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function untuk eksekusi query
function query($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

// Function untuk fetch single row
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Function untuk fetch multiple rows
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

// Function untuk insert dan return last ID
function insert($table, $data) {
    global $conn;
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = ':' . implode(', :', $keys);
    
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    $stmt = query($sql, $data);
    
    return $stmt ? $conn->lastInsertId() : false;
}

// Function untuk update
function update($table, $data, $where, $whereParams = []) {
    $set = [];
    foreach($data as $key => $value) {
        $set[] = "$key = :$key";
    }
    $setString = implode(', ', $set);
    
    $sql = "UPDATE $table SET $setString WHERE $where";
    return query($sql, array_merge($data, $whereParams));
}

// Function untuk delete
function delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    return query($sql, $params);
}
?>