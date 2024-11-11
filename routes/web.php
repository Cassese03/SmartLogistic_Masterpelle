<?php

use Illuminate\Support\Facades\Route;

//use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::any('', 'HomeController@index');
Route::any('login', 'HomeController@login');
Route::any('logout', 'HomeController@logout');


Route::any('articoli', 'HomeController@articoli');
Route::any('modifica_articolo/{id}', 'HomeController@modifica_articolo');
//Route::any('nuovo_articolo', 'HomeController@nuovo_articolo');


Route::any('magazzino', 'HomeController@magazzino');


Route::any('ordini', 'HomeController@ordini');
Route::any('scegli_doc', 'HomeController@scegli_doc');
Route::any('scegli_doc/{produttore}', 'HomeController@scegli_doc');
Route::any('scegli_doc/{produttore}/{tipo}', 'HomeController@scegli_doc');
Route::any('scegli_doc/{produttore}/{tipo}/{stagione}', 'HomeController@scegli_doc');
Route::any('id_dotes/{id_dotes}', 'HomeController@id_dotes');
Route::any('cerca_id_dotes', 'HomeController@cerca_id_dotes');
Route::any('giacenza', 'HomeController@giacenza');
Route::any('prezzo', 'HomeController@prezzo');
Route::any('magazzino/altri', 'HomeController@altri');

Route::any('ajax/evadi_articolo2/{dorig}', 'AjaxController@evadi_articolo2');
Route::any('ajax/conferma_righe/{dorig}/{cd_mg_a}/{cd_mg_p}/{cd_do}', 'AjaxController@conferma_righe');
Route::any('ajax/cerca_documento/{id_dotes}', 'AjaxController@cerca_documento');

Route::any('magazzino/carico', 'HomeController@carico_magazzino');
Route::any('magazzino/carico2/{cd_do}', 'HomeController@carico_magazzino2');
Route::any('magazzino/carico3/{id_fornitore}/{cd_do}', 'HomeController@carico_magazzino3');
Route::any('magazzino/carico3_tot/{id_fornitore}/{cd_do}', 'HomeController@carico_magazzino3_tot');
Route::any('magazzino/carico4/{id_fornitore}/{id_dotes}', 'HomeController@carico_magazzino4');
Route::any('magazzino/carico1/{cd_do}', 'HomeController@carico_magazzino1');
Route::any('magazzino/carico02/{cd_do}', 'HomeController@carico_magazzino02');
Route::any('magazzino/carico03/{id_fornitore}/{cd_do}', 'HomeController@carico_magazzino03');
Route::any('magazzino/carico03_tot/{id_fornitore}/{cd_do}', 'HomeController@carico_magazzino03_tot');
Route::any('magazzino/carico04/{id_fornitore}/{id_dotes}', 'HomeController@carico_magazzino04');

Route::any('magazzino/inventario', 'HomeController@inventario_magazzino');
Route::any('calcola_totali_ordine', 'HomeController@calcola_totali_ordine');

Route::any('ajax/cerca_articolo/{q}', 'AjaxController@cerca_articolo');
Route::any('ajax/cerca_articolo/prezzo/{q}', 'AjaxController@cerca_articolo_prezzo');
Route::any('ajax/cerca_articolo/giacenza/{q}', 'AjaxController@cerca_articolo_giacenza');
Route::any('ajax/gestiscixml/{id_dotes}', 'AjaxController@GestisciXML');
Route::any('ajax/check_next_doc/{id_dotes}', 'AjaxController@check_next_doc');
Route::any('ajax/cerca_articolo_trasporto/{q}', 'AjaxController@cerca_articolo_trasporto');
Route::any('ajax/cerca_articolo_new/{q}/{dest}/{forn}', 'AjaxController@cerca_articolo_new');
Route::any('ajax/cerca_fornitore/{q}', 'AjaxController@cerca_fornitore');
Route::any('ajax/cerca_fornitore_new/{q}/{dest}', 'AjaxController@cerca_fornitore_new');
Route::any('ajax/cerca_cliente/{q}', 'AjaxController@cerca_cliente');
Route::any('ajax/cerca_cliente_new/{q}/{dest}', 'AjaxController@cerca_cliente_new');
Route::any('ajax/cerca_fornitore', 'AjaxController@cerca_fornitore');
Route::any('ajax/cerca_cliente', 'AjaxController@cerca_cliente');

Route::any('ajax/cerca_articolo_inventario/{barcode}', 'AjaxController@cerca_articolo_inventario');
Route::any('ajax/cerca_articolo_inventario_codice/{codice}/{arlotto}', 'AjaxController@cerca_articolo_inventario_codice');
Route::any('ajax/rettifica_articolo/{codice}/{quantita}/{lotto}/{magazzino}', 'AjaxController@rettifica_articolo');
Route::any('ajax/cerca_articolo_smart_inventario/{q}/{tipo}', 'AjaxController@cerca_articolo_smart_inventario');


