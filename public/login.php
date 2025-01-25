<?php
// Inclui o arquivo de conexão com o banco de dados
include_once(__DIR__ . '/../config/conexao.php');
session_start();

// Função para validar o CPF
function validarCPF($cpf) {
    // Remover caracteres não numéricos
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Verificar se o CPF tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verificar se o CPF é uma sequência de números repetidos (ex: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Cálculo do CPF
    $soma1 = 0;
    $soma2 = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $soma1 += $cpf[$i] * (10 - $i);
        $soma2 += $cpf[$i] * (11 - $i);
    }
    
    $digito1 = $soma1 % 11 < 2 ? 0 : 11 - ($soma1 % 11);
    $digito2 = $soma2 % 11 < 2 ? 0 : 11 - ($soma2 % 11);
    
    return $cpf[9] == $digito1 && $cpf[10] == $digito2;
}

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: /public/login.php');  // Redireciona para home.php
    exit;
}

// Verifica se foi enviado o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    // Valida o CPF
    if (!validarCPF($cpf)) {
        $erro = "CPF não Cadastrado!";
    } else {
        // Conexão com o banco de dados
        $conn = new mysqli('localhost', 'root', '', 'sistema_cadastro_qrcode');
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        // Consulta para verificar o usuário
        $sql = "SELECT * FROM usuario_admin WHERE cpf = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $cpf);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            // Verifica a senha
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                header('Location: /app/views/home.php');  // Redireciona para home.php
                exit;
            } else {
                $erro = "Usuário ou Senha Incorreta!";
            }
        } else {
            $erro = "Usuário não Cadastrado. Precisa se Cadastrar para logar no Sistema.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/login.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="form-container p-3 p-md-5">
                    <h3 class="text-center mb-4">Login</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF:</label>
                            <input type="text" id="cpf" name="cpf" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha:</label>
                            <div class="input-group">
                                <input type="password" id="senha" name="senha" class="form-control" required>
                                <span class="input-group-text" id="eyeIcon" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-column">
                         <a href="cadastro.php" class="btn btn-secondary w-100 mb-1">
                            <i class="bi bi-person-plus"></i> Cadastre-se 
                         </a>
                         <button type="submit" name="login" class="btn btn-primary w-100">
                         <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de erro -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Erro de Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo isset($erro) ? $erro : ''; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script>
    // Formatação automática do CPF e limita a 11 dígitos
    document.getElementById('cpf').addEventListener('input', function(e) {
        let cpf = e.target.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (cpf.length <= 11) {
            if (cpf.length <= 3) {
                e.target.value = cpf;
            } else if (cpf.length <= 6) {
                e.target.value = cpf.replace(/(\d{3})(\d{1,})/, '$1.$2');
            } else if (cpf.length <= 9) {
                e.target.value = cpf.replace(/(\d{3})(\d{3})(\d{1,})/, '$1.$2.$3');
            } else {
                e.target.value = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{1,})/, '$1.$2.$3-$4');
            }
        } else {
            e.target.value = cpf.slice(0, 11); // Limita CPF a 11 caracteres
        }
    });

    // Limita a senha a 8 caracteres
    document.getElementById('senha').addEventListener('input', function(e) {
        let senha = e.target.value;
        if (senha.length > 8) {
            e.target.value = senha.slice(0, 8); // Limita a senha a 8 caracteres
        }
    });

    // Exibir ou ocultar a senha
    document.getElementById('eyeIcon').addEventListener('click', function() {
        const senhaInput = document.getElementById('senha');
        const icon = this.querySelector('i');
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            senhaInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Remover pontos e hífen antes de enviar o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        let cpfInput = document.getElementById('cpf');
        cpfInput.value = cpfInput.value.replace(/\D/g, ''); // Remove qualquer caractere não numérico
    });

    <?php if (isset($erro)) { ?>
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    <?php } ?>
</script>

</body>
</html>
