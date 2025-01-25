<?php
include_once(__DIR__ . '/../config/conexao.php');

// Receber os dados enviados via POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nome'], $data['cpf'], $data['curso'])) {
    $nome = $data['nome'];
    $cpf = $data['cpf'];
    $curso = $data['curso'];
    $endereco = $data['endereco'];
    $matricula = $data['matricula'];
    $estabelecimento = $data['estabelecimento'];

    // Salvar no banco de dados
    $query = "INSERT INTO presencas (nome, data, hora, curso) VALUES (?, CURDATE(), NOW(), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $nome, $curso);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
}
?>
