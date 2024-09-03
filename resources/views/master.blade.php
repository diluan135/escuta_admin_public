<?php
session_start();
$_SESSION['usuarioLogado'] = 'Diluann';
$_SESSION['nomeServidor'] = 'DILUAN';
$_SESSION['idServidor'] = 117;
$_SESSION['nivel_acesso'] = 'adm';
$_SESSION['nivel_agencia'] = 0;
$_SESSION['adm'] = 1;

include(base_path('app/Http/verifica_conexao.php'));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Painel ADM</title>
</head>
<body>
    {{-- @include('header') --}}
    <div style="background-color:blueviolet; margin: 0; padding:0;">
        <div> <?php
            print_r($_SESSION);
        ?></div>
        <div id="app">
            <App />
        </div>
    </div>

    @vite('resources/js/app.js')
    <script>
        window.nomeServidor = "{{ $_SESSION['nomeServidor'] }}";
        window.idServidor = "{{ $_SESSION['idServidor'] }}";
    </script>
</body>
</html>
