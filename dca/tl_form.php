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
 * This is the extended data container array for table tl_form.
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
 * Table tl_form
 */

$GLOBALS['TL_DCA']['tl_form']['config']['onsubmit_callback'][] = array('ModuleFormdata', 'createFormdataDca');

// fields
$GLOBALS['TL_DCA']['tl_form']['fields']['storeFormdata'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['storeFormdata'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('helpwizard'=>true,'submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_form']['fields']['efgStoreValues'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['efgStoreValues'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox',
);

$GLOBALS['TL_DCA']['tl_form']['fields']['useFormValues'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['useFormValues'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['useFieldNames'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['useFieldNames'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['efgAliasField'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['efgAliasField'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'select',
	'options_callback'        => array('tl_ext_form', 'getAliasFormFields'),
	'eval'                    => array('mandatory'=>true, 'maxlength'=>64)
);

$GLOBALS['TL_DCA']['tl_form']['fields']['sendConfirmationMail'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['sendConfirmationMail'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('helpwizard'=>true,'submitOnChange'=>true)
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailRecipientField'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipientField'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'select',
	'options_callback'        => array('tl_ext_form', 'getEmailFormFields'),
	'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailRecipient'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipient'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailSender'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'text',
	'load_callback'           => array('tl_extform', 'setMailSender'),
	'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSubject'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailSubject'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailText'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailText'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'textarea',
	'eval'                    => array('mandatory'=>true, 'rows'=>15, 'allowHTML'=>false)
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailTemplate'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailTemplate'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'fileTree',
	'eval'                    => array('helpwizard'=>false,'files'=>true, 'fieldType'=>'radio', 'extensions' => 'htm,html,txt,tpl')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSkipEmpty'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['skipEmtpy'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_form']['fields']['sendFormattedMail'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['sendFormattedMail'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailRecipient'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['recipient'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>true, 'rgxp'=>'extnd', 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailSubject'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['subject'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailText'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['formattedMailText'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'textarea',
	'eval'                    => array('rows'=>15, 'allowHTML'=>false)
);
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailTemplate'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['formattedMailTemplate'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'fileTree',
	'eval'                    => array('helpwizard'=>false,'files'=>true, 'fieldType'=>'radio', 'extensions' => 'htm,html,txt,tpl')
);
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailSkipEmpty'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['skipEmtpy'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_form']['fields']['addConfirmationMailAttachments'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['addConfirmationMailAttachments'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange'=>true)
);
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailAttachments'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailAttachments'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'fileTree',
	'eval'                    => array('fieldType'=>'checkbox', 'files'=>true, 'filesOnly'=>true, 'mandatory'=>true)
);

// Palettes
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'sendFormattedMail';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'sendConfirmationMail';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'addConfirmationMailAttachments';

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'storeFormdata';
$GLOBALS['TL_DCA']['tl_form']['palettes']['default'] =  str_replace(array('storeValues', 'sendViaEmail'), array('storeValues;{efgStoreFormdata_legend:hide},storeFormdata', 'sendViaEmail;{efgSendFormattedMail_legend:hide},sendFormattedMail;{efgSendConfirmationMail_legend:hide},sendConfirmationMail'), $GLOBALS['TL_DCA']['tl_form']['palettes']['default'] );

// Subpalettes
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
	array('sendFormattedMail' => 'formattedMailRecipient,formattedMailSubject,formattedMailText,formattedMailTemplate,formattedMailSkipEmpty')
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
	array('sendConfirmationMail' => 'confirmationMailRecipientField,confirmationMailRecipient,confirmationMailSender,confirmationMailSubject,confirmationMailText,confirmationMailTemplate,confirmationMailSkipEmpty,addConfirmationMailAttachments')
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
	array('addConfirmationMailAttachments' => 'confirmationMailAttachments')
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
	array('storeFormdata' => 'efgAliasField,efgStoreValues,useFormValues,useFieldNames')
);

// PanelLayout
// $GLOBALS['TL_DCA']['tl_form']['list']['sorting']['panelLayout'] = 'filter;search,limit';


/**
 * Class tl_extform
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn
 * @package    efg
 */
class tl_ext_form extends Backend
{

	/**
	 * Return all possible Email fields  as array
	 * @return array
	 */
	public function getEmailFormFields()
	{

		$fields = array();

		// Get all form fields which can be used to define recipient of confirmation mail
		$objFields = $this->Database->prepare("SELECT id,name,label FROM tl_form_field WHERE pid=? AND (type=? OR type=? OR type=? OR type=? OR type=?) ORDER BY name ASC")
							->execute($this->Input->get('id'), 'text', 'hidden', 'select', 'radio', 'checkbox');

		$fields[] = '-';
		while ($objFields->next())
		{
			$k = $objFields->name;
			$v = $objFields->label;
			$v = strlen($v) ? $v.' ['.$k.']' : $k;
			$fields[$k] =$v;
		}

		return $fields;
	}

	/**
	 * Return all possible Alias fields as array
	 * @return array
	 */
	public function getAliasFormFields()
	{

		$fields = array();

		// Get all form fields which can be used to build auto alias
		$objFields = $this->Database->prepare("SELECT id,name,label FROM tl_form_field WHERE pid=? AND (type=? OR type=?) ORDER BY name ASC")
							->execute($this->Input->get('id'), 'text', 'hidden');

		$fields[] = '-';
		while ($objFields->next())
		{
			$k = $objFields->name;
			$v = $objFields->label;
			$v = strlen($v) ? $v.' ['.$k.']' : $k;
			$fields[$k] =$v;
		}

		return $fields;
	}

}

?>