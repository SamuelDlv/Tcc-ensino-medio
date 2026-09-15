<?php
/**
 * Conexão centralizada com o banco.
 * Não coloque credenciais diretamente neste arquivo.
 */
session_start();

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASSWORD') ?: '';
$banco = getenv('DB_NAME') ?: 'better_organized';

$con = mysqli_init();
if (!$con || !$con->real_connect($host, $usuario, $senha, $banco, (int) $port)) {
    http_response_code(500);
    error_log('Falha ao conectar ao banco de dados.');
    exit('Não foi possível conectar ao banco de dados. Verifique a configuração local.');
}

$con->set_charset('utf8mb4');
