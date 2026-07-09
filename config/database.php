<?php
/**
 * PDO database connection (singleton).
 * Usage:  $pdo = db();
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Align MySQL clock with PHP (config uses UTC) so NOW() comparisons
        // against published_at are consistent. Fixed offset needs no tz tables.
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (PDOException $e) {
        if (APP_ENV === 'development') {
            http_response_code(500);
            exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
        http_response_code(500);
        exit('Service temporarily unavailable. Please try again later.');
    }

    return $pdo;
}

/**
 * Small query helpers built on top of PDO prepared statements.
 */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetch_one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function fetch_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function fetch_col(string $sql, array $params = [])
{
    $val = q($sql, $params)->fetchColumn();
    return $val === false ? null : $val;
}

function last_id(): int
{
    return (int) db()->lastInsertId();
}
