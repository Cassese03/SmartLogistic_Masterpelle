<!doctype html>
<html lang="en" class="md">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, user-scalable=no, shrink-to-fit=no, viewport-fit=cover">
    <link rel="apple-touch-icon" href="/img/icona_arca.png">
    <link rel="icon" href="/img/icona_arca.png">
    <link rel="stylesheet" href="/vendor/bootstrap-4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="/vendor/materializeicon/material-icons.css">
    <link rel="stylesheet" href="/vendor/swiper/css/swiper.min.css">
    <link id="theme" rel="stylesheet" href="/css/style.css" type="text/css">
    <title>Smart Logistic</title>
</head>

<body class="color-theme-blue push-content-right theme-light">

<div class="wrapper">
    <div class="page">
        <header class="row m-0 fixed-header">
            <div class="left">
                <a style="padding-left:20px;" href="/"><i class="material-icons">arrow_back_ios</i></a>
            </div>
            <div class="col center">
                <a href="#" class="logo">
                    <figure><img src="/img/logo_arca.png" alt=""></figure>
                    Scegli il documento</a>
            </div>
            <div class="right">
                <a style="padding-left:20px;" href="/"><i class="material-icons">home</i></a>
            </div>
        </header>

        <div class="page-content">
            <div class="content-sticky-footer">

                <div class="background bg-170"><img src="/img/background.png" alt=""></div>
                <div class="w-100">
                    <h1 class="text-center text-white title-background">Documenti da Lavorare</h1>
                </div>

                <ul class="list-group">
                    <?php if (sizeof($documenti) > 0){ ?>
                        <?php foreach ($documenti as $d){ ?>

                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo ($d->Cd_Do != 'OVC' && $d->Cd_Do != 'LIS' && $d->Cd_Do != 'PRC')?URL::asset('magazzino/carico3/4383/'.$d->Cd_Do):URL::asset('magazzino/carico2/'.$d->Cd_Do); ?>">
                            <div class="media-body">
                                <h5><?php echo $d->Cd_Do; ?></h5>
                                <p><?php echo $d->Descrizione; ?> </p>
                            </div>
                        </a>
                    </li>
                    <?php } ?>
                    <?php } else { ?>
                        <?php if ($produttore == 0 && $tipo == 0 && $stagione == 0) { ?>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/MST'); ?>">
                            <div class="media-body">
                                <h5>MASTER</h5>
                                <p>Produzione MasterPelle</p>
                            </div>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/DFR'); ?>">
                            <div class="media-body">
                                <h5>DFOUR</h5>
                                <p>Produzione DFOUR</p>
                            </div>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/ALT'); ?>">
                            <div class="media-body">
                                <h5>ALTRO</h5>
                                <p>Altri Documenti</p>
                            </div>
                        </a>
                    </li>
                    <?php } ?>
                        <?php if ($produttore != 0 && $tipo == 0 && $stagione == 0) { ?>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/'.$produttore.'/INT'); ?>">
                            <div class="media-body">
                                <h5>INTERNA</h5>
                                <p>Produzione Interna</p>
                            </div>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/'.$produttore.'/CLI'); ?>">
                            <div class="media-body">
                                <h5>CLIENTE</h5>
                                <p>Produzione x Cliente</p>
                            </div>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/'.$produttore.'/STG'); ?>">
                            <div class="media-body">
                                <h5>STAGIONE</h5>
                                <p>Produzione x Stagione</p>
                            </div>
                        </a>
                    </li>
                    <?php } ?>
                        <?php if ($produttore != 0 && $tipo != 0 && $stagione == 0) { ?>

                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/'.$produttore.'/'.$tipo.'/INV'); ?>">
                            <div class="media-body">
                                <h5>INVERNO</h5>
                                <p>Produzione x INVERNO</p>
                            </div>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a class="media"
                           href="<?php echo URL::asset('scegli_doc/'.$produttore.'/'.$tipo.'/PRV'); ?>">
                            <div class="media-body">
                                <h5>PRIMAVERA</h5>
                                <p>Produzione x PRIMAVERA</p>
                            </div>
                        </a>
                    </li>
                    <?php } ?>
                    <?php } ?>
                </ul>

            </div>
        </div>
    </div>
    <!-- page main ends -->

</div>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/vendor/bootstrap-4.1.3/js/bootstrap.min.js"></script>
<script src="/vendor/cookie/jquery.cookie.js"></script>
<script src="/vendor/sparklines/jquery.sparkline.min.js"></script>
<script src="/vendor/circle-progress/circle-progress.min.js"></script>
<script src="/vendor/swiper/js/swiper.min.js"></script>
<script src="/js/main.js"></script>
</body>

</html>
