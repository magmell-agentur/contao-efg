<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * Language file for modules.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007 
 * @author     Thomas Kuhn <th_kuhn@gmx.net> 
 * @package    efg 
 * @license    LGPL 
 * @filesource
 * @version    1.11.0
 */

// This file is created when saving a form in form generator
// last created on 2009-04-27 09:17:37 by saving form "EFG-Test-Formular"


/**
 * Back end modules
 */

$GLOBALS['TL_LANG']['MOD']['formdata'] = 'Formular-Daten';
$GLOBALS['TL_LANG']['MOD']['efg'] = 'Formular-Daten';
$GLOBALS['TL_LANG']['MOD']['feedback'] = array('Feedback', 'Gespeicherte Daten aus Formularen.');

$GLOBALS['TL_LANG']['MOD']['formdatalisting'] = array('Auflistung Formular-Daten', 'Mit diesem Modul können Sie eine beliebige Formular-Daten-Tabelle im Frontend auflisten.');

$GLOBALS['TL_LANG']['MOD']['fd_kontakt'] = array('Kontakt', 'Gespeicherte Daten aus Formular "Kontakt".');
$GLOBALS['TL_LANG']['MOD']['fd_efg_test_formular'] = array('EFG-Test-Formular', 'Gespeicherte Daten aus Formular "EFG-Test-Formular".');
$GLOBALS['TL_LANG']['MOD']['fd_efg_kategorien'] = array('EFG Kategorien', 'Gespeicherte Daten aus Formular "EFG Kategorien".');
$GLOBALS['TL_LANG']['MOD']['fd_produkt_kategorien'] = array('Produkt-Kategorien', 'Gespeicherte Daten aus Formular "Produkt-Kategorien".');
$GLOBALS['TL_LANG']['MOD']['fd_produkte'] = array('Produkte', 'Gespeicherte Daten aus Formular "Produkte".');
$GLOBALS['TL_LANG']['MOD']['fd_produkt_anfrage'] = array('Produkt-Anfrage', 'Gespeicherte Daten aus Formular "Produkt-Anfrage".');
$GLOBALS['TL_LANG']['MOD']['fd_supermailerchannel'] = array('SuperMailerChannel', 'Gespeicherte Daten aus Formular "SuperMailerChannel".');

/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['formdatalisting'] = array('Auflistung Formular-Daten', 'Verwenden Sie dieses Modul dazu, die Daten einer beliebigen Formular-Daten-Tabelle im Frontend aufzulisten.');

$GLOBALS['TL_LANG']['tl_module']['list_formdata'] = array('Formular-Daten-Tabelle', 'Bitte wählen Sie die Formular-Daten-Tabelle, deren Datensätze Sie auflisten möchten.');
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'] = array('URL-Fragment der Detailseite', 'Anstelle der Vorgabe "details" in der URL der Auflistungs-Detailseite können Sie hier einen abweichenden Begriff angeben.<br />Dadurch kann z.B. eine URL www.domain.tld/page/<b>info</b>/alias.html statt der Standard-URL www.domain.tld/page/<b>details</b>/alias.html erzeugt werden');
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'] = array('Verzeichnis der Icons', 'Tragen Sie hier das Verzeichnis Ihrer Icons ein. Falls das Feld nicht ausgefüllt wird, werden die Icons im Verzeichnis "system/modules/efg/html/" verwendet.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_access'] = array('Anzeige Einschränkung', 'Wählen Sie, welche Daten angezeigt werden dürfen.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields'] = array('Felder', 'Bitte wählen Sie die Felder, die Sie auflisten möchten.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = array('Typ des Such-Formulars', 'Bitte wählen Sie, welchen Typ des Such-Formulars Sie verwenden möchten.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = array('Keine Suche', 'Kein Suchformular');
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = array('Dropdown und Eingabefeld', 'Das Suchformular enthält ein DropDown zur Auswahl des zu durchsuchenden Feldes und ein Eingabefeld für den Suchbegriff.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = array('Einzelnes Eingabefeld', 'Das Suchformular enthält ein einzelndes Eingabefeld für den Suchbegriff. Bei der Suche werden alle als durchsuchbare Felder definierten Felder berücksichtigt.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = array('Mehrere Eingabefelder', 'Das Suchformular enthält für jedes durchsuchbare Feld ein separates Eingabefeld für den Suchbegriff.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_search'] = array('Durchsuchbare Felder', 'Bitte wählen Sie die Felder, die im Frontend durchsuchbar sein sollen.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = array('Felder der Detailseite', 'Bitte wählen Sie die Felder, die Sie auf der Detailseite anzeigen möchten. Wählen Sie kein Feld, um die Detailansicht eines Datensatzes zu deaktivieren.');
$GLOBALS['TL_LANG']['efg_list_access']['public'] = array('Öffentlich', 'Jeder Seitenbesucher darf alle Daten sehen.');
$GLOBALS['TL_LANG']['efg_list_access']['member'] = array('Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten sehen.');
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers'] = array('Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder sehen.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'] = array('Bearbeitung im Frontend', 'Wählen Sie, ob Daten im Frontend bearbeitet werden dürfen.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none'] = array('Keine Bearbeitung', 'Daten können nicht im Frontend bearbeitet werden.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public'] = array('Öffentlich', 'Jeder Seitenbesucher darf alle Daten bearbeiten.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member'] = array('Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten bearbeiten.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'] = array('Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder bearbeiten.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'] = array('Löschen im Frontend', 'Wählen Sie, ob Daten im Frontend gelöscht werden dürfen.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none'] = array('Kein Löschen', 'Daten können nicht im Frontend gelöscht werden.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public'] = array('Öffentlich', 'Jeder Seitenbesucher darf alle Daten löschen.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member'] = array('Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten löschen.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'] = array('Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder löschen.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'] = array('CSV-Export im Frontend', 'Wählen Sie, ob Daten im Frontend als CSV-Datei exportiert werden dürfen.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['none'] = array('Kein Export', 'Daten können nicht im Frontend exportiert werden.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['public'] = array('Öffentlich', 'Jeder Seitenbesucher darf alle Daten exportieren.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['member'] = array('Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten exportieren.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'] = array('Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder exportieren.');

?>