<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');   // Endereço do servidor
define('DB_PORT', '3306');        // Porta do MySQL (substitua se necessário)
define('DB_NAME', 'sistema_cadastro_qrcode'); // Nome do banco de dados
define('DB_USER', 'root');        // Usuário do banco de dados
define('DB_PASS', '');            // Senha do banco de dados (vazia para root no XAMPP)


// Função para obter a conexão com o banco de dados
function getConnection() {
    try {
        // Criação da conexão PDO
        $conn = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        // Configuração do modo de erro do PDO
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Mensagem de erro em caso de falha na conexão
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

// Teste de conexão (opcional - remova em produção)
try {
    $conexao = getConnection();
    echo "";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
