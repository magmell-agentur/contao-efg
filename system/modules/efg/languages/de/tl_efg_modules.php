<?php
/**
 * TL_ROOT/system/modules/efg/languages/de/tl_efg_modules.php
 *
 * TYPOlight extension: efg 1.10.4 stable
 * Deutsch translation file
 *
 * Copyright : (c) 2008 Thomas Kuhn
 * License   : GNU LGPL V3
 * Author    : Thomas Kuhn (tom)
 * Translator: Thomas Kuhn (tom)
 *
 * This file was created automatically be the TYPOlight extension repository translation module.
 * Do not edit this file manually. Contact the author or translator for this module to establish
 * permanent text corrections which are update-safe.
 */

$GLOBALS['TL_LANG']['MOD']['formdata'] = "Formular-Daten";
$GLOBALS['TL_LANG']['MOD']['efg'] = "Formular-Daten";
$GLOBALS['TL_LANG']['MOD']['feedback']['0'] = "Feedback";
$GLOBALS['TL_LANG']['MOD']['feedback']['1'] = "Gespeicherte Daten aus Formularen.";
$GLOBALS['TL_LANG']['MOD']['formdata_from'] = "Gespeicherte Daten aus Formular";
$GLOBALS['TL_LANG']['MOD']['formdatalisting']['0'] = "Auflistung Formular-Daten";
$GLOBALS['TL_LANG']['MOD']['formdatalisting']['1'] = "Mit diesem Modul können Sie eine beliebige Formular-Daten-Tabelle im Frontend auflisten.";
$GLOBALS['TL_LANG']['FMD']['formdatalisting']['0'] = "Auflistung Formular-Daten";
$GLOBALS['TL_LANG']['FMD']['formdatalisting']['1'] = "Verwenden Sie dieses Modul dazu, die Daten einer beliebigen Formular-Daten-Tabelle im Frontend aufzulisten.";
$GLOBALS['TL_LANG']['tl_module']['list_formdata']['0'] = "Formular-Daten-Tabelle";
$GLOBALS['TL_LANG']['tl_module']['list_formdata']['1'] = "Bitte wählen Sie die Formular-Daten-Tabelle, deren Datensätze Sie auflisten möchten.";

$GLOBALS['TL_LANG']['tl_module']['efg_list_fields']['0'] = "Felder";
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields']['1'] = "Bitte wählen Sie die Felder, die Sie auflisten möchten.";
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = array('Typ des Such-Formulars', 'Bitte wählen Sie, welchen Typ des Such-Formulars Sie verwenden möchten.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = array('Keine Suche', 'Kein Suchformular');
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = array('Dropdown und Eingabefeld', 'Das Suchformular enthält ein DropDown zur Auswahl des zu durchsuchenden Feldes und ein Eingabefeld für den Suchbegriff.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = array('Einzelnes Eingabefeld', 'Das Suchformular enthält ein einzelndes Eingabefeld für den Suchbegriff. Bei der Suche werden alle als durchsuchbare Felder definierten Felder berücksichtigt.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = array('Mehrere Eingabefelder', 'Das Suchformular enthält für jedes durchsuchbare Feld ein separates Eingabefeld für den Suchbegriff.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_search']['0'] = "Durchsuchbare Felder";
$GLOBALS['TL_LANG']['tl_module']['efg_list_search']['1'] = "Bitte wählen Sie die Felder, die im Frontend durchsuchbar sein sollen.";
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = array('Felder der Detailseite', 'Bitte wählen Sie die Felder, die Sie auf der Detailseite anzeigen möchten. Wählen Sie kein Feld, um die Detailansicht eines Datensatzes zu deaktivieren.');

$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder']['0'] = "Verzeichnis der Icons";
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder']['1'] = "Tragen Sie hier das Verzeichnis Ihrer Icons ein. Falls das Feld nicht ausgefüllt wird, werden die Icons im Verzeichnis \"system/modules/efg/html/\" verwendet.";
$GLOBALS['TL_LANG']['tl_module']['efg_list_access']['0'] = "Anzeige Einschränkung";
$GLOBALS['TL_LANG']['tl_module']['efg_list_access']['1'] = "Wählen Sie, welche Daten angezeigt werden dürfen.";
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey']['0'] = "URL-Fragment der Detailseite";
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey']['1'] = "Anstelle der Vorgabe \"details\" in der URL der Auflistungs-Detailseite können Sie hier einen abweichenden Begriff angeben.<br />Dadurch kann z.B. eine URL www.domain.tld/page/<b>info</b>/alias.html statt der Standard-URL www.domain.tld/page/<b>details</b>/alias.html erzeugt werden";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access']['0'] = "Bearbeitung im Frontend";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access']['1'] = "Wählen Sie, ob Daten im Frontend bearbeitet werden dürfen.";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access']['0'] = "Löschen im Frontend";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access']['1'] = "Wählen Sie, ob Daten im Frontend gelöscht werden dürfen.";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access']['0'] = "CSV-Export im Frontend";
$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access']['1'] = "Wählen Sie, ob Daten im Frontend als CSV-Datei exportiert werden dürfen.";
$GLOBALS['TL_LANG']['efg_list_access']['public']['0'] = "Öffentlich";
$GLOBALS['TL_LANG']['efg_list_access']['public']['1'] = "Jeder Seitenbesucher darf alle Daten sehen.";
$GLOBALS['TL_LANG']['efg_list_access']['member']['0'] = "Besitzer";
$GLOBALS['TL_LANG']['efg_list_access']['member']['1'] = "Mitglieder dürfen nur ihre eigenen Daten sehen.";
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers']['0'] = "Gruppen-Mitglieder";
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers']['1'] = "Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder sehen.";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none']['0'] = "Keine Bearbeitung";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none']['1'] = "Daten können nicht im Frontend bearbeitet werden.";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public']['0'] = "Öffentlich";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public']['1'] = "Jeder Seitenbesucher darf alle Daten bearbeiten.";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member']['0'] = "Besitzer";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member']['1'] = "Mitglieder dürfen nur ihre eigenen Daten bearbeiten.";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers']['0'] = "Gruppen-Mitglieder";
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers']['1'] = "Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder bearbeiten.";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none']['0'] = "Kein Löschen";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none']['1'] = "Daten können nicht im Frontend gelöscht werden.";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public']['0'] = "Öffentlich";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public']['1'] = "Jeder Seitenbesucher darf alle Daten löschen.";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member']['0'] = "Besitzer";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member']['1'] = "Mitglieder dürfen nur ihre eigenen Daten löschen.";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers']['0'] = "Gruppen-Mitglieder";
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers']['1'] = "Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder löschen.";
$GLOBALS['TL_LANG']['efg_fe_export_access']['none']['0'] = "Kein Export";
$GLOBALS['TL_LANG']['efg_fe_export_access']['none']['1'] = "Daten können nicht im Frontend exportiert werden.";
$GLOBALS['TL_LANG']['efg_fe_export_access']['public']['0'] = "Öffentlich";
$GLOBALS['TL_LANG']['efg_fe_export_access']['public']['1'] = "Jeder Seitenbesucher darf alle Daten exportieren.";
$GLOBALS['TL_LANG']['efg_fe_export_access']['member']['0'] = "Besitzer";
$GLOBALS['TL_LANG']['efg_fe_export_access']['member']['1'] = "Mitglieder dürfen nur ihre eigenen Daten exportieren.";
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers']['0'] = "Gruppen-Mitglieder";
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers']['1'] = "Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder exportieren.";

?>
