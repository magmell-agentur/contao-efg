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
 * This is the extended data container array for table tl_form_field.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @version    1.11.0
 * @license    LGPL
 * @filesource
 */


// Table tl_form_fields
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'storeFormdata';
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'sendConfirmationMail';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgLookupOptions'] = array
(
	'label'        => &$GLOBALS['TL_LANG']['tl_form_field']['efgLookupOptions'],
	'exclude'      => true,
	'inputType'    => 'efgLookupOptionWizard'
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgMultiSRC'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgMultiSRC'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageUseHomeDir'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageUseHomeDir'],
	'exclude'                 => true,
	'inputType'               => 'checkbox'
);


$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageSortBy'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageSortBy'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('name_asc', 'name_desc', 'date_asc', 'date_desc', 'meta'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_form_field']
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageSize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageSize'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit')
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImagePerRow'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImagePerRow'],
	'default'                 => 4,
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageMargin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageMargin'],
	'exclude'                 => true,
	'inputType'               => 'trbl',
	'options'                 => array('px', '%', 'em', 'pt', 'pc', 'in', 'cm', 'mm'),
	'eval'                    => array('includeBlankOption'=>true)
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageFullsize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageFullsize'],
	'exclude'                 => true,
	'inputType'               => 'checkbox'
);


// add palette for field type efgLookupSelect
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['palettes']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
		array('efgLookupSelect' => 'type,name;label,mandatory;efgLookupOptions;multiple;accesskey,class;addSubmit')
	);
}
// add field type efgLookupSelect to available form field 'type'
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('select', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])+1),
		'efgLookupSelect'
	);
}

// add palette for field type efgLookupCheckbox
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['palettes']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
		array('efgLookupCheckbox' => 'type,name;label,mandatory;efgLookupOptions;accesskey,class;addSubmit')
	);
}
// add field type efgLookupCheckbox to available form field 'type'
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('checkbox', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])+1),
		'efgLookupCheckbox'
	);
}

// add palette for field type efgLookupRadio
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['palettes']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
		array('efgLookupRadio' => 'type,name;label,mandatory;efgLookupOptions;accesskey,class;addSubmit')
	);
}
// add field type efgLookupRadio to available form field 'type'
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('radio', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])+1),
		'efgLookupRadio'
	);
}

// add palette for field type efgImageSelect
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['palettes']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
		array('efgImageSelect' => 'name,type;label,mandatory;efgMultiSRC,efgImageUseHomeDir;efgImageSortBy,efgImageSize,efgImageFullsize,efgImagePerRow,efgImageMargin;accesskey,class;addSubmit')
	);
}

// add field type efgImageSelect to available form field 'type'
if (is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options']))
{
	array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('upload', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])+1),
		'efgImageSelect'
	);
}

// add backend form fields
$GLOBALS['BE_FFL']['efgLookupOptionWizard'] = 'EfgLookupOptionWizard';
$GLOBALS['BE_FFL']['efgLookupSelect'] = 'EfgFormLookupSelectMenu';
$GLOBALS['BE_FFL']['efgLookupCheckbox'] = 'EfgFormLookupCheckbox';
$GLOBALS['BE_FFL']['efgLookupRadio'] = 'EfgFormLookupRadio';
$GLOBALS['BE_FFL']['efgImageSelectWizard'] = 'EfgImageSelectWizard';

// add front end form fields
$GLOBALS['TL_FFL']['efgLookupSelect'] = 'EfgFormLookupSelectMenu';
$GLOBALS['TL_FFL']['efgLookupCheckbox'] = 'EfgFormLookupCheckbox';
$GLOBALS['TL_FFL']['efgLookupRadio'] = 'EfgFormLookupRadio';
$GLOBALS['TL_FFL']['efgImageSelect'] = 'EfgFormImageSelect';

?>