<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use http\Env\Response;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Else_;
use Spatie\GoogleCalendar\Event;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use NGT\Barcode\GS1Decoder\Decoder;
use Symfony\Component\VarDumper\Cloner\Data;


/**
 * Controller principale del webticket
 * Class HomeController
 * @package App\Http\Controllers
 */
class AjaxController extends Controller
{

    public function check_next_doc($id_dotes)
    {
        $dorig = DB::SELECT('SELECT dorig.*
                    FROM DORig
                    WHERE DORIG.Id_DOTes = \'' . $id_dotes . '\'');
        $id_dorig = '';
        foreach ($dorig as $d) {
            $id_dorig = $id_dorig . $d->Id_DORig . ',';
        }

        $id_dorig = $id_dorig . '0';

        $dotes = DB::SELECT('SELECT CF.Id_CF,D.Id_DoTes From Dorig D left join CF on CF.Cd_CF = D.Cd_CF where D.Id_Dorig_Evade in (' . $id_dorig . ')');
        if (sizeof($dotes) > 0) {
            return '/' . $dotes[0]->Id_CF . '/' . $dotes[0]->Id_DoTes;
        } else {
            return 'NODOC';
        }
    }

    public function modifica($id_dorig)
    {
        $check = explode('_', $id_dorig);
        $id_dorig = $check[0];
        $return = '';
        $dorig = DB::SELECT('SELECT dorig.*,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    WHERE DORIG.Id_DORig = \'' . $id_dorig . '\'');
        if (sizeof($dorig) > 0) {
            $documento = DB::select('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $dorig[0]->Id_DOTes . '\'');
            foreach ($documento as $d) {
                if (($d->Cd_AR == $dorig[0]->Cd_AR) && ($d->Colore == $dorig[0]->Colore)) {
                    $return .= '
                        <div class="row" style="gap: 1%">
                            <div class="col-6 col-md-6">
                                <input class="form-control" type="text" name="' . $d->Colore . '_' . $d->Taglia . '_' . $d->Id_DORig . '" id="' . $d->Colore . '_' . $d->Taglia . '_' . $d->Id_DORig . '" value="' . $d->Colore . ' - ' . $d->Taglia . '" readonly>
                            </div>
                            <div class="col-3 col-md-3">
                                <input class="form-control" type="number" name="' . $d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig . '" id="' . $d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig . '" step="1" min="1" max="9999" value="' . number_format($d->Qta, 0) . '">
                            </div>
                            <input type="hidden" name="' . $d->Id_DORig . '" id="' . $d->Id_DORig . '" value="' . $d->Id_DORig . '">
                        </div>
                    ';
                }
            }
            return $return;
        }
    }

    public function stampe($id_dotes)
    {
        DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes exec asp_DO_End $id_dotes");
        DB::statement("exec asp_DO_End $id_dotes");
        $id_dotes = DB::SELECT('SELECT * FROM DOTes WHERE Id_DOTes = \'' . $id_dotes . '\'')[0];
        $check_stampa = DB::SELECT('SELECT * FROM DOReport WHERE Cd_DO = \'' . $id_dotes->Cd_Do . '\' and Ud_ReportDoc = \'987B2928-422F-4CF0-8D5A-BB97A6EF7166\' ');
        if (sizeof($check_stampa) > 0) {
            $html = View::make('stampe.tsi', compact('id_dotes'));
        }
        $check_stampa_2 = DB::SELECT('SELECT * FROM DOReport WHERE Cd_DO = \'' . $id_dotes->Cd_Do . '\' and Ud_ReportDoc = \'4641D5C8-4CAC-4DEB-8597-EC93AEF824EB\' ');
        if (sizeof($check_stampa_2) > 0) {
            $html = View::make('stampe.oli', compact('id_dotes'));
        }
        if (sizeof($check_stampa) <= 0 && sizeof($check_stampa_2) <= 0) {
            $html = View::make('stampe.generico', compact('id_dotes'));
        }
        /*$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
        $mpdf->curlAllowUnsafeSslRequests = true;
        $mpdf->SetTitle($id_dotes->Cd_Do);
        $mpdf->WriteHTML($html);
        $mpdf->Output($id_dotes->Cd_Do . rand(0, 1000) . '.pdf', 'I');*/
        return $html;
    }

    public function cerca_documento($id_dotes)
    {
        $cerca = DB::SELECT('SELECT * FROM dotes where id_dotes = ' . $id_dotes);
        if (sizeof($cerca) > 0) {
            $id_cd_cf = DB::SELECT('SELECT * from cf where cd_cf = \'' . $cerca[0]->Cd_CF . '\'');
            if (sizeof($id_cd_cf) > 0) {
                ?>
                <li class="list-group-item">
                    <a href="/magazzino/carico4/<?php echo $id_cd_cf[0]->Id_CF; ?>/<?php echo $id_dotes; ?>"
                       class="media">
                        <div class="media-body">
                            <h5>Documento: <?php echo $cerca[0]->Cd_Do ?> N° <?php echo $cerca[0]->NumeroDoc ?></h5>
                        </div>
                    </a>
                </li>
                <?php
            }
        }
    }

    public function id_dotes($id_dotes)
    {
        DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes exec asp_DO_End $id_dotes");
        DB::statement("exec asp_DO_End $id_dotes");
    }

    public function cerca_articolo($q)
    {

        $articoli = DB::select('SELECT [Id_AR],[Cd_AR],[Descrizione] FROM AR where(Cd_AR Like \'' . $q . '%\' or  Descrizione Like \'%' . $q . '%\' or CD_AR IN (SELECT CD_AR from ARAlias where Alias LIKE \'%' . $q . '%\'))  Order By Id_AR DESC');
        if (sizeof($articoli) == '0') {
            try {

                $decoder = new Decoder($delimiter = '');
                $barcode = $decoder->decode($q);
                $where = ' where 1=1 ';

                foreach ($barcode->toArray()['identifiers'] as $field) {

                    if ($field['code'] == '01') {
                        $testo = trim($field['content'], '*,');
                        $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';
                    }

                }
                $articoli = DB::select('SELECT [Id_AR],[Cd_AR],[Descrizione] FROM AR ' . $where . '  Order By Id_AR DESC');
            } catch (\Exception $e) {
                return;
            }
        }
        if (sizeof($articoli) != '0')
            foreach ($articoli as $articolo) { ?>

                <li class="list-group-item">
                    <a href="/modifica_articolo/<?php echo $articolo->Id_AR ?>" class="media">
                        <div class="media-body">
                            <h5><?php echo $articolo->Descrizione ?></h5>
                            <p>Codice: <?php echo $articolo->Cd_AR ?></p>
                        </div>
                    </a>
                </li>

            <?php }


    }

