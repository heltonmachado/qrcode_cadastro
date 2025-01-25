<?php
// Inclui o arquivo de conexão com o banco de dados
include_once(__DIR__ . '/../config/conexao.php');
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /public/login.php'); // Redireciona para a página de login
    exit;
}

// Verifica se o logout foi solicitado
if (isset($_POST['logout'])) {
    session_start();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Logout - Sistema de Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
body {
    background-image: url('images/logout.png');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    min-height: 100vh;
    color: black;
}

    </style>
</head>
<body>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Adicionando a classe modal-dialog-centered -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirmar Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Você tem certeza que deseja sair?
            </div>
            <div class="modal-footer">
                <form method="POST" action="logout.php"> <!-- Altere para o script de logout -->
                    <button type="submit" name="logout" class="btn btn-danger">Sim, sair</button>
                </form>
                <button type="button" class="btn btn-secondary" id="cancelButton">Não, cancelar</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exibe o modal de confirmação
        var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
        logoutModal.show();

        // Adiciona ação ao botão "Não, cancelar"
        document.getElementById('cancelButton').addEventListener('click', function () {
            window.history.back(); // Volta para a página anterior
        });
    </script>
</body>
</html>
