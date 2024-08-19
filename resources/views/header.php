<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
</head>

<body>
    <header>
        <div class="bg"></div>
        <div class="bg" id="bg"></div>

        <?php if ($_SESSION['nivel_acesso'] != 'age') { ?>
            <a href="/painel.php" class="icone-menu-principal" title="Menu Principal"><img src="/imagens/logopmpgdetra.png" class="logo icone-menu-principal"></a>
        <?php } ?>
        <div class="divNomeMat">
            <div>
                <h1 class="title">
                    <h2 id="servNome"><?php echo $_SESSION['nomeServidor'] ?></h2>
                </h1>
                <span style="font-size:18px;" id="dropdownMenuButton" data-toggle="dropdown" class="btn dropdown badge badge-light">
                    <i class="fas fa-bell "></i> <span id="numeroGeral"> </span>
                </span>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <span class="dropdown-item"> <a target="_blank" style="color:black" href="/notificacao/atividades.php?status=1">Atividades </a> <span id="atividades"> </span></span>
                    <span class="dropdown-item"> <a target="_blank" style="color:black" href="/notificacao/bloqueio.php?status=1">Bloqueio</a> <span id="bloqueio"> </span></span>
                </div>
                <a href="/logout.php" title="Sair">
                    <div class="card-icone-sair"><i class="fas fa-sign-out-alt"></i></div>
                </a>
            </div>
        </div>
        <script src="../js/popper.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/jquery.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
    </header>
</body>

</html>
<script>
    $(document).ready(function() {
        setInterval(function() {
            $.ajax({
                type: "GET",
                dataType: "JSON",
                async: false,
                url: "/notificacao/atividades_ajax.php?case=getNotificacao",
                success: function(d) {
                    $('#numeroGeral').empty();
                    $('#atividades').empty();
                    //$('#bloqueio').empty();

                    if (parseInt(d[0].atividades) > 0 /*||  parseInt(d[1].bloqueio) > 0 */ ) {
                        $('#numeroGeral').append(parseInt(d[0].atividades) /*+ parseInt(d[1].bloqueio)  */ );
                    }
                    if (parseInt(d[0].atividades) > 0) {
                        $('#atividades').append("<a target='_blank' href='/notificacao/atividades.php?status=2' class=' btn btn-primary'>" + d[0].atividades + "</a>");

                    }
                    // if (parseInt(d[1].bloqueio) > 0) {

                    //       $('#bloqueio').append("<a target='_blank' href='/notificacao/bloqueio.php?status=2' class='btn btn-primary'>" + d[1].bloqueio + "</a>");

                    //   }
                },
                error: function(d) {

                }
            });
        }, 900000);

        setInterval(function() {

            $.ajax({

                type: "GET",
                dataType: "JSON",
                async: false,
                url: "/crudHorarios/horarios_ajax.php?case=verificaAgendamento",

                success: function(d) {



                },
                error: function(d) {

                }
            });


        }, 60000);
    });
</script>