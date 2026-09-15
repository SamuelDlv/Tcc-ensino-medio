<?php
/**
 * Conexão do BetterOrganized com MySQL/MariaDB.
 *
 * As credenciais são lidas do ambiente para que nenhum segredo seja versionado.
 * Exemplos: BETTERORGANIZED_DB_HOST, BETTERORGANIZED_DB_USER,
 * BETTERORGANIZED_DB_PASS e BETTERORGANIZED_DB_NAME.
 */
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'betterorganized';

function databaseConfig(string $name, string $fallback): string
{
    $value = getenv($name);
    return $value === false ? $fallback : $value;
}

$host = databaseConfig('BETTERORGANIZED_DB_HOST', DB_HOST);
$user = databaseConfig('BETTERORGANIZED_DB_USER', DB_USER);
$pass = databaseConfig('BETTERORGANIZED_DB_PASS', DB_PASS);
$name = databaseConfig('BETTERORGANIZED_DB_NAME', DB_NAME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $con = mysqli_connect($host, $user, $pass, $name);
    mysqli_set_charset($con, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die(
        'Não foi possível conectar ao banco de dados. ' .
        'Configure as variáveis BETTERORGANIZED_DB_HOST, BETTERORGANIZED_DB_USER, ' .
        'BETTERORGANIZED_DB_PASS e BETTERORGANIZED_DB_NAME no ambiente.'
    );
}
