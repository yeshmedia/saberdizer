<?php
declare(strict_types=1);

function database(): PDO
{
    $host = getenv('DB_HOST');
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    $port = getenv('DB_PORT') ?: '3306';

    if (!$host || !$name || !$user || $password === false || !ctype_digit($port)) {
        throw new RuntimeException('Database configuration is incomplete.');
    }

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ]
    );
}
