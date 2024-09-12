<!DOCTYPE html>
<html>
<head>
    <style>
        /* Defina o estilo de fundo aqui */
        .email-background {
            background-image: url('https://i.imgur.com/Yg6hTLn.jpeg');
            background-size: cover;
            background-position: center;
            padding: 20px;
            color: #333; /* Cor do texto */
        }
        .email-container {
            background-color: #ffffff; /* Cor de fundo do conteúdo */
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
            font-family: Arial, sans-serif; /* Fonte padrão */
        }
        h1 {
            font-size: 24px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        a {
            color: #007bff; /* Cor dos links */
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-background">
        <div class="email-container">
            <h1>Sua solicitação Respondida</h1>

            <p>Sua solicitação sobre "{{ $chat->assunto }}" foi respondida.</p>
            <p>Verifique no site <a href="https://escuta.amttdetra.com">Escuta</a> ou no aplicativo <a href="https://play.google.com/store/apps/details?id=amttdetra.horarios_transporte&pcampaignid=web_share">Conecta Bus</a> para verificar a resposta.</p>

            <p>Obrigado por usar nosso serviço!</p>
        </div>
    </div>
</body>
</html>
