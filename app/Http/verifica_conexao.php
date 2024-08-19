<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!$_SESSION['usuarioLogado']){
    header("Location: /index.php");
    exit();
}
