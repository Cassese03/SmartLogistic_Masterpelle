<?php

namespace App\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Null_;
use Spatie\GoogleCalendar\Event;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**
 * Controller principale del webticket
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{


    public function login(Request $request)
    {

        $dati = $request->all();

        $psw = '0';
        if (isset($dati['login'])) {

            $utenti = DB::select('SELECT * from Operatore where Cd_Operatore = \'' . $dati['Utente'] . '\' ');

            if (sizeof($utenti) > 0) {
                $utente = $utenti[0];
                $password = DB::SELECT('SELECT * FROM Operatore WHERE Id_Operatore = ' . $utente->Id_Operatore);

                if ($password != null)
                    $password = $password[0]->Password;

                $passInserita = DB::SELECT('Select SubString(Convert(varchar(max), HASHBYTES(\'SHA2_256\', \'' . $dati['Password'] . '\'), 1), 3, 64) as Password ');

                if (sizeof($passInserita) < 1)
                    $passInserita = DB::SELECT('Select HASHBYTES(\'SHA2_256\', \'' . $dati['Password'] . '\') as Password');

                if ($password != $passInserita[0]->Password) {
                    $ditta = DB::select('SELECT * from Ditta')[0];
                    $psw = '1';
                    return View::make('login', compact('ditta', 'psw'));
                }
                session(['utente' => $utente]);
                session()->save();
            } else {
                $ditta = DB::select('SELECT * from Ditta')[0];
                $psw = '2';
                return View::make('login', compact('ditta', 'psw'));
            }

        }
        if (session()->has('utente')) {
            return Redirect::to('');
        }

        $ditta = DB::select('SELECT * from Ditta')[0];
        return View::make('login', compact('ditta', 'psw'));
    }

    public function logout(Request $request)
    {
        session()->flush();
        return Redirect::to('login');
    }

    public function index()
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }

        $ditta = DB::select('SELECT * from Ditta')[0];

        return View::make('index', compact('ditta'));
    }

    public function cerca_id_dotes()
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }


        return View::make('cerca_id_dotes');
    }

    public function giacenza()
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }


        return View::make('giacenza');
    }

    public function prezzo()
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }


        return View::make('prezzo');
    }

    public function articoli()
    {

        if (!session()->has('utente')) {
            return Redirect::to('login');
        }

        $articoli = DB::select('SELECT TOP 10 [Id_AR],[Cd_AR],[Descrizione] FROM AR Order By Id_AR DESC');

        return View::make('articoli', compact('articoli'));
    }

    public function modifica_articolo($id, Request $request)
    {

        if (is_numeric($id)) {

            $dati = $request->all();

            if (isset($dati['modifica_articolo'])) {
                unset($dati['modifica_articolo']);

                if (isset($dati['barcode'])) $barcodes = $dati['barcode'];
                unset($dati['barcode']);
                if (isset($dati['listino'])) $listini = $dati['listino'];
                unset($dati['listino']);


                if (isset($dati['gruppi'])) {
                    list($dati['Cd_ARGruppo1'], $dati['Cd_ARGruppo2'], $dati['Cd_ARGruppo3']) = explode(';', $dati['gruppi']);
                    unset($dati['gruppi']);
                }

                if ($dati['Cd_ARGruppo1'] == '') unset($dati['Cd_ARGruppo1']);
                if ($dati['Cd_ARGruppo2'] == '') unset($dati['Cd_ARGruppo2']);
                if ($dati['Cd_ARGruppo3'] == '') unset($dati['Cd_ARGruppo3']);

                /*
                     if(isset($dati['pezzi_confezione'])){
                         DB::table('ARARMisura')->where('Cd_AR',$dati['Cd_AR'])->where('Cd_ARMisura','CT')->update(array('UMFatt' => $dati['pezzi_confezione']));
                         unset($dati['pezzi_confezione']);
                     }
                 */
                DB::table('AR')->where('Id_AR', $id)->update($dati);

                foreach ($barcodes as $chiave => $valore) {
                    if ($valore != '') {
                        $esiste = DB::select('SELECT * FROM ARAlias where Riga = \'' . $chiave . '\' and Cd_AR = \'' . $dati['Cd_AR'] . '\'');
                        if (sizeof($esiste) > 0) {
                            DB::table('ARAlias')->where('Riga', $chiave)->where('Cd_AR', $dati['Cd_AR'])->update(array('Alias' => $valore));
                        } else DB::table('ARAlias')->insert(array('Alias' => $valore, 'Riga' => $chiave, 'CD_AR' => $dati['Cd_AR']/*,'Cd_ARMisura' => 'CT'*/));
                    } else {
                        $esiste = DB::select('SELECT * FROM ARAlias where Riga = \'' . $chiave . '\' and Cd_AR = \'' . $_POST['Cd_AR'] . '\'');
                        if (sizeof($esiste) > 0) {
                            DB::table('ARAlias')->where('Riga', $chiave)->where('Cd_AR', $dati['Cd_AR'])->delete();
                        }
                    }
                }
                /*
                                if(isset($listini)) {
                                    foreach ($listini as $chiave => $valore) {
                                        DB::table('LSArticolo')->where('Id_LSArticolo', $chiave)->update(array('Prezzo' => $valore));
                                    }
                                }*/

            }

            if (isset($dati['elimina_articolo'])) {
                DB::table('ARAlias')->where('Cd_AR', $dati['Cd_AR'])->delete();
                DB::table('LSArticolo')->where('Cd_AR', $dati['Cd_AR'])->delete();
                DB::table('ARARMisura')->where('Cd_AR', $dati['Cd_AR'])->delete();
                DB::table('AR')->where('Id_AR', $id)->delete();

                return Redirect::to('articoli');
            }


            $articoli = DB::select('SELECT * FROM AR where Id_AR = ' . $id);
            if (sizeof($articoli) > 0) {
                $articolo = $articoli[0];
                $gruppi = DB::select("SELECT ARGruppo1.Cd_ARGruppo1,ARGruppo2.Cd_ARGruppo2,ARGruppo3.Cd_ARGruppo3,CONCAT(ARGruppo1.Cd_ARGruppo1,';',ARGruppo2.Cd_ARGruppo2,';',ARGruppo3.Cd_ARGruppo3) as id,
                CONCAT(ARGruppo1.Descrizione,' - ',ARGruppo2.Descrizione,' - ',ARGruppo3.Descrizione) as Descrizione from ARGruppo3
                JOIN ARGruppo2 ON ARGruppo2.Cd_ARGruppo2 = ARGruppo3.Cd_ARGruppo2
                JOIN ARGruppo1 ON ARGruppo1.Cd_ARGruppo1 = ARGruppo2.Cd_ARGruppo1");

                $aliases = DB::select('SELECT * from ARAlias where Cd_AR = \'' . $articolo->Cd_AR . '\' order by Riga ASC');

                $listini = DB::select('SELECT LSArticolo.Id_LSArticolo,LS.Cd_LS,LS.Descrizione,LSArticolo.Prezzo from LSArticolo
                    JOIN LSRevisione ON LSArticolo.id_LSRevisione = LSRevisione.Id_LSRevisione
                    JOIN LS ON LS.Cd_LS = LSRevisione.Cd_LS
                    where LSArticolo.CD_AR = \'' . $articolo->Cd_AR . '\'');

                $misure = DB::select('SELECT * FROM ARARMisura where Cd_AR = \'' . $articolo->Cd_AR . '\'');
                $gruppoAR = DB::select("SELECT *,CONCAT(Cd_ARGruppo1,';',Cd_ARGruppo2,';',Cd_ARGruppo3) as id FROM ARGRUPPO123 where Cd_ARGruppo123 = '$articolo->Cd_ARGruppo1$articolo->Cd_ARGruppo2$articolo->Cd_ARGruppo3'");
                if ($gruppoAR != null)
                    $gruppoAR = $gruppoAR[0];

                return View::make('modifica_articolo', compact('articolo', 'gruppi', 'aliases', 'listini', 'misure', 'gruppoAR'));
            }
        }
    }

    /*
        public function nuovo_articolo(Request $request){

            $dati = $request->all();

            if(isset($dati['nuovo_articolo'])){
                unset($dati['nuovo_articolo']);

                $redirect = '';
                if(isset($dati['redirect'])){
                    $redirect = $dati['redirect'];
                    unset($dati['redirect']);
                }
    /*
                $prezzo_acquisto = $dati['prezzo_acquisto'];
                $prezzo_vendita = $dati['prezzo_vendita'];
                $barcode = $dati['barcode'];
    /*
                unset($dati['prezzo_acquisto']);
                unset($dati['prezzo_vendita']);
                unset($dati['margine']);
                unset($dati['barcode']);


                DB::delete('DELETE from AR where Cd_AR = \''.$dati['Cd_AR'].'\'');/*
                DB::delete('DELETE from LSArticolo where Cd_AR = \''.$dati['Cd_AR'].'\'');
                DB::delete('DELETE from ARAlias where Cd_AR = \''.$dati['Cd_AR'].'\'');
                DB::delete('DELETE from ARARMisura where Cd_AR = \''.$dati['Cd_AR'].'\'');

    /*
                $pezzi = $dati['pezzi_confezione'];
                unset($dati['pezzi_confezione']);

                try {

                    DB::beginTransaction();
    /*
                    $dati['Cd_Aliquota_V'] = '22';
                    $id = DB::table('AR')->insertGetId($dati);

                    $lsrevisione = DB::select('SELECT * from LSRevisione where Cd_LS = \'LMP\'')[0];
                    DB::table('LSArticolo')->insert(array('Prezzo' => $prezzo_acquisto,'Id_LSRevisione' => $lsrevisione->Id_LSRevisione, 'Cd_AR' => $dati['Cd_AR']));

                    $lsrevisione = DB::select('SELECT * from LSRevisione where Cd_LS = \'LS2181\'')[0];
                    DB::table('LSArticolo')->insert(array('Prezzo' => $prezzo_vendita,'Id_LSRevisione' => $lsrevisione->Id_LSRevisione, 'Cd_AR' => $dati['Cd_AR']));



                    DB::table('ARARMisura')->insert(array('CD_AR' => $dati['Cd_AR'],'Cd_ARMisura' => 'PZ','UMFatt' => 1,'DefaultMisura' => '1','Riga' => 1));
    /*
                    DB::table('ARARMisura')->insert(array('CD_AR' => $dati['Cd_AR'],'Cd_ARMisura' => 'CN','UMFatt' => $pezzi,'DefaultMisura' => '0','Riga' => 2));

                    if($barcode != ''){
                        DB::table('ARAlias')->insert(array('Alias' => $barcode,'Riga' => 1,'Cd_AR' => $dati['Cd_AR']/* ,'Cd_ARMisura' => 'CN'));
                    }

                    DB::commit();
                } catch (\PDOException $e) {
                    //Woopsy
                    print_r($e);
                    DB::rollBack();
                }



                if($redirect == '') $redirect = 'modifica_articolo/'.$id;
                return Redirect::to($redirect);


            }

            $nuovo_codice = DB::select('SELECT ISNULL(count(*)+1,1) as nuovo_codice from AR')[0]->nuovo_codice;
            $check = DB::select('SELECT * from AR where Cd_AR=\''.$nuovo_codice.'\'');
            while(sizeof($check) != 0 ) {
                $nuovo_codice++;
                $check = DB::select('SELECT * from AR where Cd_AR=\''.$nuovo_codice.'\'');
            }
            return View::make('nuovo_articolo', compact('nuovo_codice'));



        }*/

    public function magazzino()
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }
        return Redirect::to('scegli_doc');

        $documenti = DB::SELECT('SELECT * FROM DO Where Cd_DO in (\'INV\',\'TRF\',\'TSC\',\'TSI\',\'TSS\',\'TCC\') ');

        return View::make('magazzino', compact('documenti'));
    }

    public function scegli_doc($produttore = 0, $tipo = 0, $stagione = 0)
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }
        if ($produttore == 0 && $tipo == 0 && $stagione == 0) {
            $documenti = DB::SELECT('SELECT * FROM DO Where DescrizioneBreve = \'nesusno\'');
        }
        if ($produttore != 0 && $tipo == 0 && $stagione == 0) {
            $documenti = DB::SELECT('SELECT * FROM DO Where DescrizioneBreve = \'' . $produttore . '\'');
        }
        if ($produttore != 0 && $tipo != 0 && $stagione == 0) {
            $documenti = DB::SELECT('SELECT * FROM DO Where DescrizioneBreve = \'' . $produttore . '-' . $tipo . '\'');
        }
        if ($produttore != 0 && $tipo != 0 && $stagione != 0) {
            $documenti = DB::SELECT('SELECT * FROM DO Where DescrizioneBreve = \'' . $produttore . '-' . $tipo . '-' . $stagione . '\'');
        }

        return View::make('scegli_doc', compact('documenti', 'produttore', 'tipo', 'stagione'));
    }

    public function carico_magazzino()
    {

        $documenti = DB::select('SELECT * FROM DO WHERE TipoDocumento in (\'O\',\'P\') and CliFor = \'C\'');
        return View::make('carico_magazzino', compact('documenti'));
    }

    public function carico_magazzino1($documenti)
    {


        return View::make('carico_magazzino1');
    }

    public function carico_magazzino2($documenti)
    {
        $fornitori = DB::select('SELECT TOP 10 * from CF where Id_CF in(SELECT r.Id_CF FROM DORig d,Cf r WHERE d.Cd_CF=r.Cd_CF and Cd_DO = \'' . $documenti . '\' and QtaEvadibile > \'0\' and Cd_MGEsercizio =YEAR(GETDATE()) group by r.Id_CF ) and Cliente=\'1\'');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            return View::make('carico_magazzino2', compact('documenti', 'fornitori'));
        } else {
            $fornitori = DB::select('SELECT TOP 10 * from CF WHERE Cliente=\'1\'');
            $fornitore = $fornitori[0];
            return View::make('carico_magazzino2', compact('documenti', 'fornitori'));
        }

    }

    public function carico_magazzino02($documenti)
    {
        $fornitori = DB::select('SELECT TOP 10 * from CF where Id_CF in(SELECT r.Id_CF FROM DOTes d,Cf r WHERE d.Cd_CF = r.Cd_CF and Cd_DO = \'' . $documenti . '\' and RigheEvadibili > \'0\' and Cd_MGEsercizio =YEAR(GETDATE())  group by r.Id_CF ) and Fornitore=\'1\'');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            return View::make('carico_magazzino02', compact('documenti', 'fornitori'));
        } else {
            $fornitori = DB::select('SELECT TOP 10 * from CF WHERE Fornitore=\'1\'');
            $fornitore = $fornitori[0];
            return View::make('carico_magazzino2', compact('documenti', 'fornitori'));
        }

    }

    public function carico_magazzino3($id_fornitore, $cd_do)
    {

        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore . ' order by Id_CF desc');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $documenti = DB::select(' SELECT TOP 10 * from DOTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO = \'' . $cd_do . '\' and RigheEvadibili > \'0\' order by Id_DOTes DESC');
            $numero_documento = DB::select('SELECT MAX(numeroDoc)+1 as num from DOTes WHERE Cd_MGEsercizio = YEAR(GETDATE()) and Cd_DO = \'' . $cd_do . '\' ')[0]->num;
            return View::make('carico_magazzino3', compact('fornitore', 'documenti', 'cd_do', 'numero_documento'));

        }
    }

    public function carico_magazzino3_tot($id_fornitore, $cd_do)
    {

        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore . ' order by Id_CF desc');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $documenti = DB::select('SELECT * from DOTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO = \'' . $cd_do . '\' and RigheEvadibili > \'0\' order by Id_DOTes DESC');
            $numero_documento = DB::select('SELECT MAX(numeroDoc)+1 as num from DOTes WHERE Cd_MGEsercizio = YEAR(GETDATE()) and Cd_DO = \'' . $cd_do . '\'  ')[0]->num;
            return View::make('carico_magazzino3_tot', compact('fornitore', 'documenti', 'cd_do', 'numero_documento'));

        }
    }

    public function carico_magazzino03($id_fornitore, $cd_do)
    {
        $cond = '';
        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore . ' order by Id_CF desc');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $documenti = DB::select('SELECT TOP 10 [Id_DoTes],[NumeroDoc],[DataDoc],[NumeroDocRif],[DataDocRif]  from DOTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO = \'' . $cd_do . '\' AND  DATEDIFF(DAY,GETDATE(),TimeIns) > -7 order by Id_DOTes DESC');
            $numero_documento = DB::select('SELECT MAX(numeroDoc)+1 as num from DOTes WHERE Cd_MGEsercizio = YEAR(GETDATE()) and Cd_DO = \'' . $cd_do . '\'')[0]->num;
            $dodo = DB::SELECT('select * from DODOPrel where Cd_DO = \'' . $cd_do . '\'');
            foreach ($dodo as $d) {
                $cond .= ', \'' . $d->Cd_DO_Prelevabile . '\' ';
            }
            $doc_evadi = DB::SELECT('SELECT * FROM DoTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO in (\'\'' . $cond . ') and RigheEvadibili >\'0\' order by Id_DoTes desc ');
            return View::make('carico_magazzino03', compact('fornitore', 'documenti', 'cd_do', 'numero_documento', 'doc_evadi', 'id_fornitore'));

        }
    }

    public function carico_magazzino03_tot($id_fornitore, $cd_do)
    {
        $cond = '';
        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore . ' order by Id_CF desc');
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $documenti = DB::select('SELECT * from DOTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO = \'' . $cd_do . '\' AND  DATEDIFF(DAY,GETDATE(),TimeIns) > -7 order by Id_DOTes DESC');
            $numero_documento = DB::select('SELECT MAX(numeroDoc)+1 as num from DOTes WHERE Cd_MGEsercizio = YEAR(GETDATE()) and Cd_DO = \'' . $cd_do . '\' ')[0]->num;
            $dodo = DB::SELECT('select * from DODOPrel where Cd_DO = \'' . $cd_do . '\'');
            foreach ($dodo as $d) {
                $cond .= ', \'' . $d->Cd_DO_Prelevabile . '\' ';
            }
            $doc_evadi = DB::SELECT('SELECT * FROM DoTes where Cd_CF = \'' . $fornitore->Cd_CF . '\' and Cd_DO in (\'\'' . $cond . ')  and RigheEvadibili >\'0\' order by Id_DoTes desc ');

            return View::make('carico_magazzino03_tot', compact('fornitore', 'documenti', 'cd_do', 'numero_documento', 'doc_evadi', 'id_fornitore'));

        }
    }

    public function carico_magazzino4($id_fornitore, $id_dotes, Request $request)
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }
        $dati = $request->all();
        if (isset($dati['change_mg_session'])) {
            if (isset($dati['doc_evadi'])) {
                $check_mg = DB::SELECT('SELECT * FROM MGCausale where Cd_MGCausale = (select Cd_MGCausale from do where Cd_Do =  \'' . $dati['doc_evadi'] . '\')');
                if (sizeof($check_mg) > 0) {
                    if ($check_mg[0]->Cd_MG_A != null)
                        $dati['cd_mg_a'] = $check_mg[0]->Cd_MG_A;
                    if ($check_mg[0]->Cd_MG_P != null)
                        $dati['cd_mg_p'] = $check_mg[0]->Cd_MG_P;
                }
            }

            session(['\'' . $id_dotes . '\'' => array('cd_mg_a' => $dati['cd_mg_a'], 'cd_mg_p' => $dati['cd_mg_p'], 'doc_evadi' => $dati['doc_evadi'])]);
            session()->save();
            return Redirect::to('magazzino/carico4/' . $id_fornitore . '/' . $id_dotes);
        }
        if (isset($dati['elimina_riga'])) {
            unset($dati['elimina_riga']);
            $valori = array_values($dati);
            $valoreCancellare = $valori[0];
            $dorig = DB::SELECT('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                    VR.Qta as QtaVariante,
                    VR.QtaRes,
                    VR.Ud_VR1,VR.Ud_VR2
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dorig = \'' . $valoreCancellare . '\'');
            foreach ($dorig as $d) {
                if (isset($dati[$d->Id_DORig . '_' . str_replace(' ', '_', $d->Taglia) . '_' . str_replace(' ', '_', $d->Colore)])) {
                    $old_xml = '<rows>';
                    foreach ($dorig as $c) {
                        $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    }
                    $old_xml .= '</rows>';

                    $x_update = str_replace('<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . $d->QtaVariante . '" qtares="' . $d->QtaRes . '" />', '', $old_xml);
                    if ($x_update == '<rows></rows>')
                        DB::table('DoRig')->where('Id_DORig', $valoreCancellare)->delete();
                    else
                        DB::table('DoRig')->where('Id_DORig', $d->Id_DORig)->update(['x_VRData' => $x_update]);
                }
            }
            $check_qta = DB::SELECT('select DORIG.Id_DORig,
                    SUM(VR.Qta) as QtaVariante,
                    SUM(VR.QtaRes) AS QtaRes
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\' and Id_DoRig = \'' . $valoreCancellare . '\' GROUP BY DORIG.Id_DORig');

            if (sizeof($check_qta) > 0)
                foreach ($check_qta as $c)
                    DB::table('DoRig')->where('Id_DORig', $c->Id_DORig)->update(['Qta' => $c->QtaVariante, 'QtaEvadibile' => $c->QtaRes]);


            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes");
            DB::statement("exec asp_DO_End $id_dotes");

            return Redirect::to('magazzino/carico4/' . $id_fornitore . '/' . $id_dotes);
        }
        if (isset($dati['modifica_riga'])) {

            unset($dati['modifica_riga']);
            unset($dati['Id_DORig']);
            unset($dati['modal_lotto_m']);

            $dorig = DB::SELECT('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                    VR.Qta as QtaVariante,
                    VR.QtaRes,
                    VR.Ud_VR1,VR.Ud_VR2
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\'');

            foreach ($dorig as $d) {
                if (isset($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig])) {
                    $old_xml = '<rows>';
                    foreach ($dorig as $c) {
                        $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    }
                    $old_xml .= '</rows>';

                    $x_update = str_replace('<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . $d->QtaVariante . '" qtares="' . $d->QtaRes . '" />',
                        '<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . ($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig]) . '" qtares="' . ($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig] - ($d->QtaVariante - $d->QtaRes)) . '.00000000" />',
                        $old_xml);

                    DB::table('DoRig')->where('Id_DORig', $d->Id_DORig)->update(['x_VRData' => $x_update]);
                }
            }
            $check_qta = DB::SELECT('select DORIG.Id_DORig,
                    SUM(VR.Qta) as QtaVariante,
                    SUM(VR.QtaRes) AS QtaRes
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\' GROUP BY DORIG.Id_DORig');
            foreach ($check_qta as $c)
                DB::table('DoRig')->where('Id_DORig', $c->Id_DORig)->update(['Qta' => $c->QtaVariante, 'QtaEvadibile' => $c->QtaRes]);

            //DB::table('DoRig')->where('Id_DoRig', $id_riga)->update(['Cd_ARLotto' => Null]);
            //DB::table('DoRig')->where('Id_DoRig', $id_riga)->update(['Cd_MGUbicazione_A' => Null]);

            //DB::table('DoRig')->where('Id_DORig', $id_riga)->update($dati);

            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes");
            DB::statement("exec asp_DO_End $id_dotes");

            return Redirect::to('magazzino/carico4/' . $id_fornitore . '/' . $id_dotes);
        }
        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore);
        $documenti = DB::select('SELECT * from DOTes where Id_DoTes in (' . $id_dotes . ')');
        $cd_do = DB::select('SELECT * from DOTes where Id_DoTes  in (' . $id_dotes . ')')[0]->Cd_Do;
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $date = date('d/m/Y', strtotime('today'));
            foreach ($documenti as $documento)
                $documento->righe = DB::select('SELECT
                (SELECT Alias from x_ARVRAlias WHERE Cd_AR = DORig.Cd_AR and Ud_VR1 = VR.Ud_VR1 and Ud_VR2 = VR.Ud_VR2) AS xAlias,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Ud_VR1,
                VR.Ud_VR2,
                (SELECT Riga from x_VRVRGruppo where Ud_VR = VR.Ud_VR1) AS xRiga,
                VR.Prezzo,
                VR.Qta as QtaVariante, VR.QtaRes,DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR where Id_DoTes in (' . $id_dotes . ') and VR.Qta > \'0\' ORDER BY Cd_AR,Colore,xRiga');
            $session_mag = session('\'' . $id_dotes . '\'');
            $flusso = DB::SELECT('select * from DODOPrel where Cd_DO_Prelevabile =\'' . $cd_do . '\' ORDER BY TIMEINS DESC ');
            if (sizeof($flusso) > 0) {
                if (!session()->has('\'' . $id_dotes . '\'')) {
                    $check_mg = DB::SELECT('SELECT * FROM MGCausale where Cd_MGCausale = (select Cd_MGCausale from do where Cd_Do =  \'' . $flusso[0]->Cd_DO . '\')');
                    if (sizeof($check_mg) > 0) {
                        $session = array('cd_mg_a' => $check_mg[0]->Cd_MG_A, 'cd_mg_p' => $check_mg[0]->Cd_MG_P, 'doc_evadi' => $flusso[0]->Cd_DO);
                    }
                    session(['\'' . $id_dotes . '\'' => $session]);
                    session()->save();
                } else {
                    $session = session('\'' . $id_dotes . '\'');
                    if ($session['doc_evadi'] == null) {
                        $check_mg = DB::SELECT('SELECT * FROM MGCausale where Cd_MGCausale = (select Cd_MGCausale from do where Cd_Do =  \'' . $flusso[0]->Cd_DO . '\')');
                        if (sizeof($check_mg) > 0) {
                            $session = array('cd_mg_a' => $check_mg[0]->Cd_MG_A, 'cd_mg_p' => $check_mg[0]->Cd_MG_P, 'doc_evadi' => $flusso[0]->Cd_DO);
                        }
                        session(['\'' . $id_dotes . '\'' => $session]);
                        session()->save();
                    }
                }
            }
            $magazzini_selected = DB::SELECT('SELECT * from MGCausale where Cd_MGCausale = (SELECT TOP 1 Cd_MGCausale FROM DO where cd_do = \'' . $cd_do . '\')');
            $magazzini = DB::SELECT('SELECT * from MG');
            if (!session()->has('\'' . $id_dotes . '\'')) {
                if ($magazzini_selected > 0) {
                    $session = array('cd_mg_a' => $magazzini_selected[0]->Cd_MG_A, 'cd_mg_p' => $magazzini_selected[0]->Cd_MG_P, 'doc_evadi' => '');
                } else {
                    $session = array('cd_mg_a' => '', 'cd_mg_p' => '', 'doc_evadi' => '');
                }
                session(['\'' . $id_dotes . '\'' => $session]);
                session()->save();
            }
            $session_mag = session('\'' . $id_dotes . '\'');
            foreach ($documento->righe as $r) {
                $r->lotti = DB::select('SELECT * FROM ARLotto WHERE Cd_AR = \'' . $r->Cd_AR . '\' AND DataScadenza > \'' . $date . '\' ORDER BY TimeIns DESC');
                if ($session_mag == null) {
                    $giacenza = DB::select('select TOP 1 ISNULL(SUM(Quantita),0) as Quantita
                                                from xmtf_MGDispEx(YEAR(GETDATE()))	xGD join AR on xGD.Cd_AR = AR.Cd_AR
                                                left join x_VRVRGruppo xG1 on xG1.Ud_VRGruppo = isnull(AR.x_Ud_VRGruppo1, 0x) and xG1.Ud_VR = isnull(xGD.Ud_VR1, 0x)
                                                WHERE AR.Cd_AR = \'' . $r->Cd_AR . '\' and xGD.Ud_VR1 = \'' . $r->Ud_VR1 . '\' and xGD.Ud_VR2 = \'' . $r->Ud_VR2 . '\'');
                    if (sizeof($giacenza) > 0)
                        $r->giacenza = $giacenza[0]->Quantita;
                    else
                        $r->giacenza = 0;

                } else {
                    $giacenza = DB::select('select TOP 1 ISNULL(Quantita,0) as Quantita
                                                from xmtf_MGDispEx(YEAR(GETDATE()))	xGD join AR on xGD.Cd_AR = AR.Cd_AR
                                                left join x_VRVRGruppo xG1 on xG1.Ud_VRGruppo = isnull(AR.x_Ud_VRGruppo1, 0x) and xG1.Ud_VR = isnull(xGD.Ud_VR1, 0x)
                                                WHERE AR.Cd_AR = \'' . $r->Cd_AR . '\' and xGD.Ud_VR1 = \'' . $r->Ud_VR1 . '\' and xGD.Ud_VR2 = \'' . $r->Ud_VR2 . '\' and cd_mg = \'' . $session_mag["cd_mg_p"] . '\'');
                    if (sizeof($giacenza) > 0)
                        $r->giacenza = $giacenza[0]->Quantita;
                    else
                        $r->giacenza = 0;
                }
            }
            $righe = DB::select('SELECT count(Riga) as Righe from DORig where Id_DoTes in (' . $id_dotes . ') and QtaEvadibile > \'0\'')[0]->Righe;
            $articolo = DB::select('SELECT Cd_AR from DORig where Id_DoTes in (' . $id_dotes . ') group by Cd_AR');

            return View::make('carico_magazzino4', compact('magazzini_selected', 'session_mag', 'magazzini', 'fornitore', 'id_dotes', 'documento', 'articolo', 'flusso', 'righe'));

        }

    }

    public function carico_magazzino04($id_fornitore, $id_dotes, Request $request)
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }
        $dati = $request->all();
        if (isset($dati['change_mg_session'])) {

            session(['\'' . $id_dotes . '\'' => array('cd_mg_a' => $dati['cd_mg_a'], 'cd_mg_p' => $dati['cd_mg_p'], 'doc_evadi' => '')]);
            session()->save();

            return Redirect::to('magazzino/carico04/' . $id_fornitore . '/' . $id_dotes);
        }
        if (isset($dati['elimina_riga'])) {
            unset($dati['elimina_riga']);
            $valori = array_values($dati);
            $valoreCancellare = $valori[0];
            $dorig = DB::SELECT('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                    VR.Qta as QtaVariante,
                    VR.QtaRes,
                    VR.Ud_VR1,VR.Ud_VR2
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dorig = \'' . $valoreCancellare . '\'');
            foreach ($dorig as $d) {
                if (isset($dati[$d->Id_DORig . '_' . str_replace(' ', '_', $d->Taglia) . '_' . str_replace(' ', '_', $d->Colore)])) {
                    $old_xml = '<rows>';
                    foreach ($dorig as $c) {
                        $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    }
                    $old_xml .= '</rows>';

                    $x_update = str_replace('<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . $d->QtaVariante . '" qtares="' . $d->QtaRes . '" />', '', $old_xml);
                    if ($x_update == '<rows></rows>')
                        DB::table('DoRig')->where('Id_DORig', $valoreCancellare)->delete();
                    else
                        DB::table('DoRig')->where('Id_DORig', $d->Id_DORig)->update(['x_VRData' => $x_update]);
                }
            }
            $check_qta = DB::SELECT('select DORIG.Id_DORig,
                    SUM(VR.Qta) as QtaVariante,
                    SUM(VR.QtaRes) AS QtaRes
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\' and Id_DoRig = \'' . $valoreCancellare . '\' GROUP BY DORIG.Id_DORig');

            if (sizeof($check_qta) > 0)
                foreach ($check_qta as $c)
                    DB::table('DoRig')->where('Id_DORig', $c->Id_DORig)->update(['Qta' => $c->QtaVariante, 'QtaEvadibile' => $c->QtaRes]);


            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes");
            DB::statement("exec asp_DO_End $id_dotes");

            return Redirect::to('magazzino/carico04/' . $id_fornitore . '/' . $id_dotes);
        }
        if (isset($dati['modifica_riga'])) {

            unset($dati['modifica_riga']);
            unset($dati['Id_DORig']);
            unset($dati['modal_lotto_m']);
            /*
            $dati['Cd_MGUbicazione_A'] = $dati['modal_ubicazione_A_m'];
            unset($dati['modal_ubicazione_A_m']);

            if($dati['Cd_MGUbicazione_A']=='')
            {
                unset($dati['Cd_MGUbicazione_A']);
            }
            */

            $dorig = DB::SELECT('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                    VR.Qta as QtaVariante,
                    VR.QtaRes,
                    VR.Ud_VR1,VR.Ud_VR2
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\'');

            foreach ($dorig as $d) {
                if (isset($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig])) {
                    $dorig2 = DB::SELECT('select *,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                    (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                    VR.Qta as QtaVariante,
                    VR.QtaRes,
                    VR.Ud_VR1,VR.Ud_VR2
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\'');

                    $old_xml = '<rows>';
                    foreach ($dorig2 as $c) {
                        $old_xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    }
                    $old_xml .= '</rows>';

                    $x_update = str_replace('<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . $d->QtaVariante . '" qtares="' . $d->QtaRes . '" />',
                        '<row ud_vr1="' . $d->Ud_VR1 . '" ud_vr2="' . $d->Ud_VR2 . '" qta="' . ($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig]) . '" qtares="' . ($dati[$d->Colore . '_' . $d->Taglia . '_' . 'qta_' . $d->Id_DORig] - ($d->QtaVariante - $d->QtaRes)) . '.00000000" />',
                        $old_xml);

                    DB::table('DoRig')->where('Id_DORig', $d->Id_DORig)->update(['x_VRData' => $x_update]);
                }
            }
            $check_qta = DB::SELECT('select DORIG.Id_DORig,
                    SUM(VR.Qta) as QtaVariante,
                    SUM(VR.QtaRes) AS QtaRes
                    FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR
                    where id_dotes = \'' . $id_dotes . '\' GROUP BY DORIG.Id_DORig');
            foreach ($check_qta as $c)
                DB::table('DoRig')->where('Id_DORig', $c->Id_DORig)->update(['Qta' => $c->QtaVariante, 'QtaEvadibile' => $c->QtaRes]);


            //DB::table('DoRig')->where('Id_DoRig', $id_riga)->update(['Cd_ARLotto' => Null]);
            //DB::table('DoRig')->where('Id_DoRig', $id_riga)->update(['Cd_MGUbicazione_A' => Null]);

            //DB::table('DoRig')->where('Id_DORig', $id_riga)->update($dati);

            DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes");
            DB::statement("exec asp_DO_End $id_dotes");

            return Redirect::to('magazzino/carico04/' . $id_fornitore . '/' . $id_dotes);
        }
        $fornitori = DB::select('SELECT * from CF where Id_CF = ' . $id_fornitore);
        $documenti = DB::select('SELECT * from DOTes where Id_DoTes in (' . $id_dotes . ')');
        $cd_do = DB::select('SELECT * from DOTes where Id_DoTes  in (' . $id_dotes . ')')[0]->Cd_Do;
        if (sizeof($fornitori) > 0) {
            $fornitore = $fornitori[0];
            $date = date('d/m/Y', strtotime('today'));
            foreach ($documenti as $documento)
                $documento->righe = DB::select('SELECT
                                                        (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                                                        (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                                                        (SELECT Riga from x_VRVRGruppo where Ud_VR = VR.Ud_VR1) AS xRiga,
                                                        VR.Prezzo,
                                                        VR.Qta as QtaVariante, VR.QtaRes,DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR where Id_DoTes in (' . $id_dotes . ') and VR.Qta > \'0\'  ORDER BY Cd_AR,Colore,xRiga');

            foreach ($documento->righe as $r) {
                $r->lotti = DB::select('SELECT * FROM ARLotto WHERE Cd_AR = \'' . $r->Cd_AR . '\'  AND DataScadenza > \'' . $date . '\' ORDER BY TimeIns DESC ');
            }

            /* $totali_documento = DB::select('SELECT * from DoTotali where Id_DoTes = \''.$id_dotes.'\'');
             if(sizeof($totali_documento) > 0) {
                 $documento->imponibile = $totali_documento[0]->TotImponibileE;
                 $documento->imposta = $totali_documento[0]->TotImpostaE;
                 $documento->totale = $totali_documento[0]->TotaPagareE;
             }*/
            $articolo = DB::select('SELECT Cd_AR from DORig where Id_DoTes in (' . $id_dotes . ') group by Cd_AR');
            $flusso = DB::SELECT('select * from DODOPrel where Cd_DO_Prelevabile =\'' . $cd_do . '\'  ORDER BY TIMEINS DESC ');
            $magazzini_selected = DB::SELECT('SELECT * from MGCausale where Cd_MGCausale = (SELECT TOP 1 Cd_MGCausale FROM DO where cd_do = \'' . $cd_do . '\')');
            $magazzini = DB::SELECT('SELECT * from MG');
            if (!session()->has('\'' . $id_dotes . '\'')) {
                if ($magazzini_selected > 0) {
                    $session = array('cd_mg_a' => $magazzini_selected[0]->Cd_MG_A, 'cd_mg_p' => $magazzini_selected[0]->Cd_MG_P, 'doc_evadi' => '');
                } else {
                    $session = array('cd_mg_a' => '', 'cd_mg_p' => '', 'doc_evadi' => '');
                }
                session(['\'' . $id_dotes . '\'' => $session]);
                session()->save();
            }
            $session_mag = session('\'' . $id_dotes . '\'');
            return View::make('carico_magazzino04', compact('session_mag', 'magazzini_selected', 'magazzini', 'fornitore', 'id_dotes', 'documento', 'articolo'));

        }


    }

    public function inventario_magazzino(Request $request)
    {
        if (!session()->has('utente')) {
            return Redirect::to('login');
        }
        $dati = $request->all();

        if (isset($dati['rettifica'])) {
            $primo_carico = DB::select('SELECT * from MGMov where Cd_AR = \'' . $dati['Cd_AR'] . '\' and Ini = 1');
            if (sizeof($primo_carico) > 0) {
                DB::insert('INSERT INTO MGMov (DataMov,PartenzaArrivo,Cd_MGEsercizio,Cd_AR,Cd_MG,Id_MGMovDes,Quantita,Ret) VALUES(\'20200101\',\'\',2020,\'' . $dati['Cd_AR'] . '\',\'00001\',27,' . $dati['quantita'] . ',1)');
            } else DB::insert('INSERT INTO MGMov (DataMov,PartenzaArrivo,Cd_MGEsercizio,Cd_AR,Cd_MG,Id_MGMovDes,Quantita,Ini) VALUES(\'20200101\',\'\',2020,\'' . $dati['Cd_AR'] . '\',\'00001\',27,' . $dati['quantita'] . ',1)');

        }

        return View::make('inventario_magazzino');
    }

    public function phpinfo()
    {
        phpinfo();
    }

    public function calcola_totali_ordine()
    {
        ArcaUtilsController::calcola_totali_ordine();
    }


}
