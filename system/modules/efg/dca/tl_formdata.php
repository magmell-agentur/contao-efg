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
 * This is the data container array for table tl_feedback.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @version    1.11.0
 * @license    LGPL
 * @filesource
 */


/**
 * Table tl_formdata
 */
$GLOBALS['TL_DCA']['tl_formdata'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Formdata',
		'ctable'                      => array('tl_formdata_details'),
		'closed'                      => true,
		'notEditable'                 => false,
		'enableVersioning'            => false,
		'doNotCopyRecords'            => true,
		'doNotDeleteRecords'          => true,
		'switchToEdit'                => false,
		'onload_callback'             => array
			(
				array('tl_formdata', 'loadDCA'),
				array('tl_formdata', 'checkPermission')
			)
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('date DESC'),
			'flag'                    => 8,
			'panelLayout'             => 'sort,filter;search,limit',

		),
		'label' => array
		(
			'fields'                  => array('form', 'date', 'ip', 'alias'),
			'label_callback'          => array('tl_formdata','getRowLabel')
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
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'mail' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['mail'],
				'href'                => 'act=mail',
				'icon'                => 'system/modules/efg/html/mail.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'form,ip,date,alias;published,be_notes;fd_member,fd_user'
	),

	// Fields
	'fields' => array
	(
		'form' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['form'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
		),
		'date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['date'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => true,
			'flag'                    => 8,
			'eval'                    => array('rgxp' => 'datim', 'tl_class'=>'w50')
		),
		'ip' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['ip'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('tl_class'=>'w50')
		),
		'fd_member' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getMembersSelect'),
		),
		'fd_user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getUsersSelect'),
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			// 'default'                 => '1'
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['alias'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alnum', 'unique'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>64),
			'save_callback' => array
			(
				array('tl_formdata', 'generateAlias')
			)
		),
		'be_notes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['be_notes'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('rows' => 5),
			'class'                   => 'fd_notes'
		),
	)
);



/**
 * Class tl_formdata
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn
 * @package    Controller
 */
class tl_formdata extends Backend
{

	/**
	 * Database result
	 * @var array
	 */
	protected $arrData = null;


	/*
	 * Loads $GLOBALS['TL_DCA']['tl_formdata'] or overwrites with form-specific dca-config
	 * @string specific form if called from ModuleFormdataListing
	 */
	function loadDCA(DC_Formdata $dca, $varFormKey = '')
	{

		$strModule = 'efg';
		$strName = 'feedback';
		$strFileName = 'tl_formdata';

		if ( $varFormKey != '' && is_string($varFormKey))
		{
			$strFileName = $varFormKey;
		}
		else
		{
			$strFileName = ($this->Input->get('do') == 'feedback' ? 'fd_feedback' : $this->Input->get('do'));
		}

		if ( $varFormKey != '' && is_string($varFormKey) )
		{
			if ($varFormKey != 'tl_formdata' )
			{

				if ( array_key_exists($varFormKey, $GLOBALS['BE_MOD']['formdata']) )
				{
					$strFile = sprintf('%s/system/modules/%s/dca/%s.php', TL_ROOT, $strModule, $strFileName);

					if (file_exists($strFile))
					{
						$strName = $varFormKey;
						include_once($strFile);

						// now replace standard dca tl_formdata by form-dependent dca
						if (is_array($GLOBALS['TL_DCA'][$strName]) && count($GLOBALS['TL_DCA'][$strName]) > 0)
						{
							$GLOBALS['TL_DCA']['tl_formdata'] = $GLOBALS['TL_DCA'][$strName];
							unset($GLOBALS['TL_DCA'][$strName]);
						}
					}
				}
			}
		}
		else
		{
			if ( array_key_exists($this->Input->get('do'), $GLOBALS['BE_MOD']['formdata']) )
			{
				$strFile = sprintf('%s/system/modules/%s/dca/%s.php', TL_ROOT, $strModule, $strFileName);

				if (file_exists($strFile))
				{
					$strName = $this->Input->get('do');
					include_once($strFile);

					// now replace standard dca tl_formdata by form-dependent dca
					if (is_array($GLOBALS['TL_DCA'][$strName]) && count($GLOBALS['TL_DCA'][$strName]) > 0)
					{
						$GLOBALS['TL_DCA']['tl_formdata'] = $GLOBALS['TL_DCA'][$strName];
						unset($GLOBALS['TL_DCA'][$strName]);
					}
				}
			}
		}
		include(TL_ROOT . '/system/config/dcaconfig.php');

	}


	/**
	 * Check permissions to edit table tl_formdata
	 */
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');

