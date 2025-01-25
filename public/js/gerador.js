document.getElementById('send-form').addEventListener('click', function (e) {
    e.preventDefault(); // Evitar o envio padrão do formulário

    // Coletando os dados do formulário
    const nome = document.getElementById('nome').value.trim();
    const cpf = document.getElementById('cpf').value.trim();
    const endereco = document.getElementById('endereco').value.trim();
    const matricula = document.getElementById('matricula').value.trim();
    const curso = document.getElementById('curso').value.trim();
    const estabelecimento = document.getElementById('estabelecimento').value.trim();

    // Verifica se todos os campos estão preenchidos
    if (!nome || !cpf || !endereco || !matricula || !curso || !estabelecimento) {
        alert('Todos os campos são obrigatórios!');
        return;
    }

    // Previne múltiplos cliques no botão enquanto o processamento ocorre
    const sendButton = document.getElementById('send-form');
    sendButton.innerText = 'Processando...';
    sendButton.disabled = true;

    // Enviar os dados para o backend para verificar se o CPF já está cadastrado
    fetch('api/salvar_dados.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nome, cpf, endereco, matricula, curso, estabelecimento })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Exibe a mensagem de sucesso antes de gerar o QR Code
                const userConfirmed = confirm('Usuário cadastrado com sucesso. Clique em OK para exibir seu QR Code.');

                if (userConfirmed) {
                    // Gerar o QR Code após a confirmação
                    const qrData = `${nome};${cpf};${endereco};${matricula};${curso};${estabelecimento}`;

                    // Gerar o QR Code
                    QRCode.toDataURL(qrData, { width: 300, errorCorrectionLevel: 'H' }, function (err, url) {
                        if (err) {
                            alert('Erro ao gerar QR Code');
                            sendButton.innerText = 'Enviar Formulário';
                            sendButton.disabled = false;
                            return;
                        }

                        const qrImg = document.getElementById('qr-img');
                        const downloadLink = document.getElementById('download-link');
                        qrImg.src = url;
                        downloadLink.href = url;

                        const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
                        qrModal.show();

                        // Limpar os campos do formulário após o sucesso
                        document.getElementById('nome').value = '';
                        document.getElementById('cpf').value = '';
                        document.getElementById('endereco').value = '';
                        document.getElementById('matricula').value = '';
                        document.getElementById('curso').value = '';
                        document.getElementById('estabelecimento').value = '';
                    });
                } else {
                    // Caso o usuário não clique em OK, apenas retorne ao estado original
                    sendButton.innerText = 'Enviar Formulário';
                    sendButton.disabled = false;
                }
            } else {
                // Exibe a mensagem de erro, como "CPF já cadastrado"
                alert(data.message);
                sendButton.innerText = 'Enviar Formulário';
                sendButton.disabled = false;
            }
        })
        .catch(err => {
            alert('Erro ao enviar dados: ' + err.message);
            sendButton.innerText = 'Enviar Formulário';
            sendButton.disabled = false;
        });
});
