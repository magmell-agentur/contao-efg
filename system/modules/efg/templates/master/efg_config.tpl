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
 * This is the formdata configuration file.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007 
 * @author     Thomas Kuhn <th_kuhn@gmx.net> 
 * @package    efg 
 * @license    LGPL 
 * @filesource
 * @version    1.11.0
 */


<?php echo '// This file is created when saving a form in form generator' . "\n"; ?>
<?php echo '// last created on ' .date("Y-m-d H:i:s") . ' by saving form "' . $this->arrForm['title'] . '"' . "\n"; ?>


/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 *
 * Back end modules are stored in a global array called "BE_MOD". Each module 
 * has certain properties like an icon, an optional callback function and one 
 * or more tables. Each module belongs to a particular group.
 * 
 */

<?php if ($this->arrStoreForms && count($this->arrStoreForms) > 0): ?>
array_insert($GLOBALS['BE_MOD'], 1, array('formdata' => array()));

// this is used for the form independent "Feedback" module
$GLOBALS['BE_MOD']['formdata']['feedback'] = array
	(
		'tables'     => array('tl_formdata', 'tl_formdata_details'),
		'icon'       => 'system/modules/efg/html/formdata_all.gif',
		'stylesheet' => 'system/modules/efg/html/style.css'
	);

// following are used for the form dependent modules
<?php foreach($this->arrStoreForms as $strKey=>$arrVals): ?>
$GLOBALS['BE_MOD']['formdata']['fd_<?php echo $strKey; ?>'] = array
	(
		'tables'     => array('tl_formdata', 'tl_formdata_details'),
		'icon'       => 'system/modules/efg/html/formdata.gif',
		'stylesheet' => 'system/modules/efg/html/style.css'
	);
<?php endforeach; ?>
<?php endif; ?>


/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 * 
 */

array_insert($GLOBALS['FE_MOD']['application'], count($GLOBALS['FE_MOD']['application']), array
(
	'formdatalisting' => 'ModuleFormdataListing'
));
 
/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 *
 * Hooking allows you to register one or more callback functions that are 
 * called on a particular event in a specific order. Thus, third party 
 * extensions can add functionality to the core system without having to
 * modify the source code.
 * 
 */

$GLOBALS['TL_HOOKS']['processFormData'][] = array('Efp', 'processSubmittedData');
<?php if (VERSION == '2.5' && intval(BUILD) <= 9): ?>
$GLOBALS['TL_HOOKS']['outputTemplate'][] = array('Efp', 'processConfirmationContent');
<?php else: ?>
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('Efp', 'processConfirmationContent');
<?php endif; ?> 
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('FormData', 'getSearchablePages');
?>