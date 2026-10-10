<?php
/**
 * Delta State Election Results Portal
 * Database Connection Handler (PDO MySQL)
 */

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'bincom_test');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 2,
            PDO::ATTR_EMULATE_PREPARES   => false,
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Verify key table exists in the database
        $pdo->query("SELECT 1 FROM polling_unit LIMIT 1");
        return $pdo;
    } catch (Throwable $e) {
        $sqliteBackup = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (file_exists($sqliteBackup)) {
            try {
                $pdo = new PDO("sqlite:" . $sqliteBackup, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                return $pdo;
            } catch (Throwable $err) {}
        }
        throw new Exception("Database Connection Error: " . $e->getMessage());
    }
}

function fetchAll(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchOne(string $sql, array $params = []): ?array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function execute(string $sql, array $params = []): bool {
    $stmt = getDB()->prepare($sql);
    return $stmt->execute($params);
}

function lastInsertId(): string {
    return getDB()->lastInsertId();
}
