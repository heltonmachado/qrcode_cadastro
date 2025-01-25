<?php
// Inclui o arquivo de conexão com o banco de dados
include_once(__DIR__ . '/../config/conexao.php');
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /public/login.php'); // Redireciona para a página de login
    exit;
}

// Obter a conexão com o banco de dados
$conn = getConnection();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leitor de QR Code</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/public/css/leitor.css" />

    <!-- Fonte Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Barra de navegação -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="text-center w-100">
                <img src="/public/images/EAPLOGO.png" alt="Logo" class="img-fluid" style="max-width: 100px; height: auto;" />
            </div>
            <ul class="navbar-nav w-100 justify-content-center">
                <li class="nav-item"><a class="nav-link" href="/app/views/home.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/gerador_qrcode.php">Cadastro</a></li>
                <li class="nav-item active"><a class="nav-link" href="/public/alunos.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/leitor.php">Leitor QR Code</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <p class="text-center">Aponte o QR Code para a câmera.</p>
        <div class="scanner-container">
            <video id="video" autoplay></video>
            <canvas id="canvas"></canvas>
        </div>

        <!-- Container de erro -->
        <div class="alert alert-danger mt-3" id="output" style="display: none;"></div>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="presenceModal" tabindex="-1" aria-labelledby="presenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="presenceModalLabel">Presença Confirmada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Presença registrada com sucesso!</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Biblioteca jsQR -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const canvasContext = canvas.getContext('2d');
        const output = document.getElementById('output');

        // Configura a câmera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then((stream) => {
                video.srcObject = stream;
                video.setAttribute('playsinline', true); // Compatibilidade com iOS
                video.play();
                requestAnimationFrame(tick);
            })
            .catch(err => {
                showError(`Erro ao acessar a câmera: ${err.message}`);
            });

        // Função para mostrar mensagens de erro
        function showMessage(message, isSuccess = false) {
            output.style.display = 'block';
            output.textContent = message;
            output.classList.remove('alert-danger', 'alert-info');
            output.classList.add(isSuccess ? 'alert-info' : 'alert-danger');
            setTimeout(() => { output.style.display = 'none'; }, 5000);
        }

        // Função para mostrar mensagens de erro
        function showError(message) {
            showMessage(message, false);
        }

        // Função para mostrar mensagens de sucesso
        function showSuccess(message) {
            showMessage(message, true);

            // Exibir a modal de presença confirmada
            const presenceModal = new bootstrap.Modal(document.getElementById('presenceModal'));
            presenceModal.show();

            // Redirecionar para outra página após 10 segundos
            setTimeout(() => {
                window.location.href = 'gerador_qrcode.php';  // Substitua 'gerador_qrcode.php' pelo URL desejado
            }, 10000); // 10 segundos
        }

        // Função para exibir a mensagem de "Presença já registrada"
        function showPresenceAlreadyRegistered() {
            showMessage('Presença já registrada para este aluno.', false);
        }

        // Analisa o frame da câmera
        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);

                // Aumente a sensibilidade alterando a correção de erro ou outras opções do jsQR
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert', // Para tentar não inverter a imagem
                    correctLevel: 'H' // Nível de correção de erro mais alto
                });

                if (code) {
                    // Enviar os dados para o backend
                    fetch('api/salvar_presencas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ qrCodeData: code.data })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess('Presença registrada com sucesso!');
                        } else {
                            if (data.message === 'Presença já registrada') {
                                showPresenceAlreadyRegistered();
                            } else {
                                showError(`Atenção!: ${data.message}`);
                            }
                        }
                    })
                    .catch(err => showError('Erro ao tentar registrar presença.'));
                }
            }
            requestAnimationFrame(tick);
        }
    </script>
</body>
</html>
