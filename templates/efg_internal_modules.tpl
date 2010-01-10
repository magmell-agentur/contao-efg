<?php echo '<?php'; ?> if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @version    1.12.1
 */

<?php $this->import('String'); ?>
<?php echo '// This file is created when saving a form in form generator' . "\n"; ?>
<?php echo '// last created on ' .date("Y-m-d H:i:s") . ' by saving form "' . $this->arrForm['title'] . '"' . "\n"; ?>


/**
 * Back end modules
 */

$GLOBALS['TL_LANG']['MOD']['formdata'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['formdata'])); ?>';
$GLOBALS['TL_LANG']['MOD']['efg'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['efg'])); ?>';
$GLOBALS['TL_LANG']['MOD']['feedback'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['feedback'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['feedback'][1])); ?>');

$GLOBALS['TL_LANG']['MOD']['formdatalisting'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['formdatalisting'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['formdatalisting'][1])); ?>');

<?php foreach($this->arrStoreForms as $strKey=>$arrVals): ?>
$GLOBALS['TL_LANG']['MOD']['fd_<?php echo $strKey; ?>'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($arrVals['title'])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['MOD']['formdata_from'])); ?> "<?php echo str_replace("'", "\'", $this->String->decodeEntities($arrVals['title'])); ?>".');
<?php endforeach; ?>

/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['formdatalisting'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['FMD']['formdatalisting'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['FMD']['formdatalisting'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['list_formdata'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['list_formdata'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['list_formdata'][1])); ?>');
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'][1])); ?>');
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['efg_list_access'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_access'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_access'][1])); ?>');
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_fields'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_fields'][1])); ?>');
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['none'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['none'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['efg_list_search'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_search'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_search'][1])); ?>');
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_info'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_list_info'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_access']['public'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['public'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['public'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_access']['member'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['member'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['member'][1])); ?>');
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['groupmembers'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_list_access']['groupmembers'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['none'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['none'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['public'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['public'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['member'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['member'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['none'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['none'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['public'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['public'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['member'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['member'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'][1])); ?>');

$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_export_access']['none'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['none'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['none'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_export_access']['public'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['public'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['public'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_export_access']['member'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['member'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['member'][1])); ?>');
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'] = array('<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'][0])); ?>', '<?php echo str_replace("'", "\'", $this->String->decodeEntities($GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'][1])); ?>');

?>