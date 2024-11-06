<?php
session_start();
$_SESSION['usuarioLogado'] = 'Diluann';
$_SESSION['nomeServidor'] = 'DILUAN';
$_SESSION['idServidor'] = 117;
$_SESSION['nivel_acesso'] = 'adm';
$_SESSION['nivel_agencia'] = 0;
$_SESSION['adm'] = 1;

include base_path('app/Http/verifica_conexao.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('assets/phosphor/phosphor.js') }}"></script>
    <title>Painel ADM</title>
</head>

<body>
    {{-- @include('header') --}}
    <div id="app">
        <App />
    </div>
    @vite('resources/js/app.js')
    <script>
        window.nomeServidor = "{{ $_SESSION['nomeServidor'] }}";
        window.idServidor = "{{ $_SESSION['idServidor'] }}";
    </script>
    
</body>

<style>
    #app {
        background: rgb(0, 108, 119);
        background: linear-gradient(90deg, rgba(0, 108, 119, 1) 10%, rgba(0, 68, 108, 1) 45%, rgba(0, 18, 29, 1) 83%);
        height: 100%;
    }
</style>

</html>