Route::any('ajax/inserisci_lotto/{lotto}/{cd_ar}/{fornitore}/{descrizione}/{fornitore_pallet}/{pallet}', 'AjaxController@inserisci_lotto');
Route::any('ajax/visualizza_lotti/{cd_ar}', 'AjaxController@visualizza_lotti');
Route::any('ajax/elimina/{id_dotes}', 'AjaxController@elimina');
Route::any('ajax/salva/{id_dotes}', 'AjaxController@salva');
//Route::any('ajax/storialotto/{cd_ar}/{lotto}', 'AjaxController@storialotto');
Route::any('ajax/segnalazione/{dotes}/{dorig}/{testo}', 'AjaxController@segnalazione');
Route::any('ajax/invia_mail/{dotes}/{dorig}/{testo}', 'AjaxController@invia_mail');
Route::any('ajax/segnalazione_salva/{dotes}/{dorig}/{testo}', 'AjaxController@segnalazione_salva');
Route::any('ajax/cerca_articolo_barcode/{cd_cf}/{barcode}', 'AjaxController@cerca_articolo_barcode');
//Route::any('ajax/evadi_documento/{dotes}/{cd_do}/{magazzino_A}', 'AjaxController@evadi_documento');
//Route::any('ajax/evadi_documento1/{dotes}/{cd_do}/{magazzino_A}', 'AjaxController@evadi_documento1');
Route::any('ajax/salva_documento1/{dotes}/{cd_do}/{magazzino_A}', 'AjaxController@salva_documento1');
Route::any('ajax/evadi_articolo/{dorig}/{qtaevasa}/{magazzino}/{ubicazione}/{lotto}/{cd_cf}/{documento}/{cd_ar}/{magazzino_A}', 'AjaxController@evadi_articolo');
Route::any('ajax/evadi_articolo2/{dorig}/{qtaevasa}/{magazzino}/{ubicazione}/{lotto}/{cd_cf}/{documento}/{cd_ar}/{magazzino_A}', 'AjaxController@evadi_articolo2');
Route::any('ajax/cerca_articolo_codice/{cd_cf}/{codice}/{Cd_ARLotto}/{qta}', 'AjaxController@cerca_articolo_codice');
Route::any('ajax/cerca_articolo_codice_2/{cd_cf}/{codice}/{Cd_ARLotto}/{qta}/{taglia}/{colore}', 'AjaxController@cerca_articolo_codice_2');
Route::any('ajax/aggiungi_articolo_ordine/{id_ordine}/{codice}/{quantita}/{magazzino_A}/{ubicazione_A}/{lotto}/{magazzino_P}/{ubicazione_P}/{taglia}/{colore}', 'AjaxController@aggiungi_articolo_ordine');
Route::any('ajax/trasporto_articolo/{documento}/{codice}/{quantita}/{magazzino}/{ubicazione_P}/{magazzino_A}/{ubicazione_A}/{fornitore}/{lotto}/{dotes}', 'AjaxController@trasporto_articolo');
Route::any('ajax/modifica_articolo_ordine/{id_ordine}/{codice}/{quantita}/{magazzino_A}/{ubicazione_A}/{lotto}/{magazzino_P}/{ubicazione_P}', 'AjaxController@modifica_articolo_ordine');
Route::any('ajax/scarica_articolo_ordine/{id_ordine}/{codice}/{quantita}/{magazzino}/{ubicazione}/{lotto}', 'AjaxController@scarica_articolo_ordine');
Route::any('ajax/crea_documento/{cd_cf}/{cd_do}/{numero}/{data}', 'AjaxController@crea_documento');
//Route::any('ajax/crea_documento_trasporto/{cd_do}/{numero}/{data}', 'AjaxController@crea_documento_trasporto');
Route::any('ajax/crea_documento_rif/{cd_cf}/{cd_do}/{numero}/{data}/{numero_rif}/{data_rif}', 'AjaxController@crea_documento_rif');
Route::any('ajax/cerca_articolo_smart/{q}/{cd_cf}', 'AjaxController@cerca_articolo_smart');
Route::any('ajax/cerca_articolo_smart1/{q}/{cd_cf}', 'AjaxController@cerca_articolo_smart1');
Route::any('ajax/controllo_articolo_smart/{q}/{id_dotes}', 'AjaxController@controllo_articolo_smart');
Route::any('ajax/esplodi/{id_dorig}', 'AjaxController@esplodi');
Route::any('ajax/modifica/{id_dorig}', 'AjaxController@modifica');
Route::any('ajax/stampe/{id_dotes}', 'AjaxController@stampe');
