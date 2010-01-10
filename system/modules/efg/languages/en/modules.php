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

$GLOBALS['TL_LANG']['MOD']['formdata'] = 'Form data';
$GLOBALS['TL_LANG']['MOD']['efg'] = 'Form data';
$GLOBALS['TL_LANG']['MOD']['feedback'] = array('All results', 'Stored data from forms.');

$GLOBALS['TL_LANG']['MOD']['formdatalisting'] = array('Listing form data', 'This module allows you to list the records of a certain form data table in the front end.');

$GLOBALS['TL_LANG']['MOD']['fd_kontakt'] = array('Kontakt', 'Stored data from form "Kontakt".');
$GLOBALS['TL_LANG']['MOD']['fd_efg_test_formular'] = array('EFG-Test-Formular', 'Stored data from form "EFG-Test-Formular".');
$GLOBALS['TL_LANG']['MOD']['fd_efg_kategorien'] = array('EFG Kategorien', 'Stored data from form "EFG Kategorien".');
$GLOBALS['TL_LANG']['MOD']['fd_produkt_kategorien'] = array('Produkt-Kategorien', 'Stored data from form "Produkt-Kategorien".');
$GLOBALS['TL_LANG']['MOD']['fd_produkte'] = array('Produkte', 'Stored data from form "Produkte".');
$GLOBALS['TL_LANG']['MOD']['fd_produkt_anfrage'] = array('Produkt-Anfrage', 'Stored data from form "Produkt-Anfrage".');
$GLOBALS['TL_LANG']['MOD']['fd_supermailerchannel'] = array('SuperMailerChannel', 'Stored data from form "SuperMailerChannel".');

/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['formdatalisting'] = array('Listing form data', 'Use this module to list the records of a certain form data table in the front end.');

$GLOBALS['TL_LANG']['tl_module']['list_formdata'] = array('Form data table', 'Please select form data table you want to list.');
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'] = array('URL fragment for detail page', 'Instead of the default key "details" you can define another key here used in URL for listing detail page.<br />This way an URL like www.domain.tld/page/<b>info</b>/alias.html can be generated, whereas standard URL would be www.domain.tld/page/<b>details</b>/alias.html');
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'] = array('Icons folder', 'Give in the directory containing your icons. If left blank the icons in folder "system/modules/efg/html/" will be used.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_access'] = array('Display restriction', 'Choose which records should be visible.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields'] = array('Fields', 'Please select the fields you want to list.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = array('Type of search form', 'Please select the type of search form you want to use.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = array('None', 'No search form');
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = array('Dropdown and input', 'Search form will contain one dropdown to select in which field to search and one text input for the search value');
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = array('Single search field', 'Search form will contain one text input. Search will be performed on each of the defined searchable fields.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = array('Multiple search fields', 'Search form will contain one text input for each defined searchable field.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_search'] = array('Searchable fields', 'Please select the fields that you want to be searchable in the front end.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = array('Details page fields', 'Please select the fields you want to show on the details page. Select none to disable the details page feature.');
$GLOBALS['TL_LANG']['efg_list_access']['public'] = array('Public', 'Each visitor is allowed to see all records.');
$GLOBALS['TL_LANG']['efg_list_access']['member'] = array('Owner', 'Members are allowed to see their own records only.');
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers'] = array('Group members', 'Members are allowed to see their own records and records of their group members only.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'] = array('Frontend editing', 'Choose option to enable editing records in frontend.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none'] = array('No frontend editing', 'Records can not be edited in frontend.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public'] = array('Public', 'Each visitor is allowed to edit all records.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member'] = array('Owner', 'Members are allowed to edit their own records only.');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'] = array('Group members', 'Members are allowed to edit their own records and records of their group members only.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'] = array('Frontend deleting', 'Choose option to enable deleting records in frontend.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none'] = array('No frontend deleting', 'Records can not be deleted in frontend.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public'] = array('Public', 'Each visitor is allowed to delete all records.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member'] = array('Owner', 'Members are allowed to delete their own records only.');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'] = array('Group members', 'Members are allowed to delete their own records and records of their group members only.');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'] = array('Frontend CSV export', 'Choose option to enable exporting records as CSV file in frontend.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['none'] = array('No frontend export', 'Records can not be exported in frontend.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['public'] = array('Public', 'Each visitor is allowed to export all records.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['member'] = array('Owner', 'Members are allowed to export their own records only.');
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'] = array('Group members', 'Members are allowed to export their own records and records of their group members only.');

?>