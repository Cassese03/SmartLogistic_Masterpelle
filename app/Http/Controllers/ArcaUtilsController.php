<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;


/**
 * Controller utilizzate per effettuare le chiamate Ajax
 * Class AjaxController
 * @package App\Http\Controllers
 */
class ArcaUtilsController extends Controller
{


    /**
     * Aggiunge un prodotto con quantita 1 se è già presente aumenta la quantità di 1
     * @param $id_ordine
     * @return \Illuminate\Contracts\View\View
     */

    public static function calcola_totale_ordine($id_dotes)
    {

        DB::update("Update dotes set dotes.reserved_1= 'RRRRRRRRRR' where dotes.id_dotes = $id_dotes exec asp_DO_End $id_dotes");


    }

    public static function aggiungi_articolo($id_ordine, $codice, $quantita, $magazzino_A, $fornitore = 0, $ubicazione_A, $lotto, $magazzino_P, $ubicazione_P, $taglia, $colore)
    {
        try {
            $lotto = '0';
            $pedana = DB::select('SELECT * from ARLotto where Cd_AR =  \'' . $codice . '\' and Cd_ARLotto = \'' . $lotto . '\' ');
            if (sizeof($pedana) != 0)
                $pedana = $pedana[0]->xCd_xPallet;
            else
                $pedana = '0';
            if ($lotto == 'Nessun Lotto') {
                $lotto = '0';
            }
            if ($ubicazione_A == 'ND') {
                $ubicazione_A = '0';
            }
            if ($magazzino_A == 'ND') {
                $magazzino_A = '0';
            }
            if ($magazzino_P == 'ND') {
                $magazzino_P = '0';
            }
            if ($ubicazione_P == 'ND') {
                $ubicazione_P = '0';
            }
            if ($magazzino_P != '0') {
                $check_giac = DB::SELECT('
                select Cd_MG,ISNULL(SUM(Quantita),0) AS giac from xmtf_MGDisp(year(GETDATE()))
                WHERE Cd_AR = \'' . $codice . '\'
                and (SELECT x_VR.Ud_x_VR from x_VR WHERE Descrizione = \'' . $taglia . '\') = xmtf_MGDisp.Ud_VR1
                and (SELECT Ud_x_VR from x_VR WHERE Descrizione = \'' . $colore . '\') = xmtf_MGDisp.Ud_VR2
                and Cd_MG = \'' . str_replace(' ', '', $magazzino_P) . '\'
                GROUP BY Cd_MG');
                if (sizeof($check_giac) > 0) {
                    if ($check_giac[0]->giac < $quantita)
                        return 'No Giac';
                } else {
                    return 'No Giac';
                }
            }
            $nuovaRiga = null;
            $cf = DB::select('SELECT * from CF Where Cd_CF IN (SELECT Cd_CF from DOTes WHere Id_DoTes = ' . $id_ordine . ')');
            if (sizeof($cf) > 0) {
                $cf = $cf[0];
                $articoli = DB::select
                ('
                SELECT Cd_AR,Descrizione,Cd_ARMisura
                from AR
                where Cd_AR = \'' . $codice . '\';
             ');
                $RIGA = DB::SELECT('SELECT * FROM DORig where Id_DoTes = \'' . $id_ordine . '\' ORDER BY RIGA DESC');
                $id_dorig = DB::SELECT('SELECT * FROM DORig where Id_DoTes = \'' . $id_ordine . '\' ORDER BY RIGA DESC');
                $id_dotes = DB::SELECT('SELECT * FROM DOTes where Id_DoTes = \'' . $id_ordine . '\'');
                if ($RIGA == null)
                    $RIGA = '0';
                else
                    $RIGA = $RIGA[0]->Riga;
                $RIGA++;
                if (sizeof($articoli) > 0) {
                    $articolo = $articoli[0];
                    $check_riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                VR.Prezzo,
                VR.Qta as QtaVariante,
                VR.QtaRes,
                VR.Ud_VR1,VR.Ud_VR2,
                DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DOTES IN (' . $id_ordine . ')
                ORDER BY TIMEINS DESC');
                    $xml = '<rows>';
                    foreach ($check_riga as $c)
                        $xml .= '<row ud_vr1="' . $c->Ud_VR1 . '" ud_vr2="' . $c->Ud_VR2 . '" qta="' . $c->QtaVariante . '" qtares="' . $c->QtaRes . '" />';
                    $xml .= '</rows>';
                    $check_riga = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                        (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                        VR.Prezzo,
                        VR.Qta as QtaVariante,
                        VR.QtaRes,
                        VR.Ud_VR1,VR.Ud_VR2,
                        DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DOTES IN (' . $id_ordine . ') and (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) = \'' . $taglia . '\'
                        and (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) = \'' . $colore . '\'
                        ORDER BY TIMEINS DESC');
                    if (sizeof($check_riga) > 0) {
                        $ud_vr1 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $taglia . '\'')[0]->Ud_x_VR;
                        $ud_vr2 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $colore . '\'')[0]->Ud_x_VR;
                        $x_update = str_replace('<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $check_riga[0]->QtaVariante . '" qtares="' . $check_riga[0]->QtaRes . '" />', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($check_riga[0]->QtaVariante + $quantita) . '" qtares="' . ($check_riga[0]->QtaRes + $quantita) . '" />', $xml);
                        DB::table('DORig')->where('Id_DORig', $check_riga[0]->Id_DORig)->update(['x_VRData' => $x_update]);
                        DB::table('DORig')->where('Id_DORig', $check_riga[0]->Id_DORig)->update(['Qta' => $check_riga[0]->Qta + $quantita]);
                        DB::table('DORig')->where('Id_DORig', $check_riga[0]->Id_DORig)->update(['QtaEvadibile' => $check_riga[0]->QtaEvadibile + $quantita]);
                        ArcaUtilsController::calcola_totale_ordine($id_ordine);
                        return;
                    }
                    $check_riga2 = DB::SELECT('SELECT (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR1) as Taglia,
                        (SELECT descrizione from x_VR WHERE Ud_x_VR = VR.Ud_VR2) as Colore,
                        VR.Prezzo,
                        VR.Qta as QtaVariante,
                        VR.QtaRes,
                        VR.Ud_VR1,VR.Ud_VR2,
                        DORig.* FROM DORIG outer apply dbo.xmtf_DORigVRInfo(DORig.x_VRData) VR WHERE ID_DOTES IN (' . $id_ordine . ')
                        and DORig.Cd_AR = \'' . $codice . '\'
                        ORDER BY TIMEINS DESC');
                    if (sizeof($check_riga2) > 0) {
                        $ud_vr1 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $taglia . '\'')[0]->Ud_x_VR;
                        $ud_vr2 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $colore . '\'')[0]->Ud_x_VR;
                        $x_update = str_replace('</rows>', '<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . ($quantita) . '" qtares="' . (+$quantita) . '" /></rows>', $xml);
                        DB::table('DORig')->where('Id_DORig', $check_riga2[0]->Id_DORig)->update(['x_VRData' => $x_update]);
                        DB::table('DORig')->where('Id_DORig', $check_riga2[0]->Id_DORig)->update(['Qta' => $check_riga2[0]->Qta + $quantita]);
                        DB::table('DORig')->where('Id_DORig', $check_riga2[0]->Id_DORig)->update(['QtaEvadibile' => $check_riga2[0]->QtaEvadibile + $quantita]);
                        ArcaUtilsController::calcola_totale_ordine($id_ordine);
                        return;
                    }
                    if ($taglia != 'ND' && $colore != 'ND') {
                        $ud_vr1 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $taglia . '\'')[0]->Ud_x_VR;
                        $ud_vr2 = DB::SELECT('SELECT * from x_VR WHERE Descrizione = \'' . $colore . '\'')[0]->Ud_x_VR;
                        $insert_righe_ordine['x_VRData'] = '<rows>
<row ud_vr1="' . $ud_vr1 . '" ud_vr2="' . $ud_vr2 . '" qta="' . $quantita . '" qtares="' . $quantita . '" />
</rows>';
                    }
                    $insert_righe_ordine['Id_DoTes'] = $id_ordine;
                    $insert_righe_ordine['Cd_AR'] = $articolo->Cd_AR;
                    $insert_righe_ordine['Riga'] = $RIGA;
                    $insert_righe_ordine['Descrizione'] = $articolo->Descrizione;
                    $insert_righe_ordine['Cd_MGEsercizio'] = date('Y');
                    $insert_righe_ordine['Cd_ARMisura'] = $articolo->Cd_ARMisura;
                    $insert_righe_ordine['Cd_VL'] = 'EUR';
                    $insert_righe_ordine['Qta'] = $quantita;
                    $insert_righe_ordine['QtaEvadibile'] = $quantita;
                    $insert_righe_ordine['Cambio'] = 1;
                    $insert_righe_ordine['PrezzoUnitarioV'] = '';
                    /*if(str_replace(' ','',$id_dotes[0]->Cd_Do) != 'TRM'){
                        $sconto = DB::SELECT('SELECT * FROM CF WHERE Cd_CF = \'' . $cf->Cd_CF . '\'');
                        if (sizeof($sconto) != '0')
                            $insert_righe_ordine['ScontoRiga'] = $sconto[0]->Sconto;
                    }*/
                    if ($id_dotes[0]->Cd_LS_1 != '') {
                        $prezzo = DB::SELECT('SELECT * FROM LSRevisione WHERE Cd_LS = \'' . $id_dotes[0]->Cd_LS_1 . '\'');
                        if (sizeof($prezzo) != '0')
                            $prezzo = DB::SELECT('SELECT * FROM LSArticolo WHERE Id_LSRevisione =\'' . $prezzo[0]->Id_LSRevisione . '\' and Cd_AR = \'' . $codice . '\' ');
                        if (sizeof($prezzo) != '0')
                            $insert_righe_ordine['PrezzoUnitarioV'] = $prezzo[0]->Prezzo;
                    }

                    if ($insert_righe_ordine['PrezzoUnitarioV'] == '' || $id_dotes[0]->Cd_Do == 'TRM') {
                        $prezzo = DB::SELECT('SELECT * FROM DORIG WHERE Cd_AR = \'' . $codice . '\' and Cd_DO =\'BC\' Order By Id_DORig DESC ');
                        if (sizeof($prezzo) == 0)
                            $insert_righe_ordine['PrezzoUnitarioV'] = '0';
                        else
                            $insert_righe_ordine['PrezzoUnitarioV'] = $prezzo[0]->PrezzoUnitarioV;
                    }
                    $insert_righe_ordine['Cd_Aliquota'] = $cf->Cd_Aliquota;
                    if ($insert_righe_ordine['Cd_Aliquota'] == '')
                        $insert_righe_ordine['Cd_Aliquota'] = '22';
                    $insert_righe_ordine['Cd_CGConto'] = '06010105001';//DB::SELECT('SELECT * FROM IMPOSTAZIONE WHERE Id_Impostazione = \'7\'')[0]->Cd_CGConto_1;
                    $documento = DB::SELECT('SELECT * FROM DO WHERE Cd_DO = \'' . $id_dotes[0]->Cd_Do . '\'');
                    /*
                    if($documento[0]->CliFor =='C')
                    $insert_righe_ordine['Cd_CGConto'] = DB::SELECT('SELECT * FROM IMPOSTAZIONE WHERE Id_Impostazione = \'7\'')[0]->Cd_CGConto_2;
                    if($insert_righe_ordine['Cd_CGConto'] == '')
                        $insert_righe_ordine['Cd_CGConto'] = $cf->Cd_CGConto_Mastro;
                    */

                    if ($magazzino_A != 0) $insert_righe_ordine['Cd_MG_A'] = $magazzino_A;
                    if ($magazzino_P != 0) $insert_righe_ordine['Cd_MG_P'] = $magazzino_P;

                    DB::table('DORig')->insertGetId($insert_righe_ordine);
                    ArcaUtilsController::calcola_totale_ordine($id_ordine);

                }
            }
        } catch (\Exception $e) {
            return;
        }
    }

    public static function trasporto_articolo($Cd_AR, $Cd_Do, $quantita, $Cd_MG, $ubicazione_P, $Cd_Mg_A, $ubicazione_A, $Cd_Cf, $lotto, $Id_DoTes)
    {
        if ($quantita == '0')
            echo 'Impossibile trasportare la Quantita a 0';
        else {
            $condizione = 'WHERE Cd_AR = \'' . $Cd_AR . '\' and Cd_Do =\'' . $Cd_Do . '\' and Cd_MG_P = \'' . $Cd_MG . '\' and Cd_MG_A = \'' . $Cd_Mg_A . '\' and Id_DoTes = \'' . $Id_DoTes . '\' ';
            if ($lotto != '0')
                $condizione .= 'and Cd_ARLotto = \'' . $lotto . '\'';

            if ($ubicazione_A != '0')
                $condizione .= 'and Cd_MGUbicazione_A =\'' . $ubicazione_A . '\'';

            if ($ubicazione_P != '0')
                $condizione .= 'and Cd_MGUbicazione_P =\'' . $ubicazione_P . '\'';


            $esiste = DB::Select('SELECT * FROM DoRig ' . $condizione);
            if (sizeof($esiste) == 0) {
                $Id_MgMov = DB::table('DORig')->insertGetId(['Id_DOTes' => $Id_DoTes, 'Cd_DO' => $Cd_Do, 'Cd_CF' => $Cd_Cf, 'Cd_MG_P' => $Cd_MG, 'Cd_MG_A' => $Cd_Mg_A, 'Cd_AR' => $Cd_AR, 'Qta' => $quantita]);
                $Id_DoRig = DB::SELECT('SELECT * FROM MgMov WHERE Id_MgMov = \'' . $Id_MgMov . '\' ')[0]->Id_DoRig;

                DB::update("Update dotes set dotes.reserved_1 = 'RRRRRRRRRR' where dotes.id_dotes = $Id_DoRig exec asp_DO_End $Id_DoRig");

                if ($lotto != '0') {
                    DB::update("Update DoRig set Cd_ARLotto = '$lotto' where id_dorig = $Id_DoRig ");
                }
                if ($ubicazione_A != '0') {
                    DB::update("Update DoRig set Cd_MGUbicazione_A = '$ubicazione_A' where id_dorig = $Id_DoRig ");
                }
                if ($ubicazione_P != '0') {
                    DB::update("Update DoRig set Cd_MGUbicazione_P = '$ubicazione_P' where id_dorig = $Id_DoRig ");
                }
            } else {
                $quantita1 = $quantita + intval($esiste[0]->Qta);
                $Id_DoRig = $esiste[0]->Id_DORig;
                DB::update("Update DoRig set Qta = '$quantita1' where id_dorig = $Id_DoRig ");
            }
        }
    }

    public static function modifica_articolo($id_ordine, $codice_articolo, $quantita, $magazzino_A, $fornitore = 0, $ubicazione_A, $lotto, $magazzino_P, $ubicazione_P)
    {


        $cf = DB::select('SELECT * from CF Where Cd_CF IN (SELECT Cd_CF from DOTes WHere Id_DoTes = ' . $id_ordine . ')');
        if (sizeof($cf) > 0) {
            $cf = $cf[0];


            $articoli = DB::select('
                SELECT Cd_AR,Descrizione,Cd_ARMisura
                from AR
                where Cd_AR = \'' . $codice_articolo . '\';
             ');

            if (sizeof($articoli) > 0) {
                $articolo = $articoli[0];

                $insert_righe_ordine['Id_DoTes'] = $id_ordine;
                $insert_righe_ordine['Cd_AR'] = $articolo->Cd_AR;
                $insert_righe_ordine['Riga'] = 1;
                $insert_righe_ordine['Descrizione'] = $articolo->Descrizione;
                $insert_righe_ordine['Cd_MGEsercizio'] = date('Y');
                $insert_righe_ordine['Cd_ARMisura'] = $articolo->Cd_ARMisura;
                $insert_righe_ordine['Cd_VL'] = 'EUR';
                $insert_righe_ordine['Cambio'] = 1;
                $insert_righe_ordine['Qta'] = $quantita;
                $insert_righe_ordine['QtaEvadibile'] = $quantita;/*
                $insert_righe_ordine['PrezzoUnitarioV'] = $prezzo;
                $insert_righe_ordine['Cd_Aliquota_R'] = $articolo->Cd_Aliquota_V;
                $insert_righe_ordine['Cd_Aliquota'] = $articolo->Cd_Aliquota_V;
                $insert_righe_ordine['Cd_CGConto'] = $cf->Cd_CGConto_Mastro;
                $insert_righe_ordine['PrezzoTotaleV'] = $prezzo * $quantita;*/
                $insert_righe_ordine['Cd_MG_P'] = $magazzino_P;
                $insert_righe_ordine['Cd_MG_A'] = $magazzino_A;

                $esiste = DB::select('SELECT * from DORig where Id_DoTes = ' . $id_ordine . ' and Cd_AR =  \'' . $codice_articolo . '\' and Cd_ARLotto = \'' . $lotto . '\'');
                if (sizeof($esiste) == 0) {
                    $Id_MGMov = DB::table('DORig')->insertGetId($insert_righe_ordine);
                    $Id_DoRig1 = DB::select('Select * from mgmov where Id_Mgmov = \'' . $Id_MGMov . '\'');
                    $Id_DoRig = $Id_DoRig1[0]->Id_DoRig;
                } else {
                    $insert_righe_ordine['Qta'] = $quantita;
                    $insert_righe_ordine['QtaEvadibile'] = $quantita;/*
                    $insert_righe_ordine['PrezzoUnitarioV'] = $prezzo;
                    $insert_righe_ordine['PrezzoTotaleV'] = $prezzo * $quantita;*/
                    $insert_righe_ordine['Cd_MG_P'] = $magazzino_P;
                    $insert_righe_ordine['Cd_MG_A'] = $magazzino_A;
                    DB::table('DORig')->where('Id_DORig', $esiste[0]->Id_DORig)->update($insert_righe_ordine);
                    $Id_DoRig = $esiste[0]->Id_DORig;
                    if ($lotto != '0') {
                        DB::update("Update DoRig set Cd_ARLotto = '$lotto' where id_dorig = $Id_DoRig ");
                    }
                    if ($ubicazione_P != '0') {
                        DB::update("Update DoRig set Cd_MGUbicazione_P = '$ubicazione_P' where id_dorig = $Id_DoRig ");
                    }
                    if ($ubicazione_A != '0') {
                        DB::update("Update DoRig set Cd_MGUbicazione_A = '$ubicazione_A' where id_dorig = $Id_DoRig ");
                    }

                    ArcaUtilsController::calcola_totale_ordine($id_ordine);
                    exit();
                }

            }

            if ($lotto != '0') {
                DB::update("Update DoRig set Cd_ARLotto = '$lotto' where id_dorig = $Id_DoRig ");
            }
            if ($ubicazione_P != '0') {
                DB::update("Update DoRig set Cd_MGUbicazione_P = '$ubicazione_P' where id_dorig = $Id_DoRig ");
            }
            if ($ubicazione_A != '0') {
                DB::update("Update DoRig set Cd_MGUbicazione_A = '$ubicazione_A' where id_dorig = $Id_DoRig ");
            }

            ArcaUtilsController::calcola_totale_ordine($id_ordine);
        }
    }

    /**
     * Diminuisce di 1 la quantità di una riga, se la quantità è 1 elimina la riga
     * @param $id_ordine
     * @return \Illuminate\Contracts\View\View
     */
    public static function rimuovi_articolo($id_ordine, $codice_articolo)
    {


        $articoli = DB::select('
                        SELECT
                             Cd_AR
		                    ,Descrizione
		                    ,Cd_ARMisura

                        from AR
                        ');

        if (sizeof($articoli) > 0) {
            $articolo = $articoli[0];

            $esiste = DB::select('SELECT * from DORig where Id_DoTes = ' . $id_ordine . ' and Cd_AR =  \'' . $codice_articolo . '\'');
            if (sizeof($esiste) > 0) {

                if ($esiste[0]->Qta > 1) {

                    $quantita = $esiste[0]->Qta - 1;
                    $insert_righe_ordine['Qta'] = $quantita;
                    $insert_righe_ordine['QtaEvadibile'] = $quantita;
                    $insert_righe_ordine['PrezzoTotaleV'] = $articolo->Prezzo * $quantita;
                    DB::table('DORig')->where('Id_DORig', $esiste[0]->Id_DORig)->update($insert_righe_ordine);

                } else {

                    ArcaUtilsController::rimuovi_riga($id_ordine, $codice_articolo);
                }
            }
        }

        ArcaUtilsController::calcola_totale_ordine($id_ordine);

    }

    /**
     * Diminuisce di 1 la quantità di una riga, se la quantità è 1 elimina la riga
     * @param $id_ordine
     * @return \Illuminate\Contracts\View\View
     */
    public static function rimuovi_riga($id_ordine, $codice_articolo)
    {


        $articoli = DB::select('
                        SELECT
                             Cd_AR
		                    ,Descrizione
		                    ,Cd_ARMisura
		                    ,Cd_Aliquota_V

                        from AR
                        ');

        if (sizeof($articoli) > 0) {
            $esiste = DB::select('SELECT * from DORig where Id_DoTes = ' . $id_ordine . ' and Cd_AR =  \'' . $codice_articolo . '\'');
            if (sizeof($esiste) > 0) {
                DB::table('DORig')->where('Id_DORig', $esiste[0]->Id_DORig)->delete();
            }
        }

        ArcaUtilsController::calcola_totale_ordine($id_ordine);

    }


    /**
     * Diminuisce di 1 la quantità di una riga, se la quantità è 1 elimina la riga
     * @param $id_ordine
     * @return \Illuminate\Contracts\View\View
     */
    public static function modifica_riga($id_ordine, $id_riga, $prezzo, $quantita)
    {

        $esiste = DB::select('SELECT * from DORig where Id_DoTes = ' . $id_ordine . ' and Id_DORig =  ' . $id_riga);
        if (sizeof($esiste) > 0) {

            $insert_righe_ordine['Qta'] = $quantita;
            $insert_righe_ordine['PrezzoUnitarioV'] = $prezzo;
            $insert_righe_ordine['QtaEvadibile'] = $quantita;
            $insert_righe_ordine['PrezzoTotaleV'] = $prezzo * $quantita;
            DB::table('DORig')->where('Id_DORig', $esiste[0]->Id_DORig)->update($insert_righe_ordine);
        }

        ArcaUtilsController::calcola_totale_ordine($id_ordine);

    }

    public static function calcola_totali_ordine()
    {

        ini_set('max_execution_time', 0);
        $ordini = DB::select('SELECT * from DOTes');
        foreach ($ordini as $o) {

            ArcaUtilsController::calcola_totale_ordine($o->Id_DoTes);
        }
    }


}