    public
    function cerca_articolo_trasporto($q)
    {

        $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_AR where (AR.Cd_AR Like \'' . $q . '%\' or  AR.Descrizione Like \'%' . $q . '%\' or AR.CD_AR IN (SELECT CD_AR from ARAlias where Alias LIKE \'%' . $q . '%\'))  Order By AR.Id_AR DESC');
        if (sizeof($articoli) == '0') {
            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($q);
            $where = ' where 1=1 ';

            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $testo = trim($field['content'], '*,');
                    $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';
                }
                if ($field['code'] == '10') {
                    $where .= ' and ARLotto.Cd_ARLotto Like \'%' . $field['content'] . '%\'';
                }

            }
            $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto on AR.Cd_AR = ARLotto.Cd_AR ' . $where . '  Order By Id_AR DESC');
        }
        foreach ($articoli as $articolo) {
            ?>

            <li class="list-group-item">
                <a onclick="cambio_articolo(<?php echo $articolo->Cd_AR . ',' ?><?php if ($articolo->Cd_ARLotto != '') echo $articolo->Cd_ARLotto; else echo '0'; ?>)"
                   class="media">
                    <div class="media-body">
                        <h5><?php echo $articolo->Descrizione;
                            if ($articolo->Cd_ARLotto != '') echo '  Lotto: ' . $articolo->Cd_ARLotto ?></h5>
                        <p>Codice: <?php echo $articolo->Cd_AR ?></p>
                    </div>
                </a>
            </li>

        <?php }
    }

    public
    function visualizza_lotti($articolo)
    {

        $giacenza = DB::SELECT('SELECT SUM(QuantitaSign) as Giacenza  FROM MGMov where Cd_AR =\'' . $articolo . '\' and  Cd_MGEsercizio = YEAR(GETDATE()) and Cd_MG = \'00001\' ');
        foreach ($giacenza as $l) {
            ?>
            <li class="list-group-item">
                <a class="media" onclick="">
                    <div class="media-body">
                        <h3>Giacenza: <?php echo $l->Giacenza;
                            if ($l->Giacenza == '') echo '0';/*echo $l->Cd_AR.' - '.$l->Descrizione */ ?></h3>
                        <small><?php /*echo $l->Giacenza; if($l->Giacenza=='') echo '0';*/ ?></small>
                        <small><?php /*if($l->xCd_xPallet!='')echo 'Pallet : '.$l->xCd_xPallet ?></small>
                        <small><?php if($l->xNr_PalletFornitore!='')echo 'NrPalletFornitore : '.$l->xNr_PalletFornitore*/ ?></small>
                    </div>
                </a>
            </li>
        <?php }
    }

    /*
        public function storialotto($articolo,$lotto){
            $lotto1 = DB::SELECT('SELECT * FROM MGMov WHERE Cd_AR = \''.$articolo.'\' AND Cd_MGEsercizio = YEAR(GETDATE()) AND Cd_ARLotto = \''.$lotto.'\' ORDER BY DataMov ASC , PartenzaArrivo Desc');
            $giacenza =DB::SELECT('SELECT SUM(QuantitaSign) as Giacenza,Cd_AR,Cd_MG,Cd_ARLotto FROM MGMov WHERE Cd_AR = \''.$articolo.'\' AND Cd_ARLotto = \''.$lotto.'\' GROUP BY Cd_AR,Cd_ARLotto,Cd_MG HAVING SUM(QuantitaSign)>0');
            foreach ($lotto1 as $l){?>
                <li class="list-group-item">
                    <a class="media">
                        <div class="media-body">
                            <h5><?php echo $l->Cd_ARLotto ?></h5>
                            <p>Azione : <?php
                                if($l->Ini=='1') echo 'Iniziale';
                                if($l->Ret=='1') echo 'Rettifica';
                                if($l->Car=='1') echo 'Carico';
                                if($l->Sca=='1') echo 'Scarico';?></p>
                            <small>Magazzino : <?php echo  $l->Cd_MG ?></small>
                            <small>Quantita' : <?php echo floatval($l->QuantitaSign) ?></small>
                        </div>
                    </a>
                </li>
            <?php } ?>
            <li class="list-group-item">
                    <a class="media">
                        <div class="media-body">
                            <h5><?php echo $giacenza[0]->Cd_ARLotto ?></h5>
                            <p>Azione : <?php echo 'Giacenza'?></p>
                            <small>Magazzino : <?php echo  $giacenza[0]->Cd_MG ?></small>
                            <small>Quantita' : <?php echo floatval($giacenza[0]->Giacenza) ?></small>
                        </div>
                    </a>
                </li>
       <?php }

        public function inserisci_lotto($lotto,$articolo,$fornitore,$descrizione,$fornitore_pallet,$pallet){
            $esiste = DB::SELECT('SELECT * FROM ARLotto WHERE Cd_AR = \''.$articolo.'\' and Cd_ARLotto = \''.$lotto.'\' ');
            if(sizeof($esiste)>0){
                echo 'Impossibile creare il lotto in quanto gi?? esistente';
            }else {
                if($fornitore!='0') {
                    $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Fornitore = 1 and (Cd_CF Like \'%' . $fornitore . '%\' or  Descrizione Like \'%' . $fornitore . '%\')  Order By Id_CF DESC');
                    if ($fornitori == null) {
                        echo 'Fornitore non trovato';
                        exit();
                    } else
                        $fornitori = $fornitori[0]->Cd_CF;
                }
                    $id_Lotto = DB::table('ARLotto')->insertGetId(['Cd_AR' => $articolo, 'Cd_ARLotto' => $lotto, 'Descrizione' => $descrizione]);
                if($fornitore!='0'){
                            DB::update("UPDATE ARLotto Set Cd_CF = '$fornitori' where Id_ARLotto = '$id_Lotto' ");
                }
                if($fornitore_pallet!='0'){
                    DB::update("UPDATE ARLotto Set xNr_PalletFornitore = '$fornitore_pallet' where Id_ARLotto = '$id_Lotto' ");
                }
                if($pallet!='0'){
                    DB::update("UPDATE ARLotto Set xCd_xPallet = '$pallet' where Id_ARLotto = '$id_Lotto' ");
                }
                echo 'Lotto Inserito Correttamente';
            }
        }
    */

    public
    function segnalazione_salva($id_dotes, $id_dorig, $testo)
    {
        $testo = str_replace('*', '', $testo);
        $esiste = DB::SELECT('SELECT * FROM DoTes WHERE Id_DoTes = \'' . $id_dotes . '\' ')[0]->NotePiede;
        if ($esiste != null) {
            $esiste .= '                                    ';
            $esiste .= $testo;
            DB::update('Update DoTes set NotePiede = \'' . $esiste . '\' where Id_DoTes = \'' . $id_dotes . '\' ');
        } else
            DB::update('Update DOTes set NotePiede = \'' . $testo . '\' where Id_DoTes = \'' . $id_dotes . '\' ');
    }

    public
    function segnalazione($id_dotes, $id_dorig, $testo)
    {

        if (substr($testo, 0, 2) == '01') {
            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($testo);
            $where = 'Articolo ';
            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $contenuto = trim($field['content'], '*,');
                    $where .= $contenuto . ' con lotto ';

                }
                if ($field['code'] == '10') {
                    $where .= $field['content'] . ' non trovato.';

                }
                /*
                if ($field['code'] == '310') {
                    $decimali = floatval(substr($field['raw_content'],-2));
                    $qta = floatval(substr($field['raw_content'],0,4))+$decimali/100;
                    $where .= ' and Qta Like \'%' . $qta . '%\'';
                }*/

            }
        } else {
            $testo = trim($testo, '-');
            $where = $testo;
        }
        $esiste = DB::SELECT('SELECT * FROM DoTes WHERE Id_DoTes = \'' . $id_dotes . '\' ')[0]->NotePiede;
        if ($esiste != null) {
            $esiste .= '                                    ';
            $esiste .= $where;
            DB::update('Update DoTes set NotePiede = \'' . $esiste . '\' where Id_DoTes = \'' . $id_dotes . '\' ');
        } else
            DB::update('Update DOTes set NotePiede = \'' . $where . '\' where Id_DoTes = \'' . $id_dotes . '\' ');

    }

    public
    function cerca_articolo_new($q, $dest, $forn)
    {

        $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_AR where (AR.Cd_AR Like \'' . $q . '%\' or  AR.Descrizione Like \'%' . $q . '%\' or AR.CD_AR IN (SELECT CD_AR from ARAlias where Alias LIKE \'%' . $q . '%\'))  Order By AR.Id_AR DESC');
        if (sizeof($articoli) == '0') {
            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($q);
            $where = ' where 1=1 ';

            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $testo = trim($field['content'], '*,');
                    $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';
                }
                if ($field['code'] == '10') {
                    $where .= ' and ARLotto.Cd_ARLotto Like \'%' . $field['content'] . '%\'';
                }

            }
            $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto on AR.Cd_AR = ARLotto.Cd_AR ' . $where . '  Order By Id_AR DESC');
        }
        foreach ($articoli as $a) { ?>

            <li class="list-group-item">
                <a href="/magazzino/trasporto2/<?php echo $a->Cd_AR ?>/BCV/<?php echo $forn ?>/<?php echo $dest ?>/<?php if ($a->Cd_ARLotto != '') echo $a->Cd_ARLotto; else echo '0'; ?>"
                   class="media">
                    <div class="media-body">
                        <h5><?php echo $a->Descrizione;
                            if ($a->Cd_ARLotto != '') echo '  Lotto: ' . $a->Cd_ARLotto ?></h5>
                        <p>Codice: <?php echo $a->Cd_AR; ?></p>
                    </div>
                </a>
            </li>
        <?php }

    }


    public
    function cerca_fornitore($q = '')
    {

        if ($q == '') {
            $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Fornitore = 1 Order By Id_CF DESC');
        } else {
            $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Fornitore = 1 and (Cd_CF Like \'%' . $q . '%\' or  Descrizione Like \'%' . $q . '%\')  Order By Id_CF DESC');
        }

        foreach ($fornitori as $f) { ?>

            <li class="list-group-item">
                <a href="/magazzino/carico3/<?php echo $f->Id_CF ?>/ROF" class="media">
                    <div class="media-body">
                        <h5><?php echo $f->Descrizione ?></h5>
                        <p>Codice: <?php echo $f->Cd_CF ?></p>

                    </div>
                </a>
            </li>

        <?php }
    }

    public
    function cerca_fornitore_new($q = '', $dest)
    {

        $dest1 = DB::SELECT('SELECT * FROM DO WHERE Cd_DO = \'' . $dest . '\' ')[0]->CliFor;

        if ($dest1 == ('F')) {
            if ($q == '') {
                $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Fornitore = 1 Order By Id_CF DESC');
            } else {
                $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Fornitore = 1 and (Cd_CF Like \'%' . $q . '%\' or  Descrizione Like \'%' . $q . '%\')  Order By Id_CF DESC');
            }
        }
        if ($dest1 == ('C')) {
            if ($q == '') {
                $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 Order By Id_CF DESC');
            } else {
                $fornitori = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 and (Cd_CF Like \'%' . $q . '%\' or  Descrizione Like \'%' . $q . '%\')  Order By Id_CF DESC');
            }
        }
        if ($dest == 'BCV') {
            foreach ($fornitori as $f) { ?>

                <li class="list-group-item">
                    <a href="/magazzino/trasporto_documento/BCV/<?php echo $f->Cd_CF ?>" class="media">
                        <div class="media-body">
                            <h5><?php echo $f->Descrizione ?></h5>
                            <p>Codice: <?php echo $f->Cd_CF ?></p>

                        </div>
                    </a>
                </li>

            <?php }
        } else {
            foreach ($fornitori as $f) { ?>

                <li class="list-group-item">
                    <a href="/magazzino/carico03/<?php echo $f->Id_CF ?>/<?php echo $dest ?>" class="media">
                        <div class="media-body">
                            <h5><?php echo $f->Descrizione ?></h5>
                            <p>Codice: <?php echo $f->Cd_CF ?></p>

                        </div>
                    </a>
                </li>

            <?php }
        }
    }


    public
    function cerca_cliente($q = '')
    {


        if ($q == '') {
            $clienti = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 Order By Id_CF DESC');
        } else {
            $clienti = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 and (Cd_CF Like \'%' . $q . '%\' or  Descrizione Like \'%' . $q . '%\')  Order By Id_CF DESC');
        }
        foreach ($clienti as $c) { ?>

            <li class="list-group-item">
                <a href="/magazzino/scarico3/<?php echo $c->Id_CF ?>/PRV" class="media">
                    <div class="media-body">
                        <h5><?php echo $c->Descrizione ?></h5>
                        <p>Codice: <?php echo $c->Cd_CF ?></p>
                    </div>
                </a>
            </li>

        <?php }
    }

    public
    function cerca_cliente_new($q = '', $dest)
    {


        if ($q == '') {
            $clienti = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 Order By Id_CF DESC');
        } else {
            $clienti = DB::select('SELECT [Id_CF],[Cd_CF],[Descrizione] FROM CF where Cliente = 1 and (Cd_CF Like \'%' . $q . '%\' or  Descrizione Like \'%' . $q . '%\')  Order By Id_CF DESC');
        }
        if ($dest == 'S2') {
            foreach ($clienti as $f) { ?>

                <li class="list-group-item">
                    <a href="/magazzino/scarico3/<?php echo $f->Id_CF ?>/OVC" class="media">
                        <div class="media-body">
                            <h5><?php echo $f->Descrizione ?></h5>
                            <p>Codice: <?php echo $f->Cd_CF ?></p>

                        </div>
                    </a>
                </li>

            <?php }
        }
        if ($dest == 'S02') {
            foreach ($clienti as $f) { ?>

                <li class="list-group-item">
                    <a href="/magazzino/scarico03/<?php echo $f->Id_CF ?>/DDT" class="media">
                        <div class="media-body">
                            <h5><?php echo $f->Descrizione ?></h5>
                            <p>Codice: <?php echo $f->Cd_CF ?></p>

                        </div>
                    </a>
                </li>

            <?php }
        }
    }

    public
    function cerca_articolo_barcode($cd_cf, $barcode)
    {

        $articoli = DB::select('
            SELECT AR.Id_AR,AR.Cd_AR,AR.Descrizione,ARARMisura.UMFatt,DORig.PrezzoUnitarioV,LSArticolo.Prezzo from AR
            JOIN ARAlias ON AR.Cd_AR = ARAlias.Cd_AR and ARAlias.Alias = \'' . $barcode . '\'
            LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
            LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
            LEFT JOIN LSRevisione ON LSRevisione.Id_LSRevisione = LSArticolo.Id_LSRevisione and LSRevisione.Cd_LS = \'LSF\'
            LEFT JOIN DORig ON DOrig.Cd_CF = \'' . $cd_cf . '\' and DORig.Cd_AR = AR.Cd_AR
            order by DORig.DataDoc ASC');

        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            echo '<h3>Barcode: ' . $barcode . '<br>
                      Pezzi x Collo: ' . intval($articolo->UMFatt) . '<br><br>
                      Descrizione:<br>' . $articolo->Descrizione . '</h3>';
            ?>


            $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
            $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
            $('#modal_quantita').val(<?php echo intval($articolo->UMFatt) ?>);
            </script>
            <?php
        }
        if (sizeof($articoli) < 1) {
            $articoli = DB::select('
                SELECT AR.Id_AR,AR.Cd_AR,AR.Descrizione,ARARMisura.UMFatt,DORig.PrezzoUnitarioV,LSArticolo.Prezzo from AR
                LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
                LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
                LEFT JOIN LSRevisione ON LSRevisione.Id_LSRevisione = LSArticolo.Id_LSRevisione and LSRevisione.Cd_LS = \'LSF\'
                LEFT JOIN DORig ON DOrig.Cd_CF LIKE \'' . $cd_cf . '\' and DORig.Cd_AR = AR.Cd_AR
                where AR.CD_AR LIKE \'' . $barcode . '\'
                order by DORig.DataDoc DESC');

            if (sizeof($articoli) > 0) {
                $articolo = $articoli[0];
                echo '<h3>Barcode : Non inserito <br>
                          Codice: ' . $articolo->Cd_AR . '<br>
                          Pezzi x Collo: ' . intval($articolo->UMFatt) . '<br><br>
                          Descrizione:<br>' . $articolo->Descrizione . '</h3>';
                ?>
                <script type="text/javascript">

                    $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                    <?php if($articolo->PrezzoUnitarioV){ ?>
                    $('#modal_prezzo').val('<?php echo number_format($articolo->PrezzoUnitarioV, 2, '.', '') ?>');
                    $('#modal_quantita').val(<?php echo intval($articolo->UMFatt) ?>);
                    <?php } else { ?>
                    $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
                    $('#modal_quantita').val(<?php echo intval($articolo->UMFatt) ?>);
                    <?php } ?>
                </script>
                <?php
            }
        }
    }

    public
    function cerca_articolo_codice($cd_cf, $codice, $Cd_ARLotto, $qta)
    {
        $codice = str_replace("slash", "/", $codice);


        $articoli = DB::select('SELECT AR.Id_AR,AR.Cd_AR,AR.Descrizione,ARAlias.Alias as barcode,ARARMisura.UMFatt,DORig.PrezzoUnitarioV,LSArticolo.Prezzo from AR
            LEFT JOIN ARAlias ON AR.Cd_AR = ARAlias.Cd_AR
            LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
            LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
            LEFT JOIN DORig ON DOrig.Cd_CF LIKE \'' . $cd_cf . '\' and DORig.Cd_AR = AR.Cd_AR
            where AR.CD_AR LIKE \'' . $codice . '\' or ARAlias.Alias Like \'' . $codice . '\'
            order by DORig.DataDoc DESC');

        $magazzino_selected = DB::select('SELECT MgMov.Cd_MG, Mg.Descrizione from MGMov LEFT JOIN MG ON MG.Cd_MG = MgMov.Cd_MG WHERE MgMov.Cd_ARLotto = \'' . $Cd_ARLotto . '\'  and MgMov.Cd_AR = \'' . $codice . '\' and MgMov.Cd_MGEsercizio = YEAR(GETDATE()) ');

        if ($magazzino_selected != null) {
            $magazzino_selected = $magazzino_selected[0];
            $magazzino_selezionato = $magazzino_selected->Cd_MG;
        } else
            $magazzino_selezionato = '0';

        $magazzini = DB::select('SELECT * from MG WHERE Cd_MG !=\'' . $magazzino_selezionato . '\' ');

        //TODO Controllare Data Scadenza togliere i commenti

        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            $taglie = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1 ) as Taglia, INFOAR.Ud_VR1 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1 ');
            echo '<h3>    Barcode: ' . $articolo->barcode . '<br>
                          Codice: ' . $articolo->Cd_AR . '<br>
                          Descrizione:<br>' . $articolo->Descrizione . '</h3>';
            ?>
            <script type="text/javascript">

                <?php if($articolo->PrezzoUnitarioV){ ?>
                $('#modal_prezzo').val('<?php echo number_format($articolo->PrezzoUnitarioV, 2, '.', '') ?>');
                <?php } else { ?>
                $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
                <?php } ?>
                <?php if(sizeof($taglie) > 0) foreach($taglie as $t){?>
                $('#modal_taglie').append('<option taglia="<?php echo $t->Taglia?>"><?php echo $t->Taglia ?></option>')
                <?php   $colore = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2 ) as Colore FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Ud_VR1 = \'' . $t->Ud_VR1 . '\' and INFOAR.Obsoleto = \'false\' ');
                foreach ($colore as $c){?>
                $('#modal_colori').append('<option id="modal_colori_<?php echo $t->Taglia?>" style="display:none"><?php echo $c->Colore ?></option>')
                <?php } ?>
                <?php } ?>

                $('#modal_magazzino_P').html
                <?php  if($magazzino_selezionato != '0'){ ?>
                ('<option><?php echo $magazzino_selected->Cd_MG . ' - ' . $magazzino_selected->Descrizione?></option>')
                <?php } ?>
                <?php foreach($magazzini as $m){?>
                $('#modal_magazzino_P').append('<option><?php echo $m->Cd_MG . ' - ' . $m->Descrizione ?></option>')
                <?php } ?>
                $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');


                cambioTaglia();
            </script>
            <?php
        }

        if (sizeof($articoli) < 1) {
            $articoli = DB::select('
                SELECT AR.Id_AR,AR.Cd_AR,AR.Descrizione,ARARMisura.UMFatt,DORig.PrezzoUnitarioV,LSArticolo.Prezzo from AR
                LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
                LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
                LEFT JOIN LSRevisione ON LSRevisione.Id_LSRevisione = LSArticolo.Id_LSRevisione and LSRevisione.Cd_LS = \'LSF\'
                LEFT JOIN DORig ON DOrig.Cd_CF LIKE \'' . $cd_cf . '\' and DORig.Cd_AR = AR.Cd_AR
                where AR.CD_AR LIKE \'' . $codice . '\'
                order by DORig.DataDoc DESC');
            if ($Cd_ARLotto != '')
                $lotto = DB::select('SELECT * FROM ARLotto WHERE Cd_AR = \'' . $codice . '\' and Cd_ARLotto !=\'' . $Cd_ARLotto . '\' and  Cd_ARLotto in (select Cd_ARLotto from MGMov group by Cd_ARLotto having SUM(QuantitaSign) > 0)  ');
            else
                $lotto = DB::select('SELECT * FROM ARLotto WHERE Cd_AR = \'' . $codice . '\' and Cd_AR in (select Cd_AR from MGMov group by Cd_AR having SUM(QuantitaSign) > 0) ');
            if (sizeof($articoli) > 0) {
                $articolo = $articoli[0];
                echo '<h3>Barcode : Non inserito <br>
                          Codice: ' . $articolo->Cd_AR . '<br>
                          Pezzi x Collo: ' . intval($articolo->UMFatt) . '<br><br>
                          Descrizione:<br>' . $articolo->Descrizione . '</h3>';
                ?>
                <script type="text/javascript">

                    $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                    <?php if($articolo->PrezzoUnitarioV){ ?>
                    $('#modal_prezzo').val('<?php echo number_format($articolo->PrezzoUnitarioV, 2, '.', '') ?>');
                    <?php } else { ?>
                    $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
                    <?php }?>
                    $('#modal_lotto').html
                    <?php if($Cd_ARLotto != '0'){ ?>
                    ('<option><?php echo $Cd_ARLotto ?></option>');
                    <?php } ?>
                    $('#modal_lotto').append('<option>Nessun Lotto</option>');
                    <?php foreach($lotto as $l){?>
                    $('#modal_lotto').append('<option><?php echo $l->Cd_ARLotto ?></option>')
                    <?php } ?>


                </script>
                <?php
            }
        }
    }


    public
    function cerca_articolo_codice_2($cd_cf, $codice, $Cd_ARLotto, $qta, $taglia, $colore)
    {

        $codice = str_replace("slash", "/", $codice);


        $articoli = DB::select('SELECT AR.Id_AR,AR.Cd_AR,AR.Descrizione,ARAlias.Alias as barcode,ARARMisura.UMFatt,DORig.PrezzoUnitarioV,LSArticolo.Prezzo from AR
            LEFT JOIN ARAlias ON AR.Cd_AR = ARAlias.Cd_AR
            LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
            LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
            LEFT JOIN DORig ON DOrig.Cd_CF LIKE \'' . $cd_cf . '\' and DORig.Cd_AR = AR.Cd_AR
            where AR.CD_AR LIKE \'' . $codice . '\' or ARAlias.Alias Like \'' . $codice . '\'
            order by DORig.DataDoc DESC');

        $magazzino_selected = DB::select('SELECT MgMov.Cd_MG, Mg.Descrizione from MGMov LEFT JOIN MG ON MG.Cd_MG = MgMov.Cd_MG WHERE MgMov.Cd_ARLotto = \'' . $Cd_ARLotto . '\'  and MgMov.Cd_AR = \'' . $codice . '\' and MgMov.Cd_MGEsercizio = YEAR(GETDATE()) ');

        if ($magazzino_selected != null) {
            $magazzino_selected = $magazzino_selected[0];
            $magazzino_selezionato = $magazzino_selected->Cd_MG;
        } else
            $magazzino_selezionato = '0';

        $magazzini = DB::select('SELECT * from MG WHERE Cd_MG !=\'' . $magazzino_selezionato . '\' ');

        //TODO Controllare Data Scadenza togliere i commenti

        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];

            $old_taglia = $taglia;

            $old_colore = $colore;

            if ($taglia != 'ND')
                $taglia = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = \'' . $taglia . '\' ) as Taglia, INFOAR.Ud_VR1 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Ud_VR1 = \'' . $taglia . '\' and Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1 ');
            else
                $taglia = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) as Taglia, INFOAR.Ud_VR1 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1 ');
            if ($colore != 'ND')
                $colore = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = \'' . $old_taglia . '\' ) as Taglia, (Select Descrizione from x_VR WHERE Ud_x_VR = \'' . $colore . '\' ) as Colore, INFOAR.Ud_VR2 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Ud_VR1 = \'' . $old_taglia . '\' and Ud_VR2 = \'' . $colore . '\' and Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1,INFOAR.Ud_VR2 ');
            else
                $colore = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) as Taglia,(Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2) as Colore, INFOAR.Ud_VR2 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1,INFOAR.Ud_VR2 ');
            echo '<h3>    Barcode: ' . $articolo->barcode . '<br>
                          Codice: ' . $articolo->Cd_AR . '<br>
                          Descrizione:<br>' . $articolo->Descrizione . '</h3>';
            ?>
            <script type="text/javascript">

                <?php if($articolo->PrezzoUnitarioV){ ?>
                $('#modal_prezzo').val('<?php echo number_format($articolo->PrezzoUnitarioV, 2, '.', '') ?>');
                <?php } else { ?>
                $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
                <?php } ?>
                $('#modal_magazzino_P').html
                <?php  if($magazzino_selezionato != '0'){ ?>
                ('<option><?php echo $magazzino_selected->Cd_MG . ' - ' . $magazzino_selected->Descrizione?></option>')
                <?php } ?>
                <?php foreach($magazzini as $m){?>
                $('#modal_magazzino_P').append('<option><?php echo $m->Cd_MG . ' - ' . $m->Descrizione ?></option>')
                <?php } ?>
                $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                <?php foreach($taglia as $t){?>
                option = document.createElement('option');
                option.setAttribute('taglia', '<?php echo $t->Taglia ?>')
                option.value = '<?php echo $t->Taglia ?>';
                option.innerHTML = '<?php echo $t->Taglia ?>';
                <?php if(strtoupper(str_replace(' ', '', $t->Ud_VR1)) == strtoupper(str_replace(' ', '', $old_taglia))) { ?>
                option.selected = true;
                document.getElementById('modal_taglie').innerHTML = option.outerHTML;
                <?php }else{?>
                document.getElementById('modal_taglie').appendChild(option);
                <?php } ?>
                <?php } ?>
                <?php foreach($colore as $c){ ?>
                option = document.createElement('option');
                option.id = 'modal_colori_<?php echo $c->Taglia ?>';
                option.style.display = 'none';
                option.value = '<?php echo $c->Colore ?>';
                option.innerHTML = '<?php echo $c->Colore ?>';
                <?php if(strtoupper(str_replace(' ', '', $c->Ud_VR2)) == strtoupper(str_replace(' ', '', $old_colore))) { ?>
                option.selected = true;
                document.getElementById('modal_colori').innerHTML = option.outerHTML;
                carica_articolo();
                <?php }else{?>
                document.getElementById('modal_colori').appendChild(option);
                <?php } ?>
                <?php } ?>

                //cambioTaglia();
            </script>
            <?php
        }
    }

    public function cerca_articolo_prezzo($codice)
    {
        // Sanitizzazione del codice
        $codice = str_replace("slash", "/", $codice);

        $articoli = DB::select(
            "SELECT AR.Id_AR, AR.Cd_AR, AR.Descrizione, ARAlias.Alias AS barcode,
                ARARMisura.UMFatt, LSArticolo.Prezzo
         FROM AR
         LEFT JOIN ARAlias ON AR.Cd_AR = ARAlias.Cd_AR
         LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
         LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
         WHERE AR.CD_AR LIKE ? OR ARAlias.Alias LIKE ?",
            [$codice, $codice]
        );
        if (sizeof($articoli) <= 0) {
            $articoli = DB::SELECT('SELECT AR.* FROM x_ARVRAlias LEFT JOIN AR ON AR.Cd_AR = x_ARVRAlias.Cd_AR WHERE Alias = \'' . $codice . '\' ');
        }

        if (!empty($articoli)) {
            $articolo = $articoli[0];

            $taglia = DB::select(
                "SELECT x_VRVRGruppo.Riga,
                    (SELECT Descrizione FROM x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) AS Taglia,
                    INFOAR.Ud_VR1
             FROM AR
             OUTER APPLY dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR
             LEFT JOIN x_VRVRGruppo ON x_VRVRGruppo.Ud_VR = INFOAR.Ud_VR1
             WHERE Cd_AR = ? AND INFOAR.Obsoleto = 'false'
             GROUP BY INFOAR.Ud_VR1, x_VRVRGruppo.Riga
             ORDER BY x_VRVRGruppo.Riga",
                [$articolo->Cd_AR]
            );

            $colore_head = DB::select(
                "SELECT x_VRVRGruppo.Riga,
                    (SELECT Descrizione FROM x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2) AS Colore,
                    INFOAR.Ud_VR2
             FROM AR
             OUTER APPLY dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR
             LEFT JOIN x_VRVRGruppo ON x_VRVRGruppo.Ud_VR = INFOAR.Ud_VR2
             WHERE Cd_AR = ? AND INFOAR.Obsoleto = 'false'
             GROUP BY INFOAR.Ud_VR2, x_VRVRGruppo.Riga
             ORDER BY x_VRVRGruppo.Riga",
                [$articolo->Cd_AR]
            );

            $prezzo = DB::select(
                "SELECT AR.Cd_AR, TAGLIA.Descrizione AS Taglia,
                    COLORE.Descrizione AS Colore, INFOAR.Prezzo,LSRevisione.Cd_LS
             FROM AR
             LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
             OUTER APPLY dbo.xmtf_LSArticoloVRInfo(LSArticolo.x_VRData) INFOAR
             LEFT JOIN LSRevisione ON LSRevisione.Id_LSRevisione = LSArticolo.Id_LSRevisione
             LEFT JOIN x_VR TAGLIA ON TAGLIA.Ud_x_VR = INFOAR.Ud_VR1
             LEFT JOIN x_VR COLORE ON COLORE.Ud_x_VR = INFOAR.Ud_VR2
             WHERE AR.Cd_AR = ?
             ORDER BY COLORE.Descrizione, TAGLIA.Descrizione",
                [$articolo->Cd_AR]
            );
            $prezzo_map = [];
            foreach ($prezzo as $p) {
                $prezzo_map[$p->Colore][$p->Taglia][str_replace(' ', '', $p->Cd_LS)] = number_format($p->Prezzo, 2, ',', ' ');
            }
            $listini = DB::SELECT('SELECT Cd_LS FROM LS where Cd_LS not in (\'UVEN\',\'UVEN_C\',\'UACQ_F\',\'UACQ\')');
            ?>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <?php foreach ($listini as $l) { ?>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                        <tr>
                            <th scope="col">(<?php echo str_replace(' ','',$l->Cd_LS); ?>) <?php echo $articoli[0]->Cd_AR; ?></th>
                            <?php foreach ($taglia as $t) { ?>
                                <th scope="col"><?php echo htmlspecialchars($t->Taglia); ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($colore_head as $c) { ?>
                            <tr>
                                <th scope="row" class="table-primary"><?php echo htmlspecialchars($c->Colore); ?></th>
                                <?php foreach ($taglia as $t) { ?>
                                    <td style="text-align: end">
                                        <?php
                                        echo $prezzo_map[$c->Colore][$t->Taglia][str_replace(' ', '', $l->Cd_LS)] ?? '-';
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
            <?php
        } else {
            echo "<p class='alert alert-warning'>Nessun articolo trovato.</p>";
        }
    }

    public
    function cerca_articolo_giacenza($codice)
    {

        $codice = str_replace("slash", "/", $codice);

        $articoli = DB::select(
            "SELECT AR.Id_AR, AR.Cd_AR, AR.Descrizione, ARAlias.Alias AS barcode,
                ARARMisura.UMFatt, LSArticolo.Prezzo
         FROM AR
         LEFT JOIN ARAlias ON AR.Cd_AR = ARAlias.Cd_AR
         LEFT JOIN ARARMisura ON ARARMisura.Cd_AR = AR.CD_AR
         LEFT JOIN LSArticolo ON LSArticolo.Cd_AR = AR.Cd_AR
         WHERE AR.CD_AR LIKE ? OR ARAlias.Alias LIKE ?",
            [$codice, $codice]
        );
        if (sizeof($articoli) <= 0) {
            $articoli = DB::SELECT('SELECT AR.* FROM x_ARVRAlias LEFT JOIN AR ON AR.Cd_AR = x_ARVRAlias.Cd_AR WHERE Alias = \'' . $codice . '\' ');
        }

        if (!empty($articoli)) {
            $articolo = $articoli[0];
            $taglia = DB::select(
                "SELECT x_VRVRGruppo.Riga,
                    (SELECT Descrizione FROM x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) AS Taglia,
                    INFOAR.Ud_VR1
             FROM AR
             OUTER APPLY dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR
             LEFT JOIN x_VRVRGruppo ON x_VRVRGruppo.Ud_VR = INFOAR.Ud_VR1
             WHERE Cd_AR = ? AND INFOAR.Obsoleto = 'false'
             GROUP BY INFOAR.Ud_VR1, x_VRVRGruppo.Riga
             ORDER BY x_VRVRGruppo.Riga",
                [$articolo->Cd_AR]
            );

            $colore_head = DB::select(
                "SELECT x_VRVRGruppo.Riga,
                    (SELECT Descrizione FROM x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2) AS Colore,
                    INFOAR.Ud_VR2
             FROM AR
             OUTER APPLY dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR
             LEFT JOIN x_VRVRGruppo ON x_VRVRGruppo.Ud_VR = INFOAR.Ud_VR2
             WHERE Cd_AR = ? AND INFOAR.Obsoleto = 'false'
             GROUP BY INFOAR.Ud_VR2, x_VRVRGruppo.Riga
             ORDER BY x_VRVRGruppo.Riga",
                [$articolo->Cd_AR]
            );

            $giacenza = DB::select(
                "SELECT AR.Cd_AR, TAGLIA.Descrizione AS Taglia,
                    COLORE.Descrizione AS Colore,xGD.Cd_MG,xGd.Quantita --, INFOAR.Prezzo,LSRevisione.Cd_LS
             FROM AR
             LEFT JOIN xmtf_MGDispEx(YEAR(GETDATE())) xGD on xGD.Cd_AR = AR.Cd_AR --and xGD.Ud_VR1 = INFOAR.Ud_VR1 and xGD.Ud_VR2 = INFOAR.Ud_VR2
             LEFT JOIN x_VR TAGLIA ON TAGLIA.Ud_x_VR = xGD.Ud_VR1
             LEFT JOIN x_VR COLORE ON COLORE.Ud_x_VR = xGD.Ud_VR2
             WHERE AR.Cd_AR = ?
             ORDER BY COLORE.Descrizione, TAGLIA.Descrizione",
                [$articolo->Cd_AR]
            );
            $giac_map = [];

            $magazzino = DB::SELECT('SELECT Cd_MG FROM MG');
            foreach ($giacenza as $p) {
                $giac_map[$p->Colore][$p->Taglia][str_replace(' ', '', $p->Cd_MG)] = number_format($p->Quantita, 2, ',', ' ');
            }
            ?>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">

                <?php foreach ($magazzino as $m) { ?>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                        <tr>
                            <th scope="col">(<?php echo str_replace(' ', '', $m->Cd_MG); ?>
                                ) <?php echo $articoli[0]->Cd_AR; ?> </th>
                            <?php foreach ($taglia as $t) { ?>
                                <th scope="col"><?php echo htmlspecialchars($t->Taglia); ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($colore_head as $c) { ?>
                            <tr>
                                <th scope="row" class="table-primary"><?php echo htmlspecialchars($c->Colore); ?></th>
                                <?php foreach ($taglia as $t) { ?>
                                    <td style="text-align: end">
                                        <?php
                                        echo $giac_map[$c->Colore][$t->Taglia][str_replace(' ', '', $m->Cd_MG)] ?? '-';
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                <?php } ?>
            </div>
            <?php
        } else {
            echo "<p class='alert alert-warning'>Nessun articolo trovato.</p>";
        }
        /*
        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            $taglia = DB::SELECT('SELECT * FROM
             (SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) as Taglia, INFOAR.Ud_VR1 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1)
              f
              Left Join x_VRVRGruppo On x_VRVRGruppo.Ud_VR = F.Ud_VR1 order by x_VRVRGruppo.Riga Desc ');
            $colore = DB::SELECT('SELECT
                                        (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1) as Taglia,
                                        (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2) as Colore,
                                        INFOAR.Ud_VR2,
                                        isnull(xGD.Cd_MG,\'ND\') as Cd_MG,
                                        isnull(xGD.Quantita,0) as Giacenza
                                        FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR
                                        left join xmtf_MGDispEx(YEAR(GETDATE())) xGD on xGD.Cd_AR = AR.Cd_AR and xGD.Ud_VR1 = INFOAR.Ud_VR1 and xGD.Ud_VR2 = INFOAR.Ud_VR2
                                        WHERE AR.Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' AND xGD.Quantita IS NOT NULL AND xGD.Quantita != 0
                                        GROUP BY INFOAR.Ud_VR1,INFOAR.Ud_VR2,
                                        xGD.Cd_MG,
                                        xGD.Quantita
                                        order by INFOAR.Ud_VR2 DESC ');
            echo '<h3>    Barcode: ' . $articolo->barcode . '<br>
                          Codice: ' . $articolo->Cd_AR . '<br>
                          Descrizione:<br>' . $articolo->Descrizione . '</h3>';
            ?>
            <script type="text/javascript">

                $('#modal_prezzo').val('<?php echo number_format($articolo->Prezzo, 2, '.', '') ?>');
                $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                <?php foreach($taglia as $t){?>
                option = document.createElement('option');
                option.setAttribute('taglia', '<?php echo $t->Taglia ?>')
                option.value = '<?php echo $t->Taglia ?>';
                option.innerHTML = '<?php echo $t->Taglia ?>';
                document.getElementById('modal_taglie').appendChild(option);
                <?php } ?>
                <?php foreach($colore as $c){ ?>
                option = document.createElement('option');
                option.setAttribute('giacenza', '<?php echo number_format($c->Giacenza, '2', ',', ' ') ?>')
                option.setAttribute('mg', '<?php echo str_replace(' ', '', $c->Cd_MG) ?>')
                option.id = 'modal_colori_<?php echo $c->Taglia ?>';
                option.style.display = 'none';
                option.value = '<?php echo $c->Colore ?>';
                option.innerHTML = '<?php echo $c->Colore . '- MAG [' . str_replace(' ', '', $c->Cd_MG) . ']'; ?>';
                document.getElementById('modal_colori').appendChild(option);
                <?php } ?>

                cambioTaglia();
            </script>
            <?php
        }*/
    }

    /*
        public function evadi_documento1($Id_DoTes,$Cd_DO){
            $righe = DB::SELECT('SELECT * FROM DoRig WHERE Id_DoTes = \''.$Id_DoTes.'\' and QtaEvadibile > \'0\' ');
            foreach ($righe as $riga){?>
                <li class="list-group-item">
                    <a href="#"  class="media">
                        <div class="media-body">
                            <h5><?php echo $riga->Cd_AR;if($riga->Cd_ARLotto != '')echo '  Lotto: '.$riga->Cd_ARLotto;  ?></h5>
                            <p>Quantita': <?php echo $riga->Qta ?></p>
                        </div>
                    </a>
                </li>
            <?php }
        }*/
    public
    function salva_documento1($Id_DoTes, $Cd_DO)
    {
        $righe = DB::SELECT('SELECT * FROM DoRig WHERE Id_DoTes = \'' . $Id_DoTes . '\' and QtaEvadibile > \'0\' ');
        foreach ($righe as $riga) {
            ?>
            <li class="list-group-item">
                <a href="#" class="media">
                    <div class="media-body">
                        <h5><?php echo $riga->Cd_AR;
                            if ($riga->Cd_ARLotto != '') echo '  Lotto: ' . $riga->Cd_ARLotto; ?></h5>
                        <p>Quantita': <?php echo $riga->Qta ?></p>
                    </div>
                </a>
            </li>
            <script type="text/javascript">
                $('#modal_Cd_AR_c_<?php echo $riga->Id_DORig ?>').val('<?php echo $riga->Cd_AR ?>');
                $('#modal_Cd_ARLotto_c_<?php echo $riga->Id_DORig ?>').val('<?php echo $riga->Cd_ARLotto ?>');
                $('#modal_Qta_c_<?php echo $riga->Id_DORig ?>').val('<?php echo $riga->Qta ?>');
                $('#modal_QtaEvasa_c_<?php echo $riga->Id_DORig ?>').val('<?php echo $riga->QtaEvasa ?>');
                $('#modal_QtaEvasa_c_<?php echo $riga->Id_DORig ?>').val('<?php echo $riga->PrezzoUnitarioV ?>');
            </script>
        <?php }
    }

    /*
        public function evadi_documento($Id_DoTes,$Cd_DO,$magazzino_A){

            $righe  = DB::SELECT('SELECT * FROM DoRig where Id_DoTes = \''.$Id_DoTes.'\'');
            $cf     = DB::SELECT('SELECT * FROM DORIG WHERE Id_DoTes = \''.$Id_DoTes.'\' ')[0]->Cd_CF;

                    $Id_DoTes1 = DB::table('DOTes')->insertGetId(['Cd_CF' => $cf, 'Cd_Do' => $Cd_DO]);

            foreach($righe as $r) {

                if ($r->QtaEvadibile > 0) {

                    if ($r->Cd_MGUbicazione_P != NULL || $r->Cd_ARLotto != '0')
                        $insert_evasione['Cd_MGUbicazione_P'] = $r->Cd_MGUbicazione_P;

                    if( $Cd_DO == 'DTR' || $Cd_DO == 'DTG') {
                        if ($r->Cd_MG_P != null || $r->Cd_ARLotto != '0')
                            $insert_evasione['Cd_MG_P'] = $r->Cd_MG_P;
                    }
                    else
                        $insert_evasione['Cd_MG_P'] = $r->Cd_MG_A;

                    if ($r->Cd_ARLotto != null || $r->Cd_ARLotto != '0')
                        $insert_evasione['Cd_ARLotto'] = $r->Cd_ARLotto;

                    if ($r->Cd_MGUbicazione_A != NULL || $r->Cd_ARLotto != '0')
                        $insert_evasione['Cd_MGUbicazione_A'] = $r->Cd_MGUbicazione_A;

                    if( $Cd_DO == 'DTR' || $Cd_DO == 'DTG'){
                        if ($r->Cd_MG_A != null || $r->Cd_ARLotto != '0')
                            $insert_evasione['Cd_MG_A'] = $r->Cd_MG_A;
                    }
                    else
                        $insert_evasione['Cd_MG_A'] = $magazzino_A;

                    if ($r->Cd_ARLotto != null || $r->Cd_ARLotto != '0')
                        $insert_evasione['Cd_ARLotto'] = $r->Cd_ARLotto;

                    $insert_evasione['Qta'] = $r->QtaEvadibile;
                    $insert_evasione['QtaEvadibile'] = $r->QtaEvadibile;
                    $insert_evasione['QtaEvasa'] = $r->QtaEvadibile;
                    $insert_evasione['Id_DoRig_Evade'] = $r->Id_DORig;
                    $insert_evasione['Cd_AR'] = $r->Cd_AR;
                    $insert_evasione['PrezzoUnitarioV'] = $r->PrezzoUnitarioV;
                    $insert_evasione['Cd_Aliquota'] = $r->Cd_Aliquota;
                    $insert_evasione['Cd_CGConto'] = $r->Cd_CGConto;
                    $insert_evasione['Id_DoRig_Evade'] = $r->Id_DORig;

                    $insert_evasione['Id_Dotes'] = $Id_DoTes1;
                    DB::table('DoRig')->insertGetId($insert_evasione);

                    $newId_DORIG = DB::SELECT('SELECT TOP 1 * FROM DORig ORDER BY Id_DORig DESC')[0]->Id_DORig;

                    DB::update('Update dorig set QtaEvadibile = \'0\'   where Id_DoRig = \'' . $r->Id_DORig . '\' ');
                    DB::update('Update dorig set Evasa = \'1\'   where Id_DoRig = \'' . $r->Id_DORig . '\' ');;

                    DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $Id_DoTes1");
                    DB::statement("exec asp_DO_End $Id_DoTes1");

                }


            }
            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes'");
            DB::statement("exec asp_DO_End '$Id_DoTes'");
            echo 'Le riga sono state completamente evase';
        }

    */
    public
    function evadi_articolo($Id_DoRig, $qtadaEvadere, $magazzino, $ubicazione, $lotto, $cd_cf, $documento, $cd_ar, $magazzino_A)
    {
        $cd_ar = str_replace("slash", "/", $cd_ar);
        $Id_DoTes = '0';
        if ($qtadaEvadere == '0') {
            echo 'Impossibile evadere la Quantita a 0';
            exit();
        } else {
            $date = date('d/m/Y', strtotime('today'));
            $controllo = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig = \'' . $Id_DoRig . '\'')[0]->Id_DOTes;
            $controlli = DB::SELECT('SELECT * FROM DORIG WHERE Id_DOTes = \'' . $controllo . '\'');
            foreach ($controlli as $c) {
                $testata = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig_Evade = \'' . $c->Id_DORig . '\' and DataDoc = \'' . $date . '\'');
                if ($testata != null)
                    $Id_DoTes = $testata[0]->Id_DOTes;
            }

        }
        if ($Id_DoTes == '0')
            $Id_DoTes = '';
        $Id_DoTes_old = DB::SELECT('SELECT * from DoRig where id_dorig = \'' . $Id_DoRig . '\' ')[0]->Id_DOTes;
        $listino = DB::SELECT('SELECT * from DOTes where Id_DOTes = \'' . $Id_DoTes_old . '\' ');
        $insert_evasione['PrezzoUnitarioV'] = $controlli[0]->PrezzoUnitarioV;
        if ($listino[0]->Cd_LS_1 != null)
            $listino = $listino[0]->Cd_LS_1;
        else
            $listino = '';
        if ($Id_DoTes == '' && $listino != '')
            $Id_DoTes = DB::table('DOTes')->insertGetId(['Cd_CF' => $cd_cf, 'Cd_Do' => $documento, 'Cd_LS_1' => $listino]);
        if ($Id_DoTes == '' && $listino == '')
            $Id_DoTes = DB::table('DOTes')->insertGetId(['Cd_CF' => $cd_cf, 'Cd_Do' => $documento]);
        $pagamento = DB::SELECT('SELECT * FROM DOTes WHERE ID_DOTes = \'' . $controllo . '\'');
        if ($pagamento[0]->Cd_PG != '') {
            $pagamento = $pagamento[0]->Cd_PG;
            DB::update("Update DOTes set Cd_PG = '$pagamento' where ID_DOTes = '$controllo'");
        }
        $agente = DB::SELECT('SELECT * FROM DOTes WHERE ID_DOTes = \'' . $controllo . '\'');
        if ($agente[0]->Cd_Agente_1 != '') {
            $agente = $agente[0]->Cd_Agente_1;
            DB::update("Update DOTes set Cd_Agente_1 = '$agente' where ID_DOTes = '$controllo'");
        }
        if ($magazzino_A != 0)
            $insert_evasione['Cd_MG_A'] = $magazzino_A;
        if ($magazzino != 0)
            $insert_evasione['Cd_MG_P'] = $magazzino;

        if ($lotto != '0')
            $insert_evasione['Cd_ARLotto'] = $lotto;
        $Id_DoTes1 = $Id_DoTes;
        $insert_evasione['Cd_AR'] = $cd_ar;
        $insert_evasione['Id_DORig_Evade'] = $Id_DoRig;
        $insert_evasione['Qta'] = $qtadaEvadere;
        $insert_evasione['QtaEvasa'] = $insert_evasione['Qta'];
        $Riga = DB::SELECT('SELECT * FROM DoRig where Id_DoRig=\'' . $Id_DoRig . '\'');
        $insert_evasione['Cd_Aliquota'] = $Riga[0]->Cd_Aliquota;
        $insert_evasione['PrezzoUnitarioV'] = $Riga[0]->PrezzoUnitarioV;
        if ($Riga[0]->ScontoRiga != '')
            $insert_evasione['ScontoRiga'] = $Riga[0]->ScontoRiga;
        $insert_evasione['Cd_CGConto'] = $Riga[0]->Cd_CGConto;
        $insert_evasione['Id_DoTes'] = $Id_DoTes1;
        $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $Id_DoRig . '\' ')[0]->QtaEvasa;
        $qta_evasa = intval($qta_evasa) + intval($qtadaEvadere);
        $qta_evadibile = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $Id_DoRig . '\' ')[0]->QtaEvadibile;
        $qta_evadibile = intval($qta_evadibile) - intval($qtadaEvadere);
        DB::table('DoRig')->insertGetId($insert_evasione);
        $Id_DoRig_OLD = DB::SELECT('SELECT TOP 1 * FROM DORig ORDER BY Id_DORig DESC')[0]->Id_DORig;

        if ($qtadaEvadere < $Riga[0]->QtaEvadibile) {
            DB::UPDATE('Update DoRig set QtaEvadibile = \'' . $qta_evadibile . '\'WHERE Id_DoRig = \'' . $Id_DoRig . '\'');
            DB::UPDATE('Update DoRig set QtaEvasa = \'' . $qta_evasa . '\'WHERE Id_DoRig = \'' . $Id_DoRig_OLD . '\'');
        } else {
            DB::UPDATE('Update DoRig set QtaEvadibile = \'0\'WHERE Id_DoRig = \'' . $Id_DoRig . '\'');
            DB::update('Update dorig set Evasa = \'1\'   where Id_DoRig = \'' . $Id_DoRig . '\' ');
            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes_old'");
            DB::statement("exec asp_DO_End '$Id_DoTes_old'");
        }
        DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes1'");
        DB::statement("exec asp_DO_End '$Id_DoTes1'");
    }

    public function conferma_righe_all($Id_DoTes_old, $cd_mg_a, $cd_mg_p, $cd_do)
    {
        try {
            $insert = [];

            DB::beginTransaction();

            $valori = DB::SELECT('SELECT
                                        DORig.Id_DORig,
                                        VR.Ud_VR1,
                                        TAGLIA.Descrizione as Taglia,
                                        VR.Ud_VR2,
                                        COLORE.Descrizione as Colore,
                                        VR.Qta as QtaVariante,
                                        CASE
                                            WHEN VR.QtaRes > VR.Qta THEN VR.Qta
                                            ELSE VR.QtaRes
                                        END as QtaRes
                                       FROM DORIG
                                           OUTER APPLY dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                                           left join x_VR TAGLIA on  TAGLIA.Ud_x_VR = VR.Ud_VR1
                                           left join x_VR COLORE on  COLORE.Ud_x_VR = VR.Ud_VR2
                                           WHERE ID_DOTes = ' . $Id_DoTes_old);

            foreach ($valori as $d) { //                $key2 = explode('_', $key);
                $insert[] = array(
                    "id_dorig" => $d->Id_DORig,
                    "taglia" => $d->Taglia,
                    "colore" => $d->Colore,
                    "quantita" => $d->QtaRes,
                );
            }

            $Id_DoRig = 0;
            foreach ($insert as $i)
                $Id_DoRig .= '\',\'' . $i['id_dorig'];
            $date = date('d/m/Y', strtotime('today'));
            $date_compare = date('Y-m-d 00:00:00', strtotime('today'));

            $controllo = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig in (\'' . $Id_DoRig . '\')')[0]->Id_DOTes;

            $controlli = DB::SELECT('SELECT * FROM DORIG WHERE Id_DOTes = \'' . $controllo . '\'');

            //FACOLTATIVO SE SI VUOLE EVADERE SEMPRE E SOLO IN UN DOCUMENTO
            $dorigs = '';
            foreach ($controlli as $c) {
                $dorigs .= '\',\'' . $c->Id_DORig;
            }/*
            $testata = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig_Evade in (\'' . $dorigs . '\')');
            if (sizeof($testata) > 0)
                if ($testata[0]->DataDoc == $date_compare)
                    $Id_DoTes = $testata[0]->Id_DOTes;*/

            $dotes = DB::SELECT('SELECT D.* FROM DOTes D LEFT JOIN DORig do ON do.Id_DOTes = D.Id_DOTes where do.Id_DORig in (\'' . $dorigs . '\')');

            if (!isset($Id_DoTes)) {
                $Id_DoTes = '';
            }

            foreach ($insert as $r) {
                $identificativo = $r['id_dorig'];

                $lotto = '0';

                $cd_cf = $controlli[0]->Cd_CF;

                $documento = $cd_do;

                if ($cd_mg_p != 'ND') $insert_evasione['Cd_MG_P'] = $cd_mg_p;
                if ($cd_mg_a != 'ND') $insert_evasione['Cd_MG_A'] = $cd_mg_a;

                $PrezzoUnitarioV = '';
                $Cd_AR = '';
                $Cd_CGConto = '';
                $NoteRiga = '';
                $Cd_Aliquota = '';

                foreach ($controlli as $x) {
                    if ($x->Id_DORig == $r['id_dorig']) {
                        $PrezzoUnitarioV = $x->PrezzoUnitarioV;
                        $Cd_AR = $x->Cd_AR;
                        $Cd_CGConto = $x->Cd_CGConto;
                        $Cd_Aliquota = $x->Cd_Aliquota;
                        $NoteRiga = $x->NoteRiga;
                    }
                }

                $ud_vr1 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['taglia'] . '\'')[0]->q;
                $ud_vr2 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['colore'] . '\'');
                if (sizeof($ud_vr2) > 0) {
                    $ud_vr2 = $ud_vr2[0]->q;
                } else {
                    $r['colore'] = str_replace('.', ' ', $r['colore']);
                    $ud_vr2 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['colore'] . '\'')[0]->q;
                }

                $insert_evasione['x_VRData'] = '<rows>';
                $insert_evasione['x_VRData'] .= '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $r['quantita'] . '" qtares="' . $r['quantita'] . '"/>';
                $insert_evasione['x_VRData'] .= '</rows>';

                if ($Id_DoTes == '') {
                    $Id_DoTes = DB::table('DOTes')->insertGetId(['Cd_CF' => $cd_cf, 'Cd_Do' => $documento]);
                    DB::update("Update dotes set NumeroDocRif = '" . str_replace('\'', '', $dotes[0]->NumeroDocRif) . "' where dotes.id_dotes = '$Id_DoTes'");
                    DB::update("Update dotes set DataDocRif = '" . $dotes[0]->DataDocRif . "' where dotes.id_dotes = '$Id_DoTes'");
                    DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes'");
                    DB::statement("exec asp_DO_End '$Id_DoTes'");
                }

                if ($lotto != '0')
                    $insert_evasione['Cd_ARLotto'] = $lotto;

                $Id_DoTes1 = $Id_DoTes;

                $insert_evasione['Cd_AR'] = $Cd_AR;

                $insert_evasione['PrezzoUnitarioV'] = $PrezzoUnitarioV;

                $insert_evasione['Qta'] = $r['quantita'];


                $Riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DORIG IN (' . $identificativo . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                AND (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) = \'' . $r['colore'] . '\' and (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) = \'' . $r['taglia'] . '\'
                ORDER BY TIMEINS DESC');

                $insert_evasione['Cd_Aliquota'] = $Cd_Aliquota;

                $insert_evasione['Cd_CGConto'] = $Cd_CGConto;

                $insert_evasione['Id_DoTes'] = $Id_DoTes1;

                $insert_evasione['Id_DORig_Evade'] = $r['id_dorig'];

                //DB::table('DoRig')->insertGetId($insert_evasione);

                $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvasa;

                $qta_evasa = intval($qta_evasa) + intval($r['quantita']);

                $qta_evadibile = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvadibile;

                $qta_evadibile = intval($qta_evadibile) - intval($r['quantita']);

                $check_riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DORIG IN (' . $r['id_dorig'] . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                ORDER BY TIMEINS DESC');

                $_oldqta = 0;
                $_oldqtares = 0;
                $old_xml = '<rows>';
                foreach ($check_riga as $c) {
                    $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                        $_oldqta = $c->QtaVariante;
                        $_oldqtares = $c->QtaRes;
                    }
                }
                $old_xml .= '</rows>';
                $x_update = str_replace('<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $_oldqta . '" qtares="' . $_oldqtares . '" />',
                    '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($_oldqta) . '" qtares="' . ($_oldqtares - $r['quantita']) . '.00000000" />',
                    $old_xml);

                if (floatval($r['quantita']) <= floatval($Riga[0]->QtaRes)) {
                    $new_doc = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE Id_DOTes IN (' . $Id_DoTes1 . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                ORDER BY TIMEINS DESC');
                    $update = 0;
                    if (sizeof($new_doc) > 0) {
                        foreach ($new_doc as $r1) {
                            if ($r1->Cd_AR == $Cd_AR) {
                                if ($ud_vr1 == $r1->Ud_VR1 && $ud_vr2 == $r1->Ud_VR2) {
                                    // STESSO ARTICOLO CON STESSE TAGLIE E COLORI
                                    $update = 1;
                                    $_xoldqta = 0;
                                    $_xoldqtares = 0;
                                    $xml = '<rows>';
                                    foreach ($new_doc as $c) {
                                        $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                                        if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                                            $_xoldqta = $c->QtaVariante;
                                            $_xoldqtares = $c->QtaRes;
                                        }
                                    }
                                    $xml .= '</rows>';
                                    $x_update2 = str_replace('<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $_xoldqta . '" qtares="' . $_xoldqtares . '" />', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($_xoldqta + $r['quantita']) . '" qtares="' . ($_xoldqtares + $r['quantita']) . '" />', $xml);
                                    if ($x_update2 != '') DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['x_VRData' => $x_update2]);
                                    $check_qta = DB::select('SELECT DORIG.Id_DORig,SUM(VR.Qta) as QtaVariante, SUM(VR.QtaRes) as QtaRes,QtaEvasa
                                                            FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                                                            WHERE DORig.Id_DORIG = ' . $r1->Id_DORig . '
                                                            group by DORig.Id_DORIG,DORIG.QtaEvasa');
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['Qta' => $check_qta[0]->QtaVariante]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvadibile' => $check_qta[0]->QtaRes]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvasa' => intval($check_qta[0]->QtaEvasa) + intval($r['quantita'])]);
                                    break;
                                } else {
                                    // STESSO ARTICOLO MA CON DIVERSE TAGLIE E COLORI
                                    $update = 1;
                                    $xml = '<rows>';
                                    foreach ($new_doc as $c) {
                                        $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                                        if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                                            $_xoldqta = $c->QtaVariante;
                                            $_xoldqtares = $c->QtaRes;
                                        }
                                    }
                                    $xml .= '</rows>';

                                    $x_update2 = str_replace('</rows>', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($r['quantita']) . '" qtares="' . ($r['quantita']) . '" /></rows>', $xml);

                                    if ($x_update2 != '') DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['x_VRData' => $x_update2]);
                                    $check_qta = DB::select('SELECT DORIG.Id_DORig,SUM(VR.Qta) as QtaVariante, SUM(VR.QtaRes) as QtaRes,QtaEvasa
                                                            FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                                                            WHERE DORig.Id_DORIG = ' . $r1->Id_DORig . '
                                                            group by DORig.Id_DORIG,DORIG.QtaEvasa');

                                    /*                                echo '2-QtaEvasa =>' . intval($check_qta[0]->QtaEvasa);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo 'da evadere =>' . intval($r['quantita']);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo 'TOTALE =>' . intval($check_qta[0]->QtaEvasa) + intval($r['quantita']);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';*/
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['Qta' => $check_qta[0]->QtaVariante]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvadibile' => $check_qta[0]->QtaRes]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvasa' => intval($check_qta[0]->QtaEvasa) + intval($r['quantita'])]);
                                    break;
                                }
                            }
                        }
                    }
                    if ($update == 0) {
                        $insert_evasione['QtaEvasa'] = intval($r['quantita']);
                        $insert_evasione['NoteRiga'] = $NoteRiga;
                        DB::table('DoRig')->insertGetId($insert_evasione);
                        $insert_evasione['QtaEvasa'] = null;
                    }

                    if ($x_update != '') DB::table('DORig')->where('Id_DORig', $check_riga[0]->Id_DORig)->update(['x_VRData' => $x_update]);

                    $Id_DoTes_old = DB::SELECT('SELECT * from DoRig where id_dorig = \'' . $check_riga[0]->Id_DORig . '\' ')[0]->Id_DOTes;

                    DB::UPDATE('Update DoRig set QtaEvadibile = \'' . $qta_evadibile . '\' WHERE Id_DoRig = \'' . $r['id_dorig'] . '\'');

                    /* $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvasa;

                     $qta_evasa = intval($qta_evasa) + intval($r['quantita']);

                     DB::UPDATE('Update DoRig set QtaEvasa = \'' . $qta_evasa . '\' WHERE Id_DoRig = \'' . $r['id_dorig'] . '\'');*/

                    DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes_old'");

                    DB::statement("exec asp_DO_End '$Id_DoTes_old'");

                }
                DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes1'");
                DB::statement("exec asp_DO_End '$Id_DoTes1'");
                $this->GestisciXml($Id_DoTes1);
            }
            DB::COMMIT();
            return response('{"Success":"Evasione Completata"}', '200');
        } catch (\Exception $e) {
            DB::ROLLBACK();
            return $e->getLine() . ' - ' . $e->getMessage();
        }
    }

    public
    function conferma_righe($Id_DoRig, $cd_mg_a, $cd_mg_p, $cd_do)
    {
        try {
            $insert = [];

            DB::beginTransaction();

            foreach ($_GET as $key => $d) {
                $key2 = explode('_', $key);
                $insert[] = array(
                    "id_dorig" => $key2[0],
                    "taglia" => $key2[1],
                    "colore" => (sizeof($key2) == 4) ? $key2[2] . '.' . $key2[3] : $key2[2],
                    "quantita" => $d,
                );
            }

            $Id_DoRig = 0;
            foreach ($insert as $i)
                $Id_DoRig .= '\',\'' . $i['id_dorig'];

            $date = date('d/m/Y', strtotime('today'));
            $date_compare = date('Y-m-d 00:00:00', strtotime('today'));

            $controllo = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig in (\'' . $Id_DoRig . '\')')[0]->Id_DOTes;

            $controlli = DB::SELECT('SELECT * FROM DORIG WHERE Id_DOTes = \'' . $controllo . '\'');

            //FACOLTATIVO SE SI VUOLE EVADERE SEMPRE E SOLO IN UN DOCUMENTO
            $dorigs = '';
            foreach ($controlli as $c) {
                $dorigs .= '\',\'' . $c->Id_DORig;
            }/*
            $testata = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig_Evade in (\'' . $dorigs . '\')');
            if (sizeof($testata) > 0)
                if ($testata[0]->DataDoc == $date_compare)
                    $Id_DoTes = $testata[0]->Id_DOTes;*/

            $dotes = DB::SELECT('SELECT D.* FROM DOTes D LEFT JOIN DORig do ON do.Id_DOTes = D.Id_DOTes where do.Id_DORig in (\'' . $dorigs . '\')');

            if (!isset($Id_DoTes)) {
                $Id_DoTes = '';
            }

            foreach ($insert as $r) {
                $identificativo = $r['id_dorig'];

                $lotto = '0';

                $cd_cf = $controlli[0]->Cd_CF;

                $documento = $cd_do;

                if ($cd_mg_p != 'ND') $insert_evasione['Cd_MG_P'] = $cd_mg_p;
                if ($cd_mg_a != 'ND') $insert_evasione['Cd_MG_A'] = $cd_mg_a;

                $PrezzoUnitarioV = '';
                $Cd_AR = '';
                $Cd_CGConto = '';
                $NoteRiga = '';
                $Cd_Aliquota = '';

                foreach ($controlli as $x) {
                    if ($x->Id_DORig == $r['id_dorig']) {
                        $PrezzoUnitarioV = $x->PrezzoUnitarioV;
                        $Cd_AR = $x->Cd_AR;
                        $Cd_CGConto = $x->Cd_CGConto;
                        $Cd_Aliquota = $x->Cd_Aliquota;
                        $NoteRiga = $x->NoteRiga;
                    }
                }

                $ud_vr1 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['taglia'] . '\'')[0]->q;
                $ud_vr2 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['colore'] . '\'');
                if (sizeof($ud_vr2) > 0) {
                    $ud_vr2 = $ud_vr2[0]->q;
                } else {
                    $r['colore'] = str_replace('.', ' ', $r['colore']);
                    $ud_vr2 = DB::SELECT('SELECT Ud_x_VR AS q from x_VR WHERE Descrizione = \'' . $r['colore'] . '\'')[0]->q;
                }

                $insert_evasione['x_VRData'] = '<rows>';
                $insert_evasione['x_VRData'] .= '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $r['quantita'] . '" qtares="' . $r['quantita'] . '"/>';
                $insert_evasione['x_VRData'] .= '</rows>';

                if ($Id_DoTes == '') {
                    $Id_DoTes = DB::table('DOTes')->insertGetId(['Cd_CF' => $cd_cf, 'Cd_Do' => $documento]);
                    DB::update("Update dotes set NumeroDocRif = '" . str_replace('\'', '', $dotes[0]->NumeroDocRif) . "' where dotes.id_dotes = '$Id_DoTes'");
                    DB::update("Update dotes set DataDocRif = '" . $dotes[0]->DataDocRif . "' where dotes.id_dotes = '$Id_DoTes'");
                    DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes'");
                    DB::statement("exec asp_DO_End '$Id_DoTes'");
                }

                if ($lotto != '0')
                    $insert_evasione['Cd_ARLotto'] = $lotto;

                $Id_DoTes1 = $Id_DoTes;

                $insert_evasione['Cd_AR'] = $Cd_AR;

                $insert_evasione['PrezzoUnitarioV'] = $PrezzoUnitarioV;

                $insert_evasione['Qta'] = $r['quantita'];


                $Riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DORIG IN (' . $identificativo . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                AND (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) = \'' . $r['colore'] . '\' and (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) = \'' . $r['taglia'] . '\'
                ORDER BY TIMEINS DESC');

                $insert_evasione['Cd_Aliquota'] = $Cd_Aliquota;

                $insert_evasione['Cd_CGConto'] = $Cd_CGConto;

                $insert_evasione['Id_DoTes'] = $Id_DoTes1;

                $insert_evasione['Id_DORig_Evade'] = $r['id_dorig'];

                //DB::table('DoRig')->insertGetId($insert_evasione);

                $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvasa;

                $qta_evasa = intval($qta_evasa) + intval($r['quantita']);

                $qta_evadibile = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvadibile;

                $qta_evadibile = intval($qta_evadibile) - intval($r['quantita']);

                $check_riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DORIG IN (' . $r['id_dorig'] . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                ORDER BY TIMEINS DESC');

                $_oldqta = 0;
                $_oldqtares = 0;
                $old_xml = '<rows>';
                foreach ($check_riga as $c) {
                    $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                        $_oldqta = $c->QtaVariante;
                        $_oldqtares = $c->QtaRes;
                    }
                }
                $old_xml .= '</rows>';
                $x_update = str_replace('<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $_oldqta . '" qtares="' . $_oldqtares . '" />',
                    '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($_oldqta) . '" qtares="' . ($_oldqtares - $r['quantita']) . '.00000000" />',
                    $old_xml);

                if (floatval($r['quantita']) <= floatval($Riga[0]->QtaRes)) {
                    $new_doc = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE Id_DOTes IN (' . $Id_DoTes1 . ')
                AND DORig.Cd_AR = \'' . $Cd_AR . '\'
                ORDER BY TIMEINS DESC');
                    $update = 0;
                    if (sizeof($new_doc) > 0) {
                        foreach ($new_doc as $r1) {
                            if ($r1->Cd_AR == $Cd_AR) {
                                if ($ud_vr1 == $r1->Ud_VR1 && $ud_vr2 == $r1->Ud_VR2) {
                                    // STESSO ARTICOLO CON STESSE TAGLIE E COLORI
                                    $update = 1;
                                    $_xoldqta = 0;
                                    $_xoldqtares = 0;
                                    $xml = '<rows>';
                                    foreach ($new_doc as $c) {
                                        $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                                        if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                                            $_xoldqta = $c->QtaVariante;
                                            $_xoldqtares = $c->QtaRes;
                                        }
                                    }
                                    $xml .= '</rows>';
                                    $x_update2 = str_replace('<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $_xoldqta . '" qtares="' . $_xoldqtares . '" />', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($_xoldqta + $r['quantita']) . '" qtares="' . ($_xoldqtares + $r['quantita']) . '" />', $xml);
                                    if ($x_update2 != '') DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['x_VRData' => $x_update2]);
                                    $check_qta = DB::select('SELECT DORIG.Id_DORig,SUM(VR.Qta) as QtaVariante, SUM(VR.QtaRes) as QtaRes,QtaEvasa
                                                            FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                                                            WHERE DORig.Id_DORIG = ' . $r1->Id_DORig . '
                                                            group by DORig.Id_DORIG,DORIG.QtaEvasa');
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['Qta' => $check_qta[0]->QtaVariante]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvadibile' => $check_qta[0]->QtaRes]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvasa' => intval($check_qta[0]->QtaEvasa) + intval($r['quantita'])]);
                                    break;
                                } else {
                                    // STESSO ARTICOLO MA CON DIVERSE TAGLIE E COLORI
                                    $update = 1;
                                    $xml = '<rows>';
                                    foreach ($new_doc as $c) {
                                        $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                                        if ($ud_vr1 == $c->Ud_VR1 && $ud_vr2 == $c->Ud_VR2) {
                                            $_xoldqta = $c->QtaVariante;
                                            $_xoldqtares = $c->QtaRes;
                                        }
                                    }
                                    $xml .= '</rows>';

                                    $x_update2 = str_replace('</rows>', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($r['quantita']) . '" qtares="' . ($r['quantita']) . '" /></rows>', $xml);

                                    if ($x_update2 != '') DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['x_VRData' => $x_update2]);
                                    $check_qta = DB::select('SELECT DORIG.Id_DORig,SUM(VR.Qta) as QtaVariante, SUM(VR.QtaRes) as QtaRes,QtaEvasa
                                                            FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                                                            WHERE DORig.Id_DORIG = ' . $r1->Id_DORig . '
                                                            group by DORig.Id_DORIG,DORIG.QtaEvasa');

                                    /*                                echo '2-QtaEvasa =>' . intval($check_qta[0]->QtaEvasa);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo 'da evadere =>' . intval($r['quantita']);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo 'TOTALE =>' . intval($check_qta[0]->QtaEvasa) + intval($r['quantita']);
                                                                    echo '<br>';
                                                                    echo '<br>';
                                                                    echo '<br>';*/
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['Qta' => $check_qta[0]->QtaVariante]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvadibile' => $check_qta[0]->QtaRes]);
                                    DB::table('DORig')->where('Id_DORig', $r1->Id_DORig)->update(['QtaEvasa' => intval($check_qta[0]->QtaEvasa) + intval($r['quantita'])]);
                                    break;
                                }
                            }
                        }
                    }
                    if ($update == 0) {
                        $insert_evasione['QtaEvasa'] = intval($r['quantita']);
                        $insert_evasione['NoteRiga'] = $NoteRiga;
                        DB::table('DoRig')->insertGetId($insert_evasione);
                        $insert_evasione['QtaEvasa'] = null;
                    }

                    if ($x_update != '') DB::table('DORig')->where('Id_DORig', $check_riga[0]->Id_DORig)->update(['x_VRData' => $x_update]);

                    $Id_DoTes_old = DB::SELECT('SELECT * from DoRig where id_dorig = \'' . $check_riga[0]->Id_DORig . '\' ')[0]->Id_DOTes;

                    DB::UPDATE('Update DoRig set QtaEvadibile = \'' . $qta_evadibile . '\' WHERE Id_DoRig = \'' . $r['id_dorig'] . '\'');

                    /* $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $r['id_dorig'] . '\' ')[0]->QtaEvasa;

                     $qta_evasa = intval($qta_evasa) + intval($r['quantita']);

                     DB::UPDATE('Update DoRig set QtaEvasa = \'' . $qta_evasa . '\' WHERE Id_DoRig = \'' . $r['id_dorig'] . '\'');*/

                    DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes_old'");

                    DB::statement("exec asp_DO_End '$Id_DoTes_old'");

                }
                DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes1'");
                DB::statement("exec asp_DO_End '$Id_DoTes1'");
                $this->GestisciXml($Id_DoTes1);
            }
            DB::COMMIT();
            return response('{"Success":"Evasione Completata"}', '200');
        } catch (\Exception $e) {
            DB::ROLLBACK();
            return $e->getLine() . ' - ' . $e->getMessage();
        }
    }

    public
    function GestisciXML($Id_DOTes)
    {
        $doc = DB::select('SELECT DISTINCT Id_DORig FROM DORig WHERE Id_DOTes = \'' . $Id_DOTes . '\'');
        foreach ($doc as $d) {
            $check_riga = DB::SELECT('SELECT
                SUM(VR.Qta) as QtaVariante,
                SUM(VR.QtaRes) as QtaRes,
                VR.Ud_VR1,VR.Ud_VR2
                FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DORIG IN (' . $d->Id_DORig . ')
                GROUP BY VR.Ud_VR1,VR.Ud_VR2 ');

            $xml = '<rows>';
            $qta = 0;
            $qtaEvadibile = 0;
            foreach ($check_riga as $c) {
                $qta += $c->QtaVariante;
                $qtaEvadibile += $c->QtaRes;
                $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
            }
            $xml .= '</rows>';
            if ($xml != '') {
                DB::table('DORig')->where('Id_DORig', $d->Id_DORig)->update(['x_VRData' => $xml]);
                DB::table('DORig')->where('Id_DORig', $d->Id_DORig)->update(['Qta' => $qta, 'QtaEvadibile' => $qtaEvadibile]);
            }
            DB::statement("exec asp_DO_End '$Id_DOTes'");

        }
    }

    public
    function evadi_articolo2($Id_DoRig)
    {
        $Id_DoTes = '';
        $date = date('d/m/Y', strtotime('today'));
        $controllo = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig in (\'' . $Id_DoRig . '\')')[0]->Id_DOTes;
        $controlli = DB::SELECT('SELECT * FROM DORIG WHERE Id_DOTes = \'' . $controllo . '\'');
        foreach ($controlli as $c) {
            $testata = DB::SELECT('SELECT * FROM DORIG WHERE Id_DORig_Evade = \'' . $c->Id_DORig . '\'');
            if ($testata != null)
                if ($testata[0]->DataDoc == $date)
                    $Id_DoTes = $testata[0]->Id_DOTes;
        }
        $righe = DB::select('SELECT * FROM DORIG WHERE ID_DORIG IN (\'' . $Id_DoRig . '\')');
        foreach ($righe as $r) {
            $Id_DoRig = $r->Id_DORig;
            $r['quantita'] = $r->QtaEvadibile;
            $magazzino = $r->Cd_MG_A;
            $ubicazione = '0';
            $lotto = $r->Cd_ARLotto;
            $cd_cf = $r->Cd_CF;
            if ($r->Cd_DO == 'OAF')
                $documento = 'DCF';
            if ($r->Cd_DO == 'OVC')
                $documento = 'DDT';
            $cd_ar = $r->Cd_AR;
            $magazzino_A = '00001'; //magazzino di default
            $magazzino = '00001'; //magazzino di default
            $insert_evasione['Cd_MG_P'] = '';
            $insert_evasione['Cd_MG_A'] = '';
            $insert_evasione['x_VRData'] = $r->x_VRData;

            if ($Id_DoTes == '') {
                $Id_DoTes = DB::table('DOTes')->insertGetId(['Cd_CF' => $cd_cf, 'Cd_Do' => $documento]);
                if ($ubicazione != '0')
                    $insert_evasione['Cd_MGUbicazione_P'] = $ubicazione;
                if ($magazzino != '0')
                    $insert_evasione['Cd_MG_P'] = $magazzino;
                if ($magazzino_A != '0')
                    $insert_evasione['Cd_MG_A'] = $magazzino_A;

                DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes'");
                DB::statement("exec asp_DO_End '$Id_DoTes'");
            }

            if ($insert_evasione['Cd_MG_P'] == null || $insert_evasione['Cd_MG_P'] == '0')
                $insert_evasione['Cd_MG_P'] = $magazzino;
            if ($insert_evasione['Cd_MG_A'] == null || $insert_evasione['Cd_MG_A'] == '0')
                $insert_evasione['Cd_MG_A'] = $magazzino_A;
            if ($lotto != '0')
                $insert_evasione['Cd_ARLotto'] = $lotto;
            $Id_DoTes1 = $Id_DoTes;
            $insert_evasione['Cd_AR'] = $cd_ar;
            $insert_evasione['Id_DORig_Evade'] = $Id_DoRig;
            $insert_evasione['PrezzoUnitarioV'] = $r->PrezzoUnitarioV;
            $insert_evasione['Qta'] = $r['quantita'];
            $insert_evasione['QtaEvasa'] = $insert_evasione['Qta'];

            $Riga = DB::SELECT('SELECT * FROM DoRig where Id_DoRig=\'' . $Id_DoRig . '\'');
            $insert_evasione['Cd_Aliquota'] = $r->Cd_Aliquota;
            $insert_evasione['Cd_CGConto'] = $r->Cd_CGConto;
            $insert_evasione['Id_DoTes'] = $Id_DoTes1;


            $qta_evasa = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $Id_DoRig . '\' ')[0]->QtaEvasa;
            $qta_evasa = intval($qta_evasa) + intval($r['quantita']);
            $qta_evadibile = DB::SELECT('SELECT * FROM DORig WHERE Id_DoRig= \'' . $Id_DoRig . '\' ')[0]->QtaEvadibile;
            $qta_evadibile = intval($qta_evadibile) - intval($r['quantita']);
            DB::table('DoRig')->insertGetId($insert_evasione);
            $Id_DoRig_OLD = DB::SELECT('SELECT TOP 1 * FROM DORIG ORDER BY Id_DORig DESC')[0]->Id_DORig;

            if ($r['quantita'] < $Riga[0]->QtaEvadibile) {
                DB::UPDATE('Update DoRig set QtaEvadibile = \'' . $qta_evadibile . '\'WHERE Id_DoRig = \'' . $Id_DoRig . '\'');
                DB::UPDATE('Update DoRig set QtaEvasa = \'' . $qta_evasa . '\'WHERE Id_DoRig = \'' . $Id_DoRig_OLD . '\'');
            } else {
                DB::UPDATE('Update DoRig set QtaEvadibile = \'0\'WHERE Id_DoRig = \'' . $Id_DoRig . '\'');
                DB::update('Update dorig set Evasa = \'1\'   where Id_DoRig = \'' . $Id_DoRig . '\' ');
                $Id_DoTes_old = DB::SELECT('SELECT * from DoRig where id_dorig = \'' . $Id_DoRig . '\' ')[0]->Id_DOTes;
                DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes_old'");
                DB::statement("exec asp_DO_End '$Id_DoTes_old'");
            }
            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = '$Id_DoTes1'");
            DB::statement("exec asp_DO_End '$Id_DoTes1'");
        }

    }

    public
    function crea_documento($cd_cf, $cd_do, $numero, $data)
    {

        $fornitore = DB::SELECT('SELECT * FROM CF WHERE Cd_CF = \'' . $cd_cf . '\' ');
        /*if(sizeof($listino)> 0)
            $listino = $fornitore[0]->Cd_LS_1;
        else*/
        $listino = 'BANCO';
        $insert_testata_ordine['Cd_LS_1'] = $listino;
        $insert_testata_ordine['Cd_CF'] = $cd_cf;
        $insert_testata_ordine['Cd_Do'] = $cd_do;
        $insert_testata_ordine['NumeroDoc'] = $numero;
        /*if($cd_do == 'DDT')
            $insert_testata_ordine['Modificabile'] = 0 ;*/
        if ($cd_do == 'DDT') {
            $insert_testata_ordine['Cd_DoSped'] = '02';
            $insert_testata_ordine['Cd_DoPorto'] = '01';
            $insert_testata_ordine['Cd_DoTrasporto'] = '001';
            $insert_testata_ordine['Cd_DoAspBene'] = 'AV';
            date_default_timezone_set('Europe/Rome');
            $ora = date('Y-m-d', strtotime('now'));
            $insert_testata_ordine['TrasportoDataOra'] = $ora;
        }
        if ($fornitore[0]->Cd_CGConto_Banca)
            $insert_testata_ordine['Cd_CGConto_Banca'] = $fornitore[0]->Cd_CGConto_Banca;
        $data = str_replace('-', '', $data);
        $insert_testata_ordine['DataDoc'] = $data;
        $Id_DoTes = DB::table('DOTes')->insertGetId($insert_testata_ordine);
        echo $Id_DoTes;
    }


    public
    function crea_documento_rif($cd_cf, $cd_do, $numero, $data, $numero_rif, $data_rif)
    {

        $fornitore = DB::SELECT('SELECT * FROM CF WHERE Cd_CF = \'' . $cd_cf . '\' ');
        if (sizeof($fornitore) > 0)
            $listino = $fornitore[0]->Cd_LS_1;
        else
            $listino = 'BANCO';
        if (sizeof($fornitore) > 0)
            if ($fornitore[0]->Cd_PG != null)
                $insert_testata_ordine['Cd_PG'] = $fornitore[0]->Cd_PG;
        $insert_testata_ordine['Cd_LS_1'] = $listino;
        $insert_testata_ordine['Cd_CF'] = $cd_cf;
        $insert_testata_ordine['Cd_Do'] = $cd_do;
        if ($fornitore[0]->Cd_CGConto_Banca)
            $insert_testata_ordine['Cd_CGConto_Banca'] = $fornitore[0]->Cd_CGConto_Banca;
        $insert_testata_ordine['NumeroDoc'] = $numero;
        /*if ($cd_do == 'DDT')
            $insert_testata_ordine['Modificabile'] = 0;*/
        $data = str_replace('-', '', $data);
        $insert_testata_ordine['DataDoc'] = $data;
        if ($numero_rif != '0') {
            $insert_testata_ordine['NumeroDocRif'] = $numero_rif;
            $data_rif = str_replace('-', '', $data_rif);
        }
        if ($data_rif != '0')
            $insert_testata_ordine['DataDocRif'] = $data_rif;
        /*        if ($cd_do == 'DDT') {
                    $insert_testata_ordine['Cd_DoSped'] = '02';
                    $insert_testata_ordine['Cd_DoPorto'] = '01';
                    $insert_testata_ordine['Cd_DoTrasporto'] = '001';
                    $insert_testata_ordine['Cd_DoAspBene'] = 'AV';
                    date_default_timezone_set('Europe/Rome');
                    $ora = date('Y-m-d', strtotime('now'));
                    $ora = str_replace('-', '', $ora);
                    $insert_testata_ordine['TrasportoDataOra'] = $ora;
                    if ($dest != 0)
                        $insert_testata_ordine['Cd_CFDest'] = $dest;
                }*/
        $Id_DoTes = DB::table('DOTes')->insertGetId($insert_testata_ordine);
        echo $Id_DoTes;
    }

    public
    function aggiungi_articolo_ordine($id_ordine, $codice, $quantita, $magazzino_A, $ubicazione_A, $lotto, $magazzino_P, $ubicazione_P, $taglia, $colore)
    {
        $codice = str_replace('slash', '/', $codice);
        $i = 0;
        $magazzini = DB::SELECT('SELECT * FROM MGUbicazione WHERE Cd_MG=\'' . $magazzino_A . '\'');
        foreach ($magazzini as $m) {
            if ($m->Cd_MGUbicazione == $ubicazione_A)
                $i++;
        }
        if ($ubicazione_A == 'ND')
            $i++;
        if ($i > 0) {
            $ciao = ArcaUtilsController::aggiungi_articolo($id_ordine, $codice, $quantita, $magazzino_A, 1, $ubicazione_A, $lotto, $magazzino_P, $ubicazione_P, $taglia, $colore);

            $ordine = DB::select('SELECT * from DOTes where Id_DOtes = ' . $id_ordine)[0];

            if ($ciao != 'No Giac')
                echo 'Articolo Caricato Correttamente ';
            else
                echo 'No Giac';

        } else {
            echo 'Ubicazione inserita inesistente in quel magazzino';
            exit();
        }
    }

    /*
        public function trasporto_articolo($documento,$codice,$quantita,$magazzino,$ubicazione_P,$magazzino_A,$ubicazione_A,$fornitore,$lotto,$Id_DoTes){

            ArcaUtilsController::trasporto_articolo($codice,$documento,$quantita,$magazzino,$ubicazione_P,$magazzino_A,$ubicazione_A,$fornitore,$lotto,$Id_DoTes);

        }

        public function modifica_articolo_ordine($id_ordine,$codice,$quantita,$magazzino_A,$ubicazione_A,$lotto,$magazzino_P,$ubicazione_P){

            ArcaUtilsController::modifica_articolo($id_ordine,$codice,$quantita,$magazzino_A,1,$ubicazione_A,$lotto,$magazzino_P,$ubicazione_P);

            $ordine = DB::select('SELECT * from DOTes where Id_DOtes = '.$id_ordine)[0];

            echo 'Articolo Modificato Correttamente Ordine OAF: '.$ordine->NumeroDoc;

        }
        public function scarica_articolo_ordine($id_ordine,$codice,$quantita,$magazzino,$ubicazione,$lotto){

            ArcaUtilsController::scarica_articolo($id_ordine,$codice,$quantita,$magazzino,1,$ubicazione,$lotto);

            $ordine = DB::select('SELECT * from DOTes where Id_DOtes = '.$id_ordine)[0];

            echo 'Articolo Scaricato Correttamente : '.$ordine->NumeroDoc;

        }
    */
    public
    function cerca_articolo_smart($q, $cd_cf)
    {
        $q = str_replace("slash", "/", $q);
        $qta = 'ND';/*
            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($q);
            $where = ' where 1=1 ';
            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $testo = trim($field['content'], '*,');
                    $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';
                }
                if ($field['code'] == '310') {
                    $decimali = floatval(substr($field['raw_content'],-2));
                    $qta = floatval(substr($field['raw_content'],0,4))+$decimali/100;
                }
                if ($field['code'] == '10') {
                    $where .= ' and ARLotto.Cd_ARLotto Like \'%' . $field['content'] . '%\'';
                }

            }
            $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto on AR.Cd_AR = ARLotto.Cd_AR ' . $where . '  Order By Id_AR DESC');
*/

        $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_ARLotto LEFT JOIN ARAlias ON ARAlias.Cd_AR = AR.Cd_AR where AR.Obsoleto = 0 AND AR.Cd_AR Like \'' . $q . '%\' or  AR.Descrizione Like \'%' . $q . '%\' or AR.CD_AR IN (SELECT CD_AR from ARAlias where Alias LIKE \'%' . $q . '%\') Order By AR.Id_AR DESC');
        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            ?>
            '<?php echo $cd_cf ?>','<?php echo $articolo->Cd_AR ?>','<?php if ($articolo->Cd_ARLotto != '') echo $articolo->Cd_ARLotto; else echo '0'; ?>','<?php if ($qta != '') echo $qta; else echo '0'; ?>'
            <?php
        }
    }

    public
    function cerca_articolo_smart1($q, $cd_cf)
    {
        $q = str_replace("slash", "/", $q);
        $qta = 'ND';/*
            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($q);
            $where = ' where 1=1 ';
            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $testo = trim($field['content'], '*,');
                    $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';
                }
                if ($field['code'] == '310') {
                    $decimali = floatval(substr($field['raw_content'],-2));
                    $qta = floatval(substr($field['raw_content'],0,4))+$decimali/100;
                }
                if ($field['code'] == '10') {
                    $where .= ' and ARLotto.Cd_ARLotto Like \'%' . $field['content'] . '%\'';
                }

            }
            $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[D+escrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto on AR.Cd_AR = ARLotto.Cd_AR ' . $where . '  Order By Id_AR DESC');
*/

        $articoli = DB::select('SELECT DISTINCT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_ARLotto LEFT JOIN x_ARVRAlias ON x_ARVRAlias.Cd_AR = AR.Cd_AR where AR.Obsoleto = 0 AND AR.Cd_AR Like \'' . $q . '%\' or  AR.Descrizione Like \'%' . $q . '%\' or AR.CD_AR IN (SELECT CD_AR from x_ARVRAlias where Alias LIKE \'%' . $q . '%\') Order By AR.Id_AR DESC');
        if (sizeof($articoli) > 0) {
            $varianti = DB::SELECT('SELECT Ud_VR1  as taglia, Ud_VR2 as colore  from x_ARVRAlias where Alias = \'' . $q . '\' ');
            foreach ($articoli as $articolo) { ?>


                cerca_articolo_codice_2('<?php echo $cd_cf ?>','<?php echo $articolo->Cd_AR ?>','<?php if ($articolo->Cd_ARLotto != '') echo $articolo->Cd_ARLotto; else echo '0'; ?>','<?php if ($qta != '') echo $qta; else echo '0'; ?>','<?php echo (sizeof($varianti) > 0) ? $varianti[0]->taglia : 'ND' ?>','<?php echo (sizeof($varianti) > 0) ? $varianti[0]->colore : 'ND' ?>')

            <?php }
            /*<li class="list-group-item">
                   <a href="#" onclick="" class="media">
                       <div class="media-body"
                            onclick="">
                           <h5><?php /*echo $articolo->Descrizione; *//*?></h5>
                            <p>Codice: <?php /*echo $articolo->Cd_AR *//*?></p>
                        </div>
                    </a>
                </li>*/
        }
    }


    public
    function controllo_articolo_smart($q, $id_dotes)
    {
        /*
                $decoder = new Decoder($delimiter = '');
                $barcode = $decoder->decode($q);
                $where = ' where 1=1 ';
                foreach ($barcode->toArray()['identifiers'] as $field) {

                    if ($field['code'] == '01') {
                        $contenuto = trim($field['content'],'*,');
                        $where .= ' and Cd_AR Like \'%' . $contenuto . '%\'';

                    }
                    if ($field['code'] == '10') {
                        $where .= ' and Cd_ARLotto Like \'%' . $field['content'] . '%\'';

                    }
                    if ($field['code'] == '310') {
                        $decimali = floatval(substr($field['raw_content'],-2));
                        $qta = floatval(substr($field['raw_content'],0,4))+$decimali/100;
                        $where .= ' and Qta Like \'%' . $qta . '%\'';

                    }

                }*/
        $c = $q;
        $q = DB::SELECT('SELECT * FROM x_ARVRAlias WHERE Alias = \'' . $q . '\' ');
        if (sizeof($q) != 0) {
            $c = $q[0]->Cd_AR;
            $taglia = DB::SELECT('SELECT Descrizione as q from x_VR where Ud_x_VR = \'' . $q[0]->Ud_VR1 . '\'')[0]->q;
            $colore = DB::SELECT('SELECT Descrizione as q from x_VR where Ud_x_VR = \'' . $q[0]->Ud_VR2 . '\'')[0]->q;

            $articoli = DB::select('SELECT * FROM DoRig  d outer apply dbo.xmtf_ARVRInfo(d.x_VRData) INFODORIG WHERE INFODORIG.Ud_VR1 = \'' . $q[0]->Ud_VR1 . '\' and INFODORIG.Ud_VR2 = \'' . $q[0]->Ud_VR2 . '\' AND d.Cd_AR = \'' . $c . '\' and d.Id_DoTes in (\'' . $id_dotes . '\') Order By d.QtaEvadibile DESC');
            foreach ($articoli as $articolo) {
                ?>


                <script type="text/javascript">

                    $('#modal_controllo_articolo').val('<?php echo $articolo->Cd_AR ?>');
                    $('#modal_controllo_quantita').val(1);
                    $('#modal_controllo_lotto').val('<?php echo $articolo->Cd_ARLotto ?>');
                    $('#modal_controllo_dorig').val('<?php echo $articolo->Id_DORig . '_' . $taglia . '_' . $colore; ?>');
                    $('#modal_controllo_dorig_tc').val('<?php echo $articolo->Id_DORig ?>');


                </script>

            <?php }
        } else {
            $articoli = DB::select('SELECT * FROM DoRig WHERE Cd_AR = \'' . $c . '\' and Id_DoTes in (\'' . $id_dotes . '\') Order By QtaEvadibile DESC');
            foreach ($articoli as $articolo) {
                ?>

                <script type="text/javascript">

                    $('#modal_controllo_articolo').val('<?php echo $articolo->Cd_AR ?>');
                    $('#modal_controllo_quantita').val(<?php echo floatval($articolo->Qta) ?>);
                    $('#modal_controllo_lotto').val('<?php echo $articolo->Cd_ARLotto ?>');
                    $('#modal_controllo_dorig').val('<?php echo $articolo->Id_DORig ?>');


                </script>

            <?php }
        }

    }


    /**
     * Sezione Inventario di Magazzino
     * @return mixed
     */


    public
    function cerca_articolo_inventario($barcode)
    {


        $barcode = str_replace("-", "/", $barcode);
        $barcode = str_replace("slash", "/", $barcode);

        $taglia_selected = '';
        $colore_selected = '';
        $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_AR where AR.Cd_AR = \'' . $barcode . '\' or  AR.Descrizione = \'' . $barcode . '\' or AR.CD_AR IN (SELECT CD_AR from ARAlias where Alias = \'' . $barcode . '\')  Order By AR.Id_AR DESC');


        if (sizeof($articoli) == '0') {
            $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto ON AR.Cd_AR = ARLotto.Cd_AR where AR.Cd_AR = \'' . $barcode . '\' or  AR.Descrizione = \'' . $barcode . '\' or AR.CD_AR IN (SELECT CD_AR from x_ARVRAlias where Alias = \'' . $barcode . '\')  Order By AR.Id_AR DESC');

            if (sizeof($articoli) == '0') {
                $decoder = new Decoder($delimiter = '');
                $barcode = $decoder->decode($barcode);
                $where = ' where 1=1  ';

                foreach ($barcode->toArray()['identifiers'] as $field) {

                    if ($field['code'] == '01') {
                        $testo = trim($field['content'], '*,');
                        $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';

                    }
                    if ($field['code'] == '10') {
                        $where .= ' and ARLotto.Cd_ARLotto Like \'%' . $field['content'] . '%\'';
                        $Cd_ARLotto = $field['content'];
                    }

                }
                $articoli = DB::select('SELECT AR.[Id_AR],AR.[Cd_AR],AR.[Descrizione],ARLotto.[Cd_ARLotto] FROM AR LEFT JOIN ARLotto on AR.Cd_AR = ARLotto.Cd_AR ' . $where . '  Order By Id_AR DESC');

            } else {
                $varianti = DB::select('SELECT * from x_ARVRAlias where Alias = \'' . $barcode . '\' ');
                if (sizeof($varianti) > 0) {
                    $taglia_selected = DB::SELECT('SELECT * FROM x_VR where Ud_x_VR = \'' . $varianti[0]->Ud_VR1 . '\' ')[0]->Descrizione;
                    $colore_selected = DB::SELECT('SELECT * FROM x_VR where Ud_x_VR = \'' . $varianti[0]->Ud_VR2 . '\' ')[0]->Descrizione;
                }
            }
        }
        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            $taglie = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR1 ) as Taglia, INFOAR.Ud_VR1 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Obsoleto = \'false\' GROUP BY INFOAR.Ud_VR1 ');
            ?>
            <script type="text/javascript">
                $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                <?php if(sizeof($taglie) > 0) foreach($taglie as $t){?>
                $('#modal_taglie').append('<option taglia="<?php echo $t->Taglia?>" <?php if ($t->Taglia == $taglia_selected) echo 'selected'; ?>><?php echo $t->Taglia ?></option>')
                <?php  $colore = DB::SELECT('SELECT (Select Descrizione from x_VR WHERE Ud_x_VR = INFOAR.Ud_VR2 ) as Colore ,INFOAR.Ud_VR2 FROM AR outer apply dbo.xmtf_ARVRInfo(AR.x_VRData) INFOAR WHERE Cd_AR = \'' . $articolo->Cd_AR . '\' and INFOAR.Ud_VR1 = \'' . $t->Ud_VR1 . '\' and INFOAR.Obsoleto = \'false\' ');
                foreach ($colore as $c){
                $dateYear = date('Y');
                $query = 'SET NOCOUNT ON select
                                                isnull(xG1.Descrizione, \'UNICA\')	as Descrizione	,
                                                isnull(xG1.Riga, 0)					as Riga			,
                                                isnull(AR.x_Ud_VRGruppo2, 0x)		as Ud_VRGruppo2	,
                                                AR.Descrizione						as Ds_AR		,
                                                xGD.*
                                            into #xMGDispEx
                                            from xmtf_MGDispEx(\'' . $dateYear . '\')	xGD join AR on xGD.Cd_AR = AR.Cd_AR
                                                left join x_VRVRGruppo xG1 on xG1.Ud_VRGruppo = isnull(AR.x_Ud_VRGruppo1, 0x) and xG1.Ud_VR = isnull(xGD.Ud_VR1, 0x)
                                            order by xGD.Cd_AR

                                            create clustered index IX_MGDispEx_VR on #xMGDispEx (Cd_AR)

                                            select
                                                xGD.Cd_MGEsercizio							,
                                                xGD.Cd_AR									,
                                                xGD.Ds_AR									,
                                                xGD.Cd_MG									,
                                                isnull(xG2.Descrizione, \'UNICA\')  as Ds_VR2	,
                                                xGD.Cd_MGUbicazione							,
                                                xGD.Cd_ARLotto								,
                                                xGD.Cd_DOSottoCommessa						,
                                                sum(Quantita)		as Quantita			,
                                                sum(QuantitaDisp)		as QuantitaDisp		,
                                                sum(QuantitaDImm)		as QuantitaDImm		,
                                                sum(OrdQ)		as OrdQ				,
                                                sum(ImpQ)		as ImpQ

                                            from #xMGDispEx xGD
                                                left join x_VRVRGruppo xG2 on xG2.Ud_VRGruppo = xGD.Ud_VRGruppo2 and xG2.Ud_VR = isnull(xGD.Ud_VR2, 0x)
                                                left join AR on AR.Cd_AR =  xGD.Cd_AR
                                            where Ar.Cd_AR = \'' . $articolo->Cd_AR . '\' and xGD.Ud_VR1 = \'' . $t->Ud_VR1 . '\' and xGD.Ud_VR2 = \'' . $c->Ud_VR2 . '\'
                                            group by
                                                xGD.Cd_MGEsercizio		,
                                                xGD.Cd_AR				,
                                                xGD.Ds_AR				,
                                                xGD.Cd_MG				,
                                                xG2.Riga				,
                                                xG2.Descrizione			,
                                                xGD.Cd_MGUbicazione		,
                                                xGD.Cd_ARLotto			,
                                                xGD.Cd_DOSottoCommessa
                                            order by
                                                xGD.Cd_MGEsercizio		,
                                                xGD.Cd_AR				,
                                                xGD.Cd_MG				,
                                                xG2.Riga				,
                                                xGD.Cd_MGUbicazione		,
                                                xGD.Cd_ARLotto			,
                                                xGD.Cd_DOSottoCommessa
                                            drop table #xMGDispEx';

                $giac = DB::SELECT($query);?>
                $('#modal_colori').append('<option id="modal_colori_<?php echo $t->Taglia?>" quantita="<?php echo (sizeof($giac) > 0) ? $giac[0]->Quantita : '0' ?>" taglia="<?php echo $t->Taglia?>" colore="<?php echo str_replace(' ', '', $c->Colore) ?>"  <?php if (str_replace(' ', '', $c->Colore) == $colore_selected) echo 'selected'; ?> style="display:none"><?php echo $c->Colore ?></option>')
                <?php } ?>
                <?php } ?>
                cambioColore();
            </script>
        <?php }
    }


    public
    function rettifica_articolo($codice, $quantita, $lotto, $magazzino)
    {

        try {
            DB::beginTransaction();

            $taglia = DB::SELECT('SELECT Ud_x_VR as codice FROM x_VR where tipo = 1 and Descrizione = \'' . $_GET['taglia'] . '\' ');

            $colore = DB::SELECT('SELECT Ud_x_VR as codice FROM x_VR where tipo = 2 and Descrizione = \'' . $_GET['colore'] . '\' ');

            $check = DB::SELECT('SELECT * from dotes where Cd_MGEsercizio = YEAR(GETDATE()) and Cd_DO = \'RET\' and Cd_Cf = \'F000000\' and DataDoc = FORMAT(GETDATE(),\'yyyy-MM-dd\') ');

            if (sizeof($check) > 0)
                $id_ordine = $check[0]->Id_DoTes;
            else
                $id_ordine = DB::table('DOTes')->insertGetId(['Cd_CF' => 'F000000', 'Cd_Do' => 'RET']);

            $insert_righe_ordine['x_VRData'] =
                '<rows>
            <row ud_vr1="' . $taglia[0]->codice . '" ud_vr2="' . $colore[0]->codice . '" qta="' . $quantita . '" qtares="' . $quantita . '" />
            </rows>';

            $insert_righe_ordine['Cd_Aliquota'] = '22';
            $insert_righe_ordine['Cd_CGConto'] = '06010105001';
            $insert_righe_ordine['Cd_VL'] = 'EUR';
            $insert_righe_ordine['Qta'] = $quantita;
            $insert_righe_ordine['QtaEvadibile'] = $quantita;
            $insert_righe_ordine['Cambio'] = 1;
            $insert_righe_ordine['Id_DoTes'] = $id_ordine;
            $insert_righe_ordine['Cd_AR'] = $codice;
            $insert_righe_ordine['Cd_MG_A'] = $magazzino;
            if ($lotto != 0)
                $insert_righe_ordine['Cd_ARLotto'] = $lotto;
            $insert_righe_ordine['Cd_MGEsercizio'] = date('Y');

            DB::table('DORig')->insertGetId($insert_righe_ordine);
            ArcaUtilsController::calcola_totale_ordine($id_ordine);

            echo 'Quantit?? Rettificata con Successo';

            DB::commit();

        } catch (\PDOException $e) {
            +
                // Woopsy
            print_r($e);
            DB::rollBack();
        }


    }

    public
    function cerca_articolo_smart_inventario($q, $tipo)
    {
        $Cd_ARLotto = 'NESSUN LOTTO';
        if ($tipo == 'GS1') {

            $decoder = new Decoder($delimiter = '');
            $barcode = $decoder->decode($q);
            $where = ' where 1=1 ';

            foreach ($barcode->toArray()['identifiers'] as $field) {

                if ($field['code'] == '01') {
                    $testo = trim($field['content'], '*,');
                    $where .= ' and AR.Cd_AR Like \'%' . $testo . '%\'';

                }
                if ($field['code'] == '10') {
                    $Cd_ARLotto = $field['content'];
                }

            }

            $articoli = DB::select('SELECT [Id_AR],[Cd_AR],[Descrizione] FROM AR ' . $where . '  Order By Id_AR DESC');
            if (sizeof($articoli) > 0) {
                foreach ($articoli as $articolo) { ?>

                    <li class="list-group-item">
                        <a href="#" onclick="" class="media">
                            <div class="media-body"
                                 onclick="cerca_articolo_inventario_codice('<?php echo $articolo->Cd_AR ?>','<?php echo $Cd_ARLotto; ?>') ">
                                <h5><?php echo $articolo->Descrizione ?></h5>
                                <p>Codice: <?php echo $articolo->Cd_AR ?></p>
                            </div>
                        </a>
                    </li>

                <?php }
            } else
                echo 'Nessun Articolo Trovato';
        }
        if ($tipo == 'EAN') {
            $articoli = DB::select('SELECT [Id_AR],[Cd_AR],[Descrizione] FROM AR where (Cd_AR Like \'' . $q . '%\' or  Descrizione Like \'%' . $q . '%\' or CD_AR IN (SELECT CD_AR from ARAlias where Alias LIKE \'%' . $q . '%\'))  Order By Id_AR DESC');
            if (sizeof($articoli) > 0) {
                foreach ($articoli as $articolo) { ?>

                    <li class="list-group-item">
                        <a href="#" onclick="" class="media">
                            <div class="media-body"
                                 onclick="cerca_articolo_inventario_codice('<?php echo $articolo->Cd_AR ?>','NESSUNLOTTO')">
                                <h5><?php echo $articolo->Descrizione ?></h5>
                                <p>Codice: <?php echo $articolo->Cd_AR ?></p>
                            </div>
                        </a>
                    </li>

                <?php }
            } else
                echo 'Nessun Articolo Trovato';
        }
    }


    public
    function cerca_articolo_inventario_codice($codice, $Cd_ARLotto)
    {

        $articoli = DB::select('SELECT AR.Cd_AR from AR where Cd_AR = \'' . $codice . '\'');

        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];
            $quantita = 0;
            $disponibilita = DB::select('SELECT ISNULL(sum(QuantitaSign),0) as disponibilita from MGMOV where Cd_MGEsercizio = ' . date('Y') . ' and Cd_AR = \'' . $articolo->Cd_AR . '\'');
            if (sizeof($disponibilita) > 0) {
                $prova = DB::SELECT('SELECT ISNULL(sum(QuantitaSign),0) as disponibilita,Cd_ARLotto,Cd_MG from MGMOV where Cd_MGEsercizio = ' . date('Y') . ' and Cd_AR = \'' . $articolo->Cd_AR . '\' and Cd_ARLotto IS NOT NULL group by Cd_ARLotto, Cd_MG HAVING SUM(QuantitaSign)!= 0  ');
            }

            /* echo '<h3>Disponibilit??: ' . $quantita . '</h3>';*/
            ?>
            <script type="text/javascript">
                $('#modal_Cd_AR').val('<?php echo $articolo->Cd_AR ?>');
                $('#modal_Cd_ARLotto').html('<option value="">Nessun Lotto</option>');
                <?php foreach($prova as $l){?>
                $('#modal_Cd_ARLotto').append('<option quantita="<?php echo floatval($l->disponibilita) ?>" magazzino="<?php echo $l->Cd_MG ?>" <?php echo ($Cd_ARLotto == $l->Cd_ARLotto) ? 'selected' : '' ?>><?php echo $l->Cd_ARLotto . ' - ' . $l->Cd_MG ?></option>')
                <?php } ?>


            </script>
            <?php
        }

    }

    public
    function elimina($id_dotes)
    {
        DB::table('DoRig')->where('Id_DOTes', $id_dotes)->delete();
        DB::table('DOTes')->where('Id_DOTes', $id_dotes)->delete();
        echo 'Eliminato';
    }

    public
    function salva($id_dotes)
    {
        //DB::update("Update dotes set Modificabile = 0 where id_dotes = $id_dotes ");
    }

    public
    function invia_mail($id_dotes, $id_dorig, $testo)
    {
        if ($id_dorig == '1') {
            if (substr($testo, 0, 2) == '01') {
                $decoder = new Decoder($delimiter = '');
                $barcode = $decoder->decode($testo);
                $where = 'Articolo ';
                foreach ($barcode->toArray()['identifiers'] as $field) {

                    if ($field['code'] == '01') {
                        $contenuto = trim($field['content'], '*,');
                        $where .= $contenuto;

                    }
                    if ($field['code'] == '10') {
                        $where .= ' con lotto ' . $field['content'];

                    }
                    /*
                    if ($field['code'] == '310') {
                        $decimali = floatval(substr($field['raw_content'],-2));
                        $qta = floatval(substr($field['raw_content'],0,4))+$decimali/100;
                        $where .= ' and Qta Like \'%' . $qta . '%\'';
                    }*/

                }
                $where .= ' non trovato. ';
            }
        } else {
            if ($id_dorig == '2') {
                $testo = str_replace('*', '', $testo);
                $where = $testo;
            } else {
                $where = trim($testo, '-');
            }


        }

        if ($id_dorig == '3') {
            $documento = DB::SELECT('Select * from dotes where Id_DOTes = \'' . $id_dotes . '\'')[0]->Cd_Do;
            $testo = str_replace('(documento)', $documento, $testo);
            $where = $testo;
        }

    }


}
