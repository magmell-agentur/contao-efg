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
 * This file modifies the data container array of table tl_module.
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
* to fix height of style class w50 in backend
*/
$GLOBALS['BE_MOD']['design']['modules']['stylesheet'] = 'system/modules/efg/html/w50_fix.css';


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['formdatalisting'] = '{title_legend},name,headline,type;{config_legend},list_formdata,list_where,list_sort,perPage,list_fields,list_info;{efgSearch_legend},list_search,efg_list_searchtype;{protected_legend:hide},efg_list_access,efg_fe_edit_access,efg_fe_delete_access,efg_fe_export_access;{template_legend:hide},list_layout,list_info_layout;{expert_legend:hide},efg_DetailsKey,efg_iconfolder,efg_fe_keep_id,align,space,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['type']['load_callback'][] = array('tl_ext_module', 'onloadModuleType');

/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['list_formdata'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_formdata'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_ext_module', 'getFormdataTables'),
	'eval'                    => array('mandatory' => true, 'maxlength' => 64, 'includeBlankOption' => true, 'submitOnChange' => true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['list_where'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_where'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['list_sort'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_sort'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_fields'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50" style="height:auto'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_list_searchtype'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('dropdown', 'singlefield', 'multiplefields'),
	'reference'               => &$GLOBALS['TL_LANG']['efg_list_searchtype'],
	'eval'                    => array('mandatory'=>false, 'includeBlankOption'=>true, 'helpwizard'=>true,  'tl_class'=>'w50" style="height:auto')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['list_search'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_search'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50" style="height:auto')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['list_info'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['list_info'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50" style="height:auto')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_list_access'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_list_access'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('public','groupmembers','member'),
	'reference'               => &$GLOBALS['TL_LANG']['efg_list_access'],
	'eval'                    => array('mandatory'=>true, 'includeBlankOption' => true, 'helpwizard'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_fe_edit_access'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('none','public','groupmembers','member'),
	'reference'               => &$GLOBALS['TL_LANG']['efg_fe_edit_access'],
	'eval'                    => array('mandatory'=>true, 'includeBlankOption' => true, 'helpwizard'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_fe_delete_access'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('none','public','groupmembers','member'),
	'reference'               => &$GLOBALS['TL_LANG']['efg_fe_delete_access'],
	'eval'                    => array('mandatory'=>true, 'includeBlankOption' => true, 'helpwizard'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_fe_export_access'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('none','public','groupmembers','member'),
	'reference'               => &$GLOBALS['TL_LANG']['efg_fe_export_access'],
	'eval'                    => array('mandatory'=>true, 'includeBlankOption' => true, 'helpwizard'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_DetailsKey'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'],
	'exclude'                 => false,
	'filter'                  => false,
	'inputType'               => 'text',
	'eval'                    => array('default' => 'details', 'maxlength'=>64, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['efg_iconfolder'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'trailingSlash'=>false, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['efg_fe_keep_id'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['efg_fe_keep_id'],
	'exclude'                 => true,
	'filter'                  => false,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'clr m12 cbx')
);


/**
 * Class tl_ext_module
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn
 * @package    efg
 */
class tl_ext_module extends Backend
{

	private $arrFormdataTables = null;
	private $arrFormdataFields = null;

	public function onloadModuleType($varValue, DC_Table $dc)
	{
		if ($varValue == 'formdatalisting')
		{
			$GLOBALS['TL_LANG']['tl_module']['list_fields'] = $GLOBALS['TL_LANG']['tl_module']['efg_list_fields'];
			$GLOBALS['TL_LANG']['tl_module']['list_search'] = $GLOBALS['TL_LANG']['tl_module']['efg_list_search'];
			$GLOBALS['TL_LANG']['tl_module']['list_info'] = $GLOBALS['TL_LANG']['tl_module']['efg_list_info'];

			$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields']['inputType'] = 'checkboxWizard';
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields']['eval']['mandatory'] = false;
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields']['options_callback'] = array('tl_ext_module', 'optionsListFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields']['load_callback'][] = array('tl_ext_module', 'onloadListFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_fields']['save_callback'][] = array('tl_ext_module', 'onsaveFieldList');

			$GLOBALS['TL_DCA']['tl_module']['fields']['list_search']['inputType'] = 'checkboxWizard';
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_search']['options_callback'] = array('tl_ext_module', 'optionsSearchFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_search']['load_callback'][] = array('tl_ext_module', 'onloadSearchFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_search']['save_callback'][] = array('tl_ext_module', 'onsaveFieldList');

			$GLOBALS['TL_DCA']['tl_module']['fields']['list_info']['inputType'] = 'checkboxWizard';
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_info']['options_callback'] = array('tl_ext_module', 'optionsInfoFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_info']['load_callback'][] = array('tl_ext_module', 'onloadInfoFields');
			$GLOBALS['TL_DCA']['tl_module']['fields']['list_info']['save_callback'][] = array('tl_ext_module', 'onsaveFieldList');
		}
		return $varValue;
	}

	/**
	 * Return all formdata tables as array
	 * @return array
	 */
	public function getFormdataTables(DC_Table $dc)
	{
		if (is_null($this->arrFormdataTables) || is_null($this->arrFormdataFields))
		{
			$this->arrFormdataTables = array();
			$this->arrFormdataTables['fd_feedback'] = $GLOBALS['TL_LANG']['MOD']['feedback'][0];

			// all forms marked to store data
			$objForms = $this->Database->prepare("SELECT f.id,f.title,f.formID,ff.type,ff.name,ff.label FROM tl_form f, tl_form_field ff WHERE (f.id=ff.pid) AND storeFormdata=? ORDER BY title")
										->execute("1");
			while ($objForms->next())
			{
				if (strlen($objForms->formID)) {
					$varKey = 'fd_' . $objForms->formID;
				}
				else
				{
					$varKey = 'fd_' . str_replace('-', '_', standardize($objForms->title));
				}
				$this->arrFormdataTables[$varKey] = $objForms->title;
				$this->arrFormdataFields['fd_feedback'][$objForms->name] = $objForms->label;
				$this->arrFormdataFields[$varKey][$objForms->name] = $objForms->label;
			}
		}

		$this->loadLanguageFile('tl_formdata');
		if (strlen($dc->value))
		{
			$this->loadDataContainer($dc->value);
		}
		return $this->arrFormdataTables;
	}

	public function optionsListFields(DC_Table $dc)
	{
		return $this->getFieldsOptionsArray('list_fields');
	}

	public function optionsSearchFields(DC_Table $dc)
	{
		return $this->getFieldsOptionsArray('list_search');
	}

	public function optionsInfoFields(DC_Table $dc)
	{
		return $this->getFieldsOptionsArray('list_info');
	}

	public function getFieldsOptionsArray($strField)
	{
		$arrReturn = array();
		if (count($GLOBALS['TL_DCA']['tl_formdata']['fields']))
		{
			$GLOBALS['TL_DCA']['tl_module']['fields'][$strField]['inputType'] = 'CheckboxWizard';
			$GLOBALS['TL_DCA']['tl_module']['fields'][$strField]['eval']['multiple'] = true;
			$GLOBALS['TL_DCA']['tl_module']['fields'][$strField]['eval']['mandatory'] = false;
			foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'] as $k => $v)
			{
				if (in_array($k, array('ip', 'published')) )
				{
// ###
				//	continue;
				}
				$arrReturn[$k] = (strlen($GLOBALS['TL_DCA']['tl_formdata']['fields'][$k]['label'][0]) ? $GLOBALS['TL_DCA']['tl_formdata']['fields'][$k]['label'][0] . ' [' . $k . ']' : $k);
			}
		}
		return $arrReturn;
	}

	public function onloadListFields($varValue, DC_Table $dc)
	{
		return $this->onloadFieldList('list_fields', $varValue);
	}

	public function onloadSearchFields($varValue, DC_Table $dc)
	{
		return $this->onloadFieldList('list_search', $varValue);
	}

	public function onloadInfoFields($varValue, DC_Table $dc)
	{
		return $this->onloadFieldList('list_info', $varValue);
	}

	public function onsaveFieldList($varValue)
	{
		if (strlen($varValue))
		{
			return implode(',', deserialize($varValue));
		}
		return $varValue;
	}

	public function onloadFieldList($strField, $varValue)
	{
		if (isset($GLOBALS['TL_DCA']['tl_module']['fields'][$strField]))
		{
			$GLOBALS['TL_DCA']['tl_module']['fields'][$strField]['eval']['multiple'] = true;
			if (is_string($varValue))
			{
				$varValue = explode(',', $varValue);
			}

		}
		return $varValue;
	}

}

?>