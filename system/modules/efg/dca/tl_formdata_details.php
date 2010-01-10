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
 * This is the data container array for table tl_formdata_details.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007 
 * @author     Thomas Kuhn <th_kuhn@gmx.net> 
 * @package    efg 
 * @version    1.12.1
 * @license    LGPL 
 * @filesource
 */


/**
 * Table tl_formdata_details 
 */
$GLOBALS['TL_DCA']['tl_formdata_details'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Formdata',
		'ptable'                      => 'tl_formdata',
		'closed'                      => true,
		'notEditable'                 => false,
		'enableVersioning'            => false,
		'doNotCopyRecords'            => false,
		'doNotDeleteRecords'          => false,
		'switchToEdit'                => false,		
		
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('ff_name','ff_label','value'),
			'panelLayout'             => 'search,filter',
			'headerFields'            => array('form', 'date', 'ip', 'be_notes'),
			'child_record_callback'   => array('tl_formdata_details', 'listFormdata')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'pid,id,ff_name,ff_label,ff_type,value'
	),

	// Fields
	'fields' => array
	(
		'value' => array
		(
			'label'                   => array('Value', 'Wert des tl_formdata_details-Datensatzes'),
			'inputType'               => 'text',
			'exclude'                 => false,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false
		)
	)
);


?>