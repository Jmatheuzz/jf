<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Recuperação de senha</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f9; margin:0; padding:0; }
    .box { background:#fff; max-width:600px; margin:40px auto; border-radius:10px;
           padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:center; }
    .code { font-size:28px; font-weight:bold; color:#FFF;
            background:#004d4d; padding:10px 20px; border-radius:8px; display:inline-block; margin:20px 0; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Redefina sua senha</h2>
    <p>Olá, {{ $nome }} 👋</p>
    <p>Use o código abaixo para redefinir sua senha:</p>
    <div class="code">{{ $codigo }}</div>
    <p>Este código é válido por {{ $expiracao }} minutos.<br>
       Se você não solicitou, ignore este e-mail.</p>
  </div>
</body>
</html>
