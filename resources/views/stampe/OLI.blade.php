<?php $dorig = DB::SELECT('SELECT dorig.*,AR.Descrizione as Desc_AR,
                    VR.Qta as Qta_VR,
                    x_VRGruppo.Descrizione as ScalaNumerica,
                    A2.Descrizione as Pellame,
                    DOTes.NumeroDoc,
                    DORig.NoteRiga,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    LEFT JOIN AR ON DORIG.Cd_AR = AR.Cd_AR
                    LEFT JOIN DOTES ON DOTES.Id_DOTes = DORIG.Id_DOTes
                    Left Join x_ARAttributo A2 On A2.Ud_x_ARAttributo = AR.x_Ud_ARAttributo2
                    LEFT JOIN x_VRGruppo ON x_VRGruppo.Ud_x_VRGruppo = AR.x_Ud_VRGruppo1
                    WHERE DORIG.Id_DOTes = \'' . $id_dotes->Id_DoTes . '\'');


/*
$cliente = DB::SELECT('SELECT * FROM CF WHERE Cd_CF = \'' . $id_dotes->Cd_CF . '\'')[0];
$contatto = DB::SELECT('SELECT * FROM CFContatto WHERE Cd_CF = \'' . $id_dotes->Cd_CF . '\'')[0];

$date = date('d/m/Y', strtotime($id_dotes->DataDoc));
$pagamento = DB::SELECT('SELECT * FROM PG WHERE Cd_PG = \'' . $id_dotes->Cd_PG . '\'');
$dototali = DB::SELECT('SELECT * FROM DOTotali WHERE Id_DoTes = \'' . $id_dotes->Id_DoTes . '\'')[0];
$creazione = date('d/m/Y H:i:s', strtotime($id_dotes->TimeIns));
$porto = DB::SELECT('SELECT * FROM DOPorto where Cd_DOPorto =\'' . $id_dotes->Cd_DoPorto . '\'');
if (sizeof($porto) > 0)
    $porto = $porto[0]->Descrizione;
$trasporto = DB::SELECT('SELECT * FROM DOTrasporto where Cd_DOTrasporto =\'' . $id_dotes->Cd_DoTrasporto . '\'');
if (sizeof($trasporto) > 0)
    $trasporto = $trasporto[0]->Descrizione;
$data_trasporto = ($id_dotes->TrasportoDataora) ? $id_dotes->TrasportoDataora : '';
if ($data_trasporto != '')
    $data_trasporto = date('d-m-y', strtotime($data_trasporto));
$spedizione = DB::SELECT('SELECT * FROM DOSped where Cd_DOSped =\'' . $id_dotes->Cd_DoSped . '\'');
if (sizeof($spedizione) > 0)
    $spedizione = $spedizione[0]->Descrizione;
$aspetto_beni = DB::SELECT('SELECT * FROM DOAspBene where Cd_DOAspBene =\'' . $id_dotes->Cd_DoAspBene . '\'');
if (sizeof($aspetto_beni) > 0)
    $aspetto_beni = $aspetto_beni[0]->Descrizione;
//$banca = 'IT-62-C-C0200876312-000401045594 BANCA UNICREDIT';
$banca = DB::SELECT('SELECT * FROM Banca where Cd_CGConto = \'' . $id_dotes->Cd_CGConto_Banca . '\' ');
if (sizeof($banca) > 0) {
    $banca2 = $banca[0]->Iban;
    $banca2 .= ' - ';
    $banca2 .= $banca[0]->Descrizione;
    $banca = $banca2;
} else {
    $banca = '';
}*/

$html = '<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css" media="print">
    @page
        {
            size: auto;   /* auto is the initial value */
            margin: 0mm;  /* this affects the margin in the printer settings */
        }
        .container {
            position: relative;
            text-align: center;
        }
       body{
            margin: 0px;
            padding: 0;
            width: 14.8cm;
            height: 10.5cm;
       }
       label{
            font-family: Tahoma;
       }
    </style>
</head>
<body>

<div class="container">
    <img src="';
$html .= URL::asset('img/OLI.png');
$html .= '" alt="OLI" style="width:99%;z-index:1;filter: grayscale(99%);">';
$html .= '
    <div style="text-align:left;position: absolute;top: 30px;left: 20px;z-index:10;font-size:16px;font-weight:bolder;">' . $dorig[0]->Cd_AR . '</div>
    <div style="text-align:left;position: absolute;top: 45px;left: 20px;z-index:10;font-size:16px;font-weight:bolder;">' . $dorig[0]->Desc_AR . '</div>';
$html .= '
    <div style="text-align:left;position: absolute;top: 70px; left: 125px;z-index:10;font-size:16px;font-weight:bolder;">' . $dorig[0]->Pellame . '</div>
    <div style="text-align:left;position: absolute;top: 100px;left: 125px;z-index:10;font-size:16px;font-weight:bolder;">' . $dorig[0]->Colore . '</div>';
$html .= '
    <div style="text-align:left;position: absolute;top: 173px;left: 420px;z-index:10;font-size:12px">' . date('d/m/Y', strtotime($dorig[0]->DataDoc)) . '</div>
    <div style="text-align:left;position: absolute;top: 188px;left: 420px;z-index:10;font-size:12px">' . $dorig[0]->ScalaNumerica . '</div>
    <div style="text-align:left;position: absolute;top: 203px;left: 420px;z-index:10;font-size:12px"> </div>';
/*foreach ($dorig as $d) {
    if ($d->Taglia != '' && $d->Colore) {
        $html .= '<label>' . substr($d->Descrizione, 0, 20) . '</label>';
        $html .= '<label> Taglia : ' . $d->Taglia . ' - Colore : ' . $d->Colore . '</label><br>';
    } else {
        $html .= '<label>' . substr($d->Descrizione, 0, 20) . '</label><br>';
    }
}
$html .= '
    </div>';*/

$html .= '<div style="text-align:center;position: absolute;top: 261px;left:450px;z-index:10;font-size:12px">
        <label>' . number_format($dorig[0]->Qta, 0, ',', '.') . '</label><br></div>';
foreach ($dorig as $d) {
    if ($d->Taglia != '' && $d->Colore) {
        $top = 320;
        $left = 100;
        switch ($d->Taglia) {
            case '38':
                $left = 130;
                break;
            case '40':
                $left = 150;
                break;
            case '42':
                $left = 170;
                break;
            case '44':
                $left = 192;
                break;
            case '46':
                $left = 213;
                break;
            case '48':
                $left = 240;
                break;
            case '50':
                $left = 260;
                break;
            case '52':
                $left = 280;
                break;
            case '54':
                $left = 300;
                break;
            case '56':
                $left = 320;
                break;
            case '58':
                $left = 340;
                break;
            case '60s':
                $left = 360;
                break;
            case '62':
                $left = 380;
                break;
            case '64':
                $left = 400;
                break;
        }

        $html .= '<div style="text-align:center;position: absolute;top: ' . $top . 'px;left: ' . $left . 'px;z-index:10;font-size:12px">
        <label>' . number_format($d->Qta_VR, 0, ',', '.') . '</label><br></div>';
    }

}
$html .= '
    </div>
    <br>
</div>

</body>';

$html .= '
</html >
<script type = "text/javascript" >
        window.print();
</script > ';

echo $html;
exit();

?>
