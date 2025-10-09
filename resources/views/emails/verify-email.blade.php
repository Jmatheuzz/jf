<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirme seu e-mail</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .container { background: #fff; max-width: 600px; margin: 40px auto; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; }
        .header h1 { color: #004d4d; }
        .content { margin-top: 20px; line-height: 1.6; color: #333; text-align: center; }
        .code { font-size: 28px; font-weight: bold; letter-spacing: 4px; background: #f5ffffff; color: #004d4d; padding: 10px 20px; border-radius: 8px; display: inline-block; margin-top: 15px; }
        .footer { margin-top: 40px; text-align: center; font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirme seu e-mail</h1>
        </div>
        <div class="content">
            <p>Olá, {{ $nome }} 👋</p>
            <p>Seu código de verificação é:</p>
            <div class="code">{{ $codigo }}</div>
            <p>Digite este código no site para concluir seu cadastro.</p>
            <p>Ele expira em {{ $expiracao }} minutos.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