		$arrFields = array_keys($GLOBALS['TL_DCA']['tl_formdata']['fields']);
		// check/set restrictions
		foreach ($arrFields as $strField)
		{
			if ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$strField]['exclude'] == true)
			{
				if ($this->User->isAdmin || $this->User->hasAccess('tl_formdata::'.$strField, 'alexf') == true)
				{
					$GLOBALS['TL_DCA']['tl_formdata']['fields'][$strField]['exclude'] = false;
				}
			}
		}
	}


	/**
	 * Autogenerate an alias if it has not been set yet
	 * alias is created from formdata content related to first form field of type text not using rgxp=email
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		$strFormTitle = '';
		if (strlen($dc->strFormFilterValue))
		{
			$strFormTitle = $dc->strFormFilterValue;
		}

		$objFormField = $this->Database->prepare("SELECT ff.name FROM tl_form f, tl_form_field ff WHERE (f.id=ff.pid) AND f.title=? AND ff.type=? AND ff.rgxp NOT IN ('email','date','datim','time') ORDER BY sorting")
							->limit(1)
							->execute($strFormTitle, 'text');

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			// get value from post instead of DB, because the field holding the value will be saved in a later step
			$autoAlias = true;
			$varValue = standardize($this->Input->post($objFormField->name));
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_formdata WHERE alias=?")
								   ->execute($varValue, $dc->id);

		// Check whether the news alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '.' . $dc->id;
		}

		return $varValue;
	}


	/*
	* Create List Label for formdata item
	*/
	public function getRowLabel($arrRow)
	{
		$strRet = '';

		// Titles of all forms
		if (is_null($this->arrData))
		{
			$strSql = "SELECT id,title FROM tl_form";
			$objForms = $this->Database->prepare($strSql)->execute();

			while ($objForms->next())
			{
				$this->arrData[$objForms->id]['title'] = $objForms->title;
			}
		}


		$strRet .= '<div class="fd_wrap"><div class="fd_head"><div class="cte_type unpublished">';
		$strRet .= '<strong>' . date($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date']) . '</strong>';
		$strRet .= '<span style="color:#b3b3b3; padding-left:3px;">[' . $this->arrData[$arrRow['pid']]['title'] . ']</span>';
		$strRet .= '<span style="border:solid 1px blue">#' . $this->arrData[$arrRow['alias']] . '#</span>';
		$strRet .= '</div><p class="fd_notes">' . $arrRow['be_notes'] . '</p></div>';

		$strRet .= '<div class="mark_links">';
		// Details from table tl_formdata_details
		$strSql = "SELECT ff_type,ff_name,ff_label,value FROM tl_formdata_details WHERE pid=? ORDER BY sorting ASC";
		$objDetails = $this->Database->prepare($strSql)->execute($arrRow['id']);

		while ($objDetails->next())
		{
			 $strRet .=  '<div class="fd_row"><div class="fd_label">' .$objDetails->ff_name . ':&nbsp;</div><div class="fd_value">' . $objDetails->value . '&nbsp;</div></div>';
		}

		$strRet .= '</div></div>';

		return $strRet;

	}


	/**
	 * Return all forms as array for dropdown
	 * @return array
	 */
	public function getFormsSelect()
	{
		$forms = array();

		// Get all forms
		$objForms = $this->Database->prepare("SELECT id,title,formID FROM tl_form WHERE storeFormdata=? ORDER BY title ASC")
							->execute("1");
		$forms[] = '-';
		if ($objForms->numRows)
		{
			while ($objForms->next())
			{
				$k = $objForms->title;
				$v = $objForms->title;
				$forms[$k] = $v;
			}
		}
		return $forms;
	}


// TODO: checken, ob per options_callback duchgaengig funzt
// .. dann in DC_FormData ~Zeile 2160 und 2180 umsellen
// .. und templates/master/... anpassen
	/**
	 * Return all members as array for dropdown
	 * @return array
	 */
	public function getMembersSelect()
	{
		$items = array();

		// Get all members
		$objItems = $this->Database->prepare("SELECT id, CONCAT(firstname,' ',lastname) AS fullname FROM tl_member ORDER BY fullname ASC")
							->execute("1");
		//$items[0] = '-';
		if ($objItems->numRows)
		{
			while ($objItems->next())
			{
				$k = $objItems->id;
				$v = $objItems->fullname;
				$items[$k] = $v;
			}
		}
		return $items;
	}

	/**
	 * Return all users as array for dropdown
	 * @return array
	 */
	public function getUsersSelect()
	{
		$items = array();

		// Get all users
		$objItems = $this->Database->prepare("SELECT id, name FROM tl_user ORDER BY name ASC")
							->execute("1");
		//$items[0] = '-';
		if ($objItems->numRows)
		{
			while ($objItems->next())
			{
				$k = $objItems->id;
				$v = $objItems->name;
				$items[$k] = $v;
			}
		}
		return $items;
	}


}

?>