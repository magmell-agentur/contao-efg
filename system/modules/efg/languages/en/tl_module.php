<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * Extended language file for table tl_form (en).
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007 - 2010
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    efg
 * @license    LGPL
 * @filesource
 */


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['efgSearch_legend'] = "Search settings";
$GLOBALS['TL_LANG']['tl_module']['comments_legend'] = "Comments";

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['list_formdata'] = array('Form data table', 'Please select form data table you want to list.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields'] = array('Fields', 'Please select the fields you want to list.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = array('Type of search form', 'Please select the type of search form you want to use.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = array('None', 'No search form');
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = array('Dropdown and input', 'Search form will contain one dropdown to select in which field to search and one text input for the search value');
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = array('Single search field', 'Search form will contain one text input. Search will be performed on each of the defined searchable fields.');
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = array('Multiple search fields', 'Search form will contain one text input for each defined searchable field.');

$GLOBALS['TL_LANG']['tl_module']['efg_list_search'] = array('Searchable fields', 'Please select the fields that you want to be searchable in the front end.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = array('Details page fields', 'Please select the fields you want to show on the details page. Select none to disable the details page feature.');
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'] = array('Icons folder', 'Give in the directory containing your icons. If left blank the icons in folder "system/modules/efg/html/" will be used.');
$GLOBALS['TL_LANG']['tl_module']['efg_fe_keep_id'] = array('Keep record ID on frontend editing', 'When editing in frontend normally a new record is created and therefore a new ID, then the old one is deleted. Choose this option if you rely on an unchanged record ID.');
$GLOBALS['TL_LANG']['tl_module']['efg_list_access'] = array('Display restriction', 'Choose which records should be visible.');
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'] = array('URL fragment for detail page', 'Instead of the default key "details" you can define another key here used in URL for listing detail page.<br />This way an URL like www.domain.tld/page/<b>info</b>/alias.html can be generated, whereas standard URL would be www.domain.tld/page/<b>details</b>/alias.html');
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