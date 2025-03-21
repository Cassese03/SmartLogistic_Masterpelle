<?php $magazzino_ord = DB::select('SELECT * from MG'); //TODO MANCANO GLI ALERT?>
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

<style>
    @charset "UTF-8";
    .collapsable-source pre {
        font-size: small;
    }

    .input-field {
        display: flex;
        align-items: center;
        width: 260px;
        autocomplete: off;
    }

    .input-field label {
        flex: 0 0 auto;
        padding-right: 0.5rem;
        autocomplete: off;
    }

    .input-field input {
        flex: 1 1 auto;
        height: 20px;
        autocomplete: off;
    }

    .input-field button {
        flex: 0 0 auto;
        height: 28px;
        font-size: 20px;
        width: 40px;
        autocomplete: off;
    }

    .icon-barcode {
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
        background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiI+PHBhdGggZD0iTTAgNGg0djIwaC00ek02IDRoMnYyMGgtMnpNMTAgNGgydjIwaC0yek0xNiA0aDJ2MjBoLTJ6TTI0IDRoMnYyMGgtMnpNMzAgNGgydjIwaC0yek0yMCA0aDF2MjBoLTF6TTE0IDRoMXYyMGgtMXpNMjcgNGgxdjIwaC0xek0wIDI2aDJ2MmgtMnpNNiAyNmgydjJoLTJ6TTEwIDI2aDJ2MmgtMnpNMjAgMjZoMnYyaC0yek0zMCAyNmgydjJoLTJ6TTI0IDI2aDR2MmgtNHpNMTQgMjZoNHYyaC00eiI+PC9wYXRoPjwvc3ZnPg==);
    }

    .overlay {
        overflow: hidden;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .overlay__content {
        top: 50%;
        position: absolute;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-height: 90%;
        max-width: 800px;
    }

    .overlay__close {
        position: absolute;
        right: 0;
        padding: 0.5rem;
        width: 2rem;
        height: 2rem;
        line-height: 2rem;
        text-align: center;
        background-color: white;
        cursor: pointer;
        border: 3px solid black;
        font-size: 1.5rem;
        margin: -1rem;
        border-radius: 2rem;
        z-index: 100;
        box-sizing: content-box;
    }

    .overlay__content video {
        width: 100%;
        height: 100%;
    }

    .overlay__content canvas {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
    }

    #interactive.viewport {
        position: relative;
    }

    #interactive.viewport > canvas, #interactive.viewport > video {
        max-width: 100%;
        width: 100%;
    }

    canvas.drawing, canvas.drawingBuffer {
        position: absolute;
        left: 0;
        top: 0;
    }

    /* line 16, ../sass/_viewport.scss */
    .controls fieldset {
        border: none;
        margin: 0;
        padding: 0;
    }

    /* line 19, ../sass/_viewport.scss */
    .controls .input-group {
        float: left;
        autocomplete: off;
    }

    /* line 21, ../sass/_viewport.scss */
    .controls .input-group input, .controls .input-group button {
        display: block;
        autocomplete: off;
    }

    /* line 25, ../sass/_viewport.scss */
    .controls .reader-config-group {
        float: right;
    }

    /* line 28, ../sass/_viewport.scss */
    .controls .reader-config-group label {
        display: block;
    }

    /* line 30, ../sass/_viewport.scss */
    .controls .reader-config-group label span {
        width: 9rem;
        display: inline-block;
        text-align: right;
    }

    /* line 37, ../sass/_viewport.scss */
    .controls:after {
        content: '';
        display: block;
        clear: both;
    }

    /* line 22, ../sass/_viewport.scss */
    #result_strip {
        margin: 10px 0;
        border-top: 1px solid #EEE;
        border-bottom: 1px solid #EEE;
        padding: 10px 0;
    }

    /* line 28, ../sass/_viewport.scss */
    #result_strip ul.thumbnails {
        padding: 0;
        margin: 0;
        list-style-type: none;
        width: auto;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
    }

    /* line 37, ../sass/_viewport.scss */
    #result_strip ul.thumbnails > li {
        display: inline-block;
        vertical-align: middle;
        width: 160px;
    }

    /* line 41, ../sass/_viewport.scss */
    #result_strip ul.thumbnails > li .thumbnail {
        padding: 5px;
        margin: 4px;
        border: 1px dashed #CCC;
    }

    /* line 46, ../sass/_viewport.scss */
    #result_strip ul.thumbnails > li .thumbnail img {
        max-width: 140px;
    }

    /* line 49, ../sass/_viewport.scss */
    #result_strip ul.thumbnails > li .thumbnail .caption {
        white-space: normal;
    }

    /* line 51, ../sass/_viewport.scss */
    #result_strip ul.thumbnails > li .thumbnail .caption h4 {
        text-align: center;
        word-wrap: break-word;
        height: 40px;
        margin: 0px;
    }

    /* line 61, ../sass/_viewport.scss */
    #result_strip ul.thumbnails:after {
        content: "";
        display: table;
        clear: both;
    }

    @media (max-width: 603px) {
        /* line 2, ../sass/phone/_core.scss */
        #container {
            margin: 10px auto;
            -moz-box-shadow: none;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
    }

    @media (max-width: 603px) {
        /* line 5, ../sass/phone/_viewport.scss */
        .reader-config-group {
            width: 100%;
        }

        .reader-config-group label > span {
            width: 50%;
        }

        .reader-config-group label > select, .reader-config-group label > input {
            max-width: calc(50% - 2px);
            autocomplete: off;
        }

        #interactive.viewport {
            width: 100%;
            height: auto;
            overflow: hidden;
        }

        /* line 20, ../sass/phone/_viewport.scss */
        #result_strip {
            margin-top: 5px;
            padding-top: 5px;
        }

        #result_strip ul.thumbnails {
            width: 100%;
            height: auto;
        }

        /* line 24, ../sass/phone/_viewport.scss */
        #result_strip ul.thumbnails > li {
            width: 150px;
        }

        /* line 27, ../sass/phone/_viewport.scss */
        #result_strip ul.thumbnails > li .thumbnail .imgWrapper {
            width: 130px;
            height: 130px;
            overflow: hidden;
        }

        /* line 31, ../sass/phone/_viewport.scss */
        #result_strip ul.thumbnails > li .thumbnail .imgWrapper img {
            margin-top: -25px;
            width: 130px;
            height: 180px;
        }
    }
</style>

<body class="color-theme-blue push-content-right theme-light">

<div class="loader justify-content-center ">
    <div class="maxui-roller align-self-center">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<di class="wrapper">

    <!-- page main start -->
    <div class="page">
        <form class="searchcontrol">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text close-search"><i class="material-icons">keyboard_backspace</i>
                    </button>
                </div>
                <input type="text" id="cerca" class="form-control border-0" placeholder="Cerca Fornitore..."
                       aria-label="Username">
            </div>
        </form>
        <header class="row m-0 fixed-header">
            <div class="left">
                <a style="padding-left:20px;"
                   href="/magazzino/carico03/<?php echo $fornitore->Id_CF;echo "/";echo $documento->Cd_Do?>"><i
                        class="material-icons">arrow_back_ios</i></a>
            </div>
            <div class="col center">
                <a href="#" class="logo">
                    <figure><img src="/img/logo_arca.png" alt=""></figure>
                    Aggiungi Articoli</a>
            </div>
            <div class="right">
                <a style="padding-left:20px;" href="/"><i class="material-icons">home</i></a>
            </div>
        </header>

        <div class="page-content">
            <div class="content-sticky-footer">
                <input style="height: 1px;width: 1px" type="text" id="cerca_articolo2" onkeyup="check();" autofocus
                       autocomplete="off">
                <input type="hidden" id="lung" value="0">

                <div class="background bg-125"><img src="/img/background.png" alt=""></div>
                <div class="w-100">
                    <h1 class="text-center text-white title-background"><?php echo $fornitore->Descrizione ?>
                        <br><small><?php echo $documento->Cd_Do ?> N.<?php echo $documento->NumeroDoc ?>
                            Del <?php echo date('d/m/Y', strtotime($documento->DataDoc)) ?></small></h1>
                </div>


                <!--
                                 <fieldset class="reader-config-group" style="margin-top:50px;">
                                    <label>
                                        <span>Barcode-Type</span>
                                        <select name="decoder_readers">
                                            <option value="ean">EAN</option>
                                            <option value="ean_8">EAN-8</option>
                                        </select>
                                    </label>
                                </fieldset>
                -->

                <form method="post" id="session">
                    <input type="hidden" name="change_mg_session" value="change_mg_session">
                    <div style="width: 90%;display: flex;gap: 5%;margin:2% 5% 0 5%">
                        <div style="width: 90%;">
                            <label for="cd_mg_p" style="font-weight: bolder;">
                                Codice Magazzino Partenza
                            </label>
                            <input value="{{ $session_mag['cd_mg_p'] }}" class="form-control" id="cd_mg_p"
                                   name="cd_mg_p" onblur="change_mag()"
                                   list="magazzini_partenza">
                            <datalist id="magazzini_partenza">
                                @foreach($magazzini as $m)
                                    <option value="{{$m->Cd_MG}}"> {{$m->Descrizione}}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div style="width: 90%;">
                            <label for="cd_mg_a" style="font-weight: bolder;">
                                Codice Magazzino di Arrivo
                            </label>
                            <input value="{{ $session_mag['cd_mg_a'] }}" class="form-control" id="cd_mg_a"
                                   name="cd_mg_a" onblur="change_mag('cd_mg_a')"
                                   list="magazzini_arrivo">
                            <datalist id="magazzini_arrivo">
                                @foreach($magazzini as $m)
                                    <option value="{{$m->Cd_MG}}"> {{$m->Descrizione}}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                </form>

                <button
                    style="margin-top:10px !important;width:80%;margin:auto;display:block;background-color:lightblue;border:lightblue"
                    class="btn btn-primary" type="button"
                    onclick="url = window.location.href; pos = url.search('/magazzino'); url = url.substring(0,pos);
                    top.location.href = url + '/magazzino/carico4/'+'<?php echo $fornitore->Id_CF ?>'+'/'+'<?php echo $id_dotes ?>';">
                    Cambia Modalitá (Evadi Documento)
                </button>
                <button style="margin-top:20px !important;width:80%;margin:0 auto;display:block;margin-bottom:0;"
                        class="btn btn-primary" onclick="$('#modal_cerca_articolo').modal('show');">Aggiungi Prodotto
                </button>
                <?php if (sizeof($documento->righe) > 0){ ?>

                <div class="row">


                    <div class="col-sm-12 col-xl-12" style="margin-top:0px;">
                        <ul class="list-group">

                                <?php foreach ($documento->righe as $r){
                                $totale = 0; ?>

                            <li class="list-group-item">
                                <a href="#" onclick="" class="media">
                                    <div class="media-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <h5 <?php if ($r->QtaRes == 0) echo 'style="color: red"' ?>><?php echo $r->Cd_AR . ' - ' . $r->Descrizione; ?>
                                                    <br><?php echo 'Prezzo : ' . round(floatval($r->PrezzoUnitarioV), 2); ?>
                                                    <br> Qta: <?php echo floatval($r->QtaRes) ?>
                                                                  <?php echo '<br> Taglia : ' . $r->Taglia . ' <br> Colore : ' . $r->Colore; ?>
                                                                  <?php /* echo  'Magazzino di Partenza: '.$r->Cd_MG_P;if($r->Cd_MGUbicazione_A != null) echo ' - '.$r->Cd_MGUbicazione_A;?><br><?php echo' Magazzino di Arrivo: '.$r->Cd_MG_A;?><br><?php if($r->Cd_ARLotto != Null)echo 'Lotto: '.$r->Cd_ARLotto;*/ ?>
                                                </h5>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">

                                                <form method="post"
                                                      onsubmit="return confirm('Vuoi Eliminare Questa Riga ?')">
                                                    <button style="width:32%;" type="reset" name="segnalazione" value=""
                                                            class="btn btn-warning btn-sm"
                                                            onclick="$('#modal_segnalazione<?php echo $r->Id_DORig?>').modal('show');">
                                                        <i class="fa fa-exclamation-triangle" aria-hidden="true">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor"
                                                                 class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                                                <path
                                                                    d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                                                            </svg>
                                                        </i>
                                                    </button>
                                                    <input type="hidden" id="codice" value="<?php echo $r->Cd_AR ?>">
                                                    <button style="width:32%;" type="reset" name="modifica_riga"
                                                            value="<?php echo $r->Cd_AR;?>"
                                                            class="btn btn-primary btn-sm"
                                                            onclick="modifica('<?php echo $r->Id_DORig ?>')">
                                                        <i class="bi bi-pencil">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor" class="bi bi-pencil"
                                                                 viewBox="0 0 16 16">
                                                                <path
                                                                    d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                                            </svg>
                                                        </i>
                                                    </button>
                                                    <input type="hidden"
                                                           name="<?php echo $r->Id_DORig.'_'.$r->Taglia.'_'.$r->Colore;?>"
                                                           value="<?php echo $r->Id_DORig ?>">
                                                    <button style="width:32%;" type="submit" name="elimina_riga"
                                                            value="Elimina" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash-fill">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                 height="16" fill="currentColor"
                                                                 class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                                                            </svg>
                                                        </i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>

                            <?php } ?>
                        </ul>
                    </div>

                    <?php } ?>
                    <button
                        style="margin-top:10px !important;width:80%;margin:0 auto;display:block;background-color:#007bff;border: #007bff"
                        class="btn btn-primary" onclick="$('#modal_salva_documento').modal('show');">Salva Documento
                    </button>
                    {{--
                                        <button
                                            style="margin-top:10px !important;width:80%;margin:0 auto;display:block;background-color:#007bff;border: #007bff"
                                            class="btn btn-primary" onclick="stampa_doc()">Stampa Documento
                                        </button>
                    --}}
                    <button type="button"
                            style="margin-top:10px !important;width:80%;margin:0 auto;display:block;background-color:#007bff;border: #007bff"
                            class="btn btn-primary"
                            onclick="url = window.location.href; pos = url.search('/magazzino'); url = url.substring(0,pos);window.open(url + '/ajax/stampe/'+'<?php echo$id_dotes ?>');top.location.reload()">
                        Stampa Documento
                    </button>
                    <button style="display: none" id="next_doc" type="button">
                        PROSSIMO DOCUMENTO
                    </button>

                </div>
            </div>

        </div>
        <!-- page main ends -->

    </div>

    <div class="modal" id="modal_cerca_articolo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Carica Articolo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="$('#modal_cerca_articolo').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">

                    <label>Cerca Articolo</label>
                    <input class="form-control" type="text" id="cerca_articolo" value=""
                           placeholder="Inserisci barcode,codice o nome dell'articolo" autocomplete="off" autofocus>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            onclick="$('#modal_cerca_articolo').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                        Chiudi
                    </button>
                    <button type="button" class="btn btn-primary" onclick="cerca_articolo_smart1();check();">Cerca
                        Articolo
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="modal_lista_articoli" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Carica Articolo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_lista_articoli').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body" id="ajax_lista_articoli"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_lista_articoli').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php foreach ($documento->righe as $r){ ?>
    <div class="modal" id="modal_segnalazione<?php echo $r->Id_DORig?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Segnalazione</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_segnalazione<?php echo $r->Id_DORig?>').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <input type="number" class="form-control" id="Segnala_riga" value="<?php echo $r->Id_DORig;?>"
                               readonly><br>
                        <input type="text" class="form-control" id="Segnalazione" value=""
                               placeholder="Inserire Segnalazione..." autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_segnalazione<?php echo $r->Id_DORig?>').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                        <button type="button" class="btn btn-primary" onclick="segnalazione();">Invia Segnalazione
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>
    <div class="modal" id="modal_carico" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Carica Articolo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="$('#modal_carico').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="ajax_modal_carico"></div>
                    <label>Quantita</label>
                    <input class="form-control" type="number" id="modal_quantita" value="" required
                           placeholder="Inserisci una Quantità" autocomplete="off">
                    <input class="form-control" type="hidden" id="modal_Cd_AR" value="" required autocomplete="off">
                    <label>Taglia</label>
                    <select class="form-control" type="text" id="modal_taglie" onchange="cambioTaglia()"
                            autocomplete="off">
                        <option>No Taglia</option>
                    </select>
                    <label>Colore</label>
                    <select class="form-control" type="text" id="modal_colori" autocomplete="off">
                        <option id="reset">No Colore</option>
                    </select>
                    <label>Magazzino Partenza</label>
                    <select class="form-control" type="text" id="modal_cd_mg_p" autocomplete="off">
                        <option>Scegli il magazzino...</option>
                        <?php foreach ($magazzini as $m) { ?>
                        <option <?php if ($session_mag['cd_mg_p'] == $m->Cd_MG) echo 'selected'; ?>
                                value="<?php echo $m->Cd_MG ?>"><?php echo $m->Cd_MG . ' - ' . $m->Descrizione; ?></option>
                        <?php } ?>
                    </select>
                    <label>Magazzino Arrivo</label>
                    <select class="form-control" type="text" id="modal_cd_mg_a" autocomplete="off">
                        <option>Scegli il magazzino...</option>
                        <?php foreach ($magazzini as $m) { ?>
                        <option <?php if ($session_mag['cd_mg_a'] == $m->Cd_MG) echo 'selected'; ?>
                                value="<?php echo $m->Cd_MG ?>"><?php echo $m->Cd_MG . ' - ' . $m->Descrizione; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            onclick="$('#modal_carico').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                        Chiudi
                    </button>
                    <button type="button" class="btn btn-primary" onclick="carica_articolo();">Carica Articolo</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="modal_inserimento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Articolo Mancante</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input class="form-control" type="text" id="modal_inserimento_barcode" value=""
                               autocomplete="off">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        <button type="button" class="btn btn-primary" onclick="crea_articolo();">Crea Articolo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modal_lista_articoli_daevadere" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Articolo da Evadere</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_lista_articoli_daevadere').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <label>Articolo</label>
                        <input class="form-control" type="text" id="modal_controllo_articolo" value=""
                               autocomplete="off" readonly>

                        <label>Quantita</label>
                        <input class="form-control" type="text" id="modal_controllo_quantita" value=""
                               autocomplete="off" readonly>


                        <input class="form-control" type="hidden" id="modal_controllo_lotto" value="0"
                               autocomplete="off" readonly>

                        <input class="form-control" type="hidden" id="modal_controllo_dorig" value="" autocomplete="off"
                               readonly>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_lista_articoli_daevadere').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                        <button type="button" class="btn btn-primary"
                                onclick="$('#modal_lista_articoli_daevadere').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Evadi Riga
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal" id="modal_modifica_ajax" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modifica Variante</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_modifica_ajax').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="ajax_modal_modifica">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_modifica_ajax').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                        <button type="submit" name="modifica_riga" value="Salva" class="btn btn-primary">Salva</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php foreach ($documento->righe as $r) { ?>
    <div class="modal" id="modal_modifica_<?php  echo $r->Id_DORig ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modifica Articolo <?php echo $r->Cd_AR ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_modifica_<?php  echo $r->Id_DORig ?>').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="ajax_modal_modifica"></div>

                        <label>Quantita</label>
                        <input class="form-control" type="number" name="Qta" value="<?php echo floatval($r->Qta) ?>"
                               required placeholder="Inserisci una Quantità" autocomplete="off" step="0.01">
                            <?php /*
                        <label>Quantita Evadibile</label>
                        <input class="form-control" type="number" name="QtaRes" value="<?php echo floatval($r->QtaRes) ?>" required placeholder="Inserisci una Quantità" autocomplete="off" step="0.01">
                        <label>Prezzo</label>
                        <input class="form-control" type="number" name="PrezzoUnitarioV" value="<?php echo floatval($r->PrezzoUnitarioV) ?>" required placeholder="Inserisci un Prezzo" autocomplete="off" step="0.01" >
*/ ?>
                            <!--
                    <label>Prezzo (&euro;)</label>
                    <input class="form-control" type="number" name="PrezzoUnitarioV" value="<?php echo intval($r->PrezzoUnitarioV) ?>" required placeholder="Inserisci un Prezzo" autocomplete="off"; step="0.01">
-->
                        <input type="hidden" class="form-control" id="modal_magazzino_P_m"
                               value="00001 - Magazzino Centrale">
                        <input type="hidden" class="form-control" id="modal_ubicazione_A_m" name="modal_ubicazione_A_m"
                               value="0">
                        <input type="hidden" class="form-control" id="modal_magazzino_A_m"
                               value="00001 - Magazzino Centrale">
                        <input type="hidden" class="form-control" id="modal_lotto_m" name="modal_lotto_m" value="0">

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="Id_DORig" value="<?php echo $r->Id_DORig ?>">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_modifica_<?php  echo $r->Id_DORig ?>').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                        <button type="submit" name="modifica_riga" value="Salva" class="btn btn-primary">Salva</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>
    <div class="modal" id="modal_lista_salva" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Righe non Evase</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_lista_salva').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body" id="ajax_lista_documenti_1">
                    </div>
                    <?php foreach ($documento->righe as $r) { ?>
                    <input type="hidden" name="modal_Cd_ARLotto_c_<?php echo $r->Id_DORig?>"
                           id="modal_Cd_ARLotto_c_<?php echo $r->Id_DORig?>">
                    <input type="hidden" name="modal_Cd_AR_c_<?php echo $r->Id_DORig?>"
                           id="modal_Cd_AR_c_<?php echo $r->Id_DORig?>">
                    <input type="hidden" name="modal_Qta_c_<?php echo $r->Id_DORig?>"
                           id="modal_Qta_c_<?php echo $r->Id_DORig?>">
                    <input type="hidden" name="modal_Prezzo_c_<?php echo $r->Id_DORig?>"
                           id="modal_Prezzo_c_<?php echo $r->Id_DORig?>">
                    <?php } ?>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_lista_salva').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            Chiudi
                        </button>
                        <button type="button" class="btn btn-primary" onclick="invia();">Salva Documento</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal" id="modal_salva_documento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Salvataggio Documento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_salva_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <label>Vuoi Salvare il Documento ? </label>
                    </div>
                    <?php //$('#modal_salva_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_salva_documento').modal('hide');$('#modal_elimina_documento').modal('show');">
                            No
                        </button>
                        <button type="button" class="btn btn-primary" onclick="top.location.href='/'">Si</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modal_elimina_documento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Elimina Documento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="$('#modal_elimina_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <label>Vuoi Eliminare il Documento ? </label>
                    </div>
                    <?php //$('#modal_salva_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#modal_elimina_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                            No
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="elimina()">Si
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>


    {{--  <div class="modal" id="modal_stampa_documento" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
              <form method="post">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel">Stampa Documento</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                  onclick="$('#modal_stampa_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                              <span aria-hidden="true">×</span>
                          </button>
                      </div>

                      <div class="modal-body">

                          <label>Vuoi Stampare il Documento ? </label>
                      </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                  onclick="$('#modal_stampa_documento').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                              No
                          </button>
                          <button type="button" class="btn btn-primary"
                                  onclick="url = window.location.href; pos = url.search('/magazzino'); url = url.substring(0,pos);window.open(url + '/ajax/stampe/'+'<?php echo$id_dotes ?>');top.location.reload()">
                              Si
                          </button>
                      </div>
                  </div>
              </form>
          </div>
      </div>--}}

    <div class="modal" id="modal_alertSegnalazione" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert"
                    onclick="$('#modal_alertSegnalazione').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                &times;
            </button>
            <strong>Success!</strong><br> Segnalazione Effettuata</a>.
        </div>
    </div>


    <div class="modal" id="modal_alertQuantita" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert"
                    onclick="$('#modal_alertQuantita').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                &times;
            </button>
            <strong>Alert!</strong><br> Inserire una quantita </a>.
        </div>
    </div>

    <div class="modal" id="modal_alertInserimento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" onclick="location.reload()">&times;</button>
            <strong>Success!</strong><br> Articolo Inserito Correttamente</a>.
        </div>
    </div>

    <div class="modal" id="modal_alertUbicazione" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert"
                    onclick="$('#modal_alertUbicazione').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                &times;
            </button>
            <strong>Alert!</strong><br> Ubicazione inserita non corretta o inesistente</a>.
        </div>
    </div>

    <div class="modal" id="modal_alertTrovare" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert"
                    onclick="$('#modal_alertTrovare').modal('hide');$('#cerca_articolo2').val('');$('#cerca_articolo2').focus()">
                &times;
            </button>
            <strong>Alert!</strong><br> Nessun Articolo Trovato </a>.
        </div>
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
    <script src="//webrtc.github.io/adapter/adapter-latest.js" type="text/javascript"></script>
    <script src="/dist/quagga.js" type="text/javascript"></script>
    <script src="/js/live_w_locator.js" type="text/javascript"></script>
    <script src="/js/jquery.scannerdetection.js" type="text/javascript"></script>

</body>
</html>

<script type="text/javascript">

    function check_next_doc() {

        $.ajax({
            url: "<?php echo URL::asset('ajax/check_next_doc') ?>/" + {{$id_dotes}},
        }).done(function (result) {
            if (result !== 'NODOC') {
                element = document.getElementById('next_doc');
                element.style.display = 'block';
                element.style.backgroundColor = 'green';
                element.style.border = 'green';
                element.style.width = '80%';
                element.style.margin = '10px auto auto auto';
                element.classList.add('btn', 'btn-primary');
                element.onclick = function () {
                    var url = window.location.href;
                    var pos = url.search('/magazzino');
                    url = url.substring(0, pos);
                    window.open(url + '/magazzino/carico04' + result);
                    top.location.reload();
                };
            }
        });

    }

    $(document).ready(function () {
        if (document.getElementById('cd_mg_a').value == '')
            alert('Attenzione inserire il MAGAZZINO di ARRIVO');
        if (document.getElementById('cd_mg_p').value == '')
            alert('Attenzione inserire il MAGAZZINO di PARTENZA');
        check_next_doc();
    });

    function change_mag() {
        $('#session').submit();
    }

    function modifica(id_dorig) {
        $.ajax({
            url: "<?php echo URL::asset('ajax/modifica') ?>/" + id_dorig,
        }).done(function (result) {
            $('#modal_modifica_ajax').modal('show');
            $('#ajax_modal_modifica').html(result);
        });

    }

    cd_cf = '<?php echo $fornitore->Cd_CF ?>';
    document.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            let el = document.querySelector(':focus');
            if (el) el.blur();
        }
    });

    function salva_doc() {
        $.ajax({
            url: "<?php echo URL::asset('ajax/salva') ?>/<?php echo $id_dotes ?>"
        }).done(function (result) {
            top.location.href = '/';
        });
    }

    function stampa_doc() {
        $.ajax({
            url: "<?php echo URL::asset('ajax/stampe') ?>/<?php echo $id_dotes ?>"
        }).done(function (result) {
        });
    }

    function elimina() {
        $.ajax({
            url: "<?php echo URL::asset('ajax/elimina') ?>/<?php echo $id_dotes ?>"
        }).done(function (result) {
            if (result == 'Eliminato')
                alert('Documento Eliminato Correttamente');
            top.location.href = '/';
        });
    }

    function invia() {
        testo = 'Il documento (documento) è stato salvato.<br> Le righe del documento sono:';
        <?php foreach ($documento->righe as $r){ ?>
            articolo = '<?php echo $r->Cd_AR ?>';
        quantita = '<?php echo $r->Qta ?>';
        prezzo = '<?php echo $r->PrezzoUnitarioV ?>';
        testo = testo + '<br> Articolo ' + articolo + ' quantita\' ' + Number.parseFloat(quantita).toFixed(2) + ' prezzo ' + prezzo + '<br>';
        <?php } ?>
        $.ajax({
            url: "<?php echo URL::asset('ajax/invia_mail') ?>/<?php echo $id_dotes ?>/" + 3 + "/" + testo
        }).done(function (result) {
        });
    }

    function segnalazione() {
        Id_DoRig = document.getElementById('Segnala_riga').value;
        Segnalazione = document.getElementById('Segnalazione').value;
        if (Id_DoRig != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/segnalazione') ?>/<?php echo $id_dotes ?>/" + Id_DoRig + "/-" + encodeURIComponent(Segnalazione),
            }).done(function (result) {
                $('#modal_alertSegnalazione').modal('show');
            });
        }
        if (Id_DoRig != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/invia_mail') ?>/<?php echo $id_dotes ?>/" + 0 + "/-" + Segnalazione
            }).done(function (result) {
            });
        }
    }

    function check() {
        check2 = document.getElementById('cerca_articolo2').value;
        lung = document.getElementById('lung').value;
        if (check2.length != 0) {
            if (lung == check2.length) {
                cerca_articolo_smart1();
                document.getElementById('lung').value = 0;
                document.getElementById('cerca_articolo2').value = '';
            } else {
                document.getElementById('lung').value = check2.length;
                const myTimeout = setTimeout(check, 100);
                return;
            }
        }
    }

    function cambioTaglia() {
        taglia = $('#modal_taglie option:selected').attr('taglia');
        document.getElementById('reset').selected = 'selected';
        for (let elem of document.querySelectorAll("[id^=modal_colori_]")) {
            elem.style.display = "none";
        }
        for (let elem of document.querySelectorAll("[id^=modal_colori_" + taglia + "]")) {
            elem.style.display = "";
        }
    }

    function carica_articolo() {
        codice = $('#modal_Cd_AR').val();
        pos = codice.search('/');
        if (pos != (-1)) {
            codice = codice.substr(0, pos) + 'slash' + codice.substr(pos + 1)
        }
        quantita = $('#modal_quantita').val();
        /* prezzo    =      $('#modal_prezzo').val();*/
        magazzino_A = $('#modal_cd_mg_a').val();
        if (magazzino_A == '' || magazzino_A == 'undefined' || magazzino_A == undefined || magazzino_A == null || magazzino_A == 'Scegli il magazzino...')
            magazzino_A = 'ND';
        magazzino_P = $('#modal_cd_mg_p').val();
        if (magazzino_P == '' || magazzino_P == 'undefined' || magazzino_P == undefined || magazzino_P == null || magazzino_P == 'Scegli il magazzino...')
            magazzino_P = 'ND';
        ubicazione_A = $('#modal_ubicazione_A').val();
        if (ubicazione_A == '' || ubicazione_A == null)
            ubicazione_A = 'ND';
        taglia = $('#modal_taglie').val();
        if (taglia == '' || taglia == null)
            taglia = 'ND';
        colore = $('#modal_colori').val();
        if (colore == '' || colore == null)
            colore = 'ND';
        lotto = 0;
        if (quantita != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/aggiungi_articolo_ordine') ?>/<?php echo $id_dotes ?>/" + codice + "/" + quantita + "/" + magazzino_A.substring(0, 5) + "/" + ubicazione_A + "/" + lotto + "/" + magazzino_P.substring(0, 5) + "/" + 'ND' + "/" + taglia + "/" + colore
            }).done(function (result) {
                $('#modal_carico').modal('hide');
                $('#modal_Cd_AR').val('');
                $('#modal_quantita').val('');
                if (result == 'Ubicazione inserita inesistente in quel magazzino')
                    $('#modal_alertUbicazione').modal('show');
                if (result == 'Articolo Caricato Correttamente ')
                    location.reload();
                if (result == 'No Giac'){
                    alert('Articolo senza giacenza nel magazzino di partenza.');
                    location.reload();
                }
            });
        } else
            $('#modal_alertQuantita').modal('show');
    }

    function modifica_articolo() {
        codice = document.getElementById('codice').value;
        quantita = $('#modal_quantita_m').val();
        /* prezzo    =      $('#modal_prezzo_m').val();*/
        magazzino = $('#modal_magazzino_m').val();
        lotto = $('#modal_lotto_m').val();
        if (quantita != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/modifica_articolo_ordine') ?>/<?php echo $id_dotes ?>/" + codice + "/" + quantita + "/" + magazzino_A.substring(0, 5) + "/" + 'ND' + "/" + lotto + "/" + magazzino_P.substring(0, 5) + "/" + 'ND'
            }).done(function (result) {
                $('#modal_modifica').modal('hide');
                $('#modal_Cd_AR').val('');
                $('#modal_quantita').val('');
                $('#modal_magazzino').val();
                location.reload();
            });
        } else
            $('#modal_alertQuantita').modal('show');
    }

    function salva_documento() {
        Cd_Do = '0';
        Id_DoTes = '<?php echo $id_dotes ?>';
        magazzino_A = '0';
        if (Id_DoTes != '')
            $.ajax({
                url: "<?php echo URL::asset('ajax/salva_documento1') ?>/" + Id_DoTes + "/" + Cd_Do + "/" + magazzino_A.substring(0, 5)
            }).done(function (result) {
                $('#modal_salva_documento').modal('hide');
                $('#modal_lista_salva').modal('show');
            });
    }

    function cerca_articolo_smart() {
        testo = $('#cerca_articolo').val();
        if (testo == '')
            testo = $('#cerca_articolo2').val();
        testo = testo.trimEnd();
        pos = testo.search('/');
        if (pos != (-1)) {
            testo = testo.substr(0, pos) + 'slash' + testo.substr(pos + 1)
        }
        if (testo != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/cerca_articolo_smart') ?>/" + encodeURIComponent(testo) + "/" + cd_cf,
                context: document.body
            }).done(function (result) {
                if (result != '') {
                    pos = result.search('/');
                    if (pos != (-1)) {
                        result = result.substr(0, pos) + 'slash' + result.substr(pos + 1)
                    }
                    $('#modal_cerca_articolo').modal('hide');
                    cerca_articolo_codice(result.trim());
                } else
                    $('#modal_alertTrovare').modal('show');
            });
        }
    }

    function cerca_articolo_smart1() {
        testo = $('#cerca_articolo').val();
        if (testo == '')
            testo = $('#cerca_articolo2').val();
        testo = testo.trimEnd();
        pos = testo.search('/');
        if (pos != (-1)) {
            testo = testo.substr(0, pos) + 'slash' + testo.substr(pos + 1)
        }
        if (testo != '') {
            $.ajax({
                url: "<?php echo URL::asset('ajax/cerca_articolo_smart1') ?>/" + encodeURIComponent(testo) + "/" + cd_cf,
                context: document.body
            }).done(function (result) {
                if (result != '') {
                    pos = result.search('/');
                    if (pos != (-1)) {
                        result = result.substr(0, pos) + 'slash' + result.substr(pos + 1)
                    }
                    $('#modal_cerca_articolo').modal('hide');
                    //$('#modal_lista_articoli').modal('show');
                    eval(result);
                } else
                    $('#modal_alertTrovare').modal('show');
            });
        }
    }

    function cerca_articolo_codice(fornitore, codice, lotto, qta) {
        if (fornitore.length > 7) {
            const myArray = fornitore.split("','");
            codice = myArray[1];
            lotto = myArray[2];
            qta = myArray[3];
        }
        $.ajax({
            url: "/ajax/cerca_articolo_codice/<?php echo $fornitore->Cd_CF ?>/" + codice + "/" + lotto + "/" + qta,
            context: document.body
        }).done(function (result) {
            if (result != '') {
                $('#modal_carico').modal('show');
                $('#ajax_modal_carico').html(result);
                $('#modal_quantita').val(1);
                $('#modal_carico').modal('hide');
                carica_articolo();
            } else {
                $('#modal_inserimento').modal('show');
                $('#modal_inserimento_barcode').val(code);
            }
        });
    }

    function cerca_articolo_codice_2(fornitore, codice, lotto, qta, taglia, colore) {
        if (fornitore.length > 7) {
            const myArray = fornitore.split("','");
            codice = myArray[1];
            lotto = myArray[2];
            qta = myArray[3];
            taglia = myArray[4];
            colore = myArray[5];
        }
        $.ajax({
            url: "/ajax/cerca_articolo_codice_2/<?php echo $fornitore->Cd_CF ?>/" + codice + "/" + lotto + "/" + qta + "/" + taglia + "/" + colore,
            context: document.body
        }).done(function (result) {
            if (result != '') {
                $('#modal_carico').modal('show');
                $('#modal_quantita').val(1);
                $('#ajax_modal_carico').html(result);
            } else {
                $('#modal_inserimento').modal('show');
                $('#modal_inserimento_barcode').val(code);
            }
        });
    }

    if (window.innerWidth > 800) {
        $('#interactive').css('display', 'none');
    }
</script>
