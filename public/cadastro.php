<?php

// Inclui o arquivo de conexão com o banco de dados
include_once(__DIR__ . '/../config/conexao.php');

// Verifica se o formulário de cadastro foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastro'])) {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cpf = preg_replace('/\D/', '', $cpf); // Remove os caracteres não numéricos do CPF
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $matricula = $_POST['matricula'];

    // Conexão com o banco de dados
    $conn = new mysqli('localhost', 'root', '', 'sistema_cadastro_qrcode');
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Verificar se o CPF ou Matrícula já existe
    $sql = "SELECT id FROM usuario_admin WHERE cpf = ? OR matricula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $cpf, $matricula);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $erro = "𝑪𝑷𝑭 𝒐𝒓 𝒎𝒂𝒕𝒓𝒊́𝒄𝒖𝒍𝒂 𝒋𝒂́ 𝒄𝒂𝒅𝒂𝒔𝒕𝒓𝒂𝒅𝒐𝒒𝒔. 𝑻𝒆𝒏𝒕𝒆 𝒏𝒐𝒗𝒂𝒎𝒆𝒏𝒕𝒆 𝒄𝒐𝒎 𝒅𝒂𝒅𝒐𝒔 𝒅𝒊𝒇𝒆𝒓𝒆𝒏𝒕𝒆𝒔.";
    } else {
        // Insere o novo usuário
        $sql = "INSERT INTO usuario_admin (nome, cpf, senha, matricula) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $nome, $cpf, $senha, $matricula);

        if ($stmt->execute()) {
            $sucesso = "𝑪𝒂𝒅𝒂𝒔𝒕𝒓𝒐 𝒓𝒆𝒂𝒍𝒊𝒛𝒂𝒅𝒐 𝒄𝒐𝒎 𝒔𝒖𝒄𝒆𝒔𝒔𝒐! 𝑭𝒂𝒄̧𝒂 𝒍𝒐𝒈𝒊𝒏.";
            // Redireciona para a página de login após 5 segundos
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 5000);
                  </script>";
        } else {
            $erro = "Erro ao cadastrar. Tente novamente.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema de Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Inclui o Font Awesome para o ícone de olho -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/public/css/cadastro.css">
  
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-12 col-md-4 form-container">
            <h3 class="text-center mb-4">Cadastro</h3>
            <form method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>
               <!-- Alteração no campo CPF -->
                <div class="mb-3">
                   <label for="cpf" class="form-label">CPF:</label>
                   <input type="text" id="cpf" name="cpf" class="form-control" maxlength="14" required oninput="mascaraCPF(this)">
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha:</label>
                    <div class="input-group">
                        <input type="password" id="senha" name="senha" class="form-control" maxlength="8" required>
                        <span class="input-group-text" id="eye-icon" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="matricula" class="form-label">Matrícula:</label>
                    <input type="text" id="matricula" name="matricula" class="form-control" maxlength="7" required>
                </div>
                <button type="submit" name="cadastro" class="btn btn-primary w-100">Cadastrar</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-secondary w-100">Já tem conta? Faça login</a>
            </div>
        </div>
    </div>

    <!-- Modal de Erro -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">𝑴𝒆𝒏𝒔𝒂𝒈𝒆𝒎</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo isset($erro) ? $erro : ''; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">𝑭𝒆𝒄𝒉𝒂𝒓</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Sucesso -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">𝘚𝘶𝘤𝘦𝘴𝘴𝘰</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo isset($sucesso) ? $sucesso : ''; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">𝑭𝒆𝒄𝒉𝒂𝒓</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exibe a modal de sucesso após cadastro
        <?php if (isset($sucesso)) { ?>
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        <?php } ?>
        
        // Função para mostrar/ocultar a senha
        document.getElementById('eye-icon').addEventListener('click', function() {
            var senhaField = document.getElementById('senha');
            var eyeIcon = this.querySelector('i');
            if (senhaField.type === 'password') {
                senhaField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                senhaField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Função para aplicar a máscara de CPF
        function mascaraCPF(input) {
            input.value = input.value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
    </script>
</body>
</html>
