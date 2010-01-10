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
 * PHP version 5
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn
 * @package    efg
 * @license    LGPL
 * @filesource
 */

/**
 * Class ModuleFormdata
 *
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @version    1.12.1
 */
class ModuleFormdata extends Backend
{

	/**
	 * Data container object
	 * @var object
	 */
	protected $objDc;

	/**
	 * Current record
	 * @var array
	 */
	protected $arrData = array();

	protected $arrStoreForms = null;

	protected $arrFormsDcaKey = null;

	protected $objForm;

	// Types of form fields with storable data
	protected $arrFFstorable = array();

	public function __construct()
	{
		parent::__construct();

		$this->loadDataContainer('tl_form_field');

		// Types of form fields with storable data
		$this->arrFFstorable = array(
			'sessionText', 'sessionOption', 'sessionCalculator', 
			'hidden','text','calendar','password','textarea',
			'select','efgImageSelect','conditionalselect', 'countryselect', 'fp_preSelectMenu','efgLookupSelect',
			'radio','efgLookupRadio',
			'checkbox','efgLookupCheckbox',
			'upload'
		);

		//$arrFFignore = array('fieldset','condition','submit','efgFormPaginator','captcha','headline','explanation','html');
		//$this->arrFFstorable = array_diff(array_keys($GLOBALS['TL_FFL']), $arrFFignore);
	}

	public function generate()
	{

		$this->getStoreForms();

		if ($this->Input->get('do') && $this->Input->get('do') != "feedback")
		{
			if ($this->arrStoreForms[$this->Input->get('do')])
			{
				$session = $this->Session->getData();
				$session['filter']['tl_feedback']['form'] = $this->arrStoreForms[$this->Input->get('do')]['title'];

				$this->Session->setData($session);
			}
		}

		if ( $this->Input->get('act') == "" )
		{
			return $this->objDc->showAll();
		}
		else
		{
			$act = $this->Input->get('act');
			return $this->objDc->$act();
		}
	}


	/*
	 * Get all Forms marked to store data in database
	 */
	public function getStoreForms()
	{
		if ( !$this->arrStoreForms)
		{
			$objForms = $this->Database->prepare("SELECT f.id,f.title,f.formID FROM tl_form f, tl_form_field ff WHERE (f.id=ff.pid) AND (f.storeFormdata=?) ORDER BY f.title")
										->execute("1");
			while ($objForms->next())
			{
				if (strlen($objForms->formID)) {
					$varKey = $objForms->formID;
				}
				else
				{
					$varKey = str_replace('-', '_', standardize($objForms->title));
				}
				$this->arrStoreForms[$varKey] = $objForms->row();
				$this->arrFormsDcaKey[$varKey] = $objForms->title;
			}
		}

		return $this->arrStoreForms;
	}


	/**
	 * Create DCA files
	 */
	public function createFormdataDca(DataContainer $dc)
	{
		$this->intFormId = $dc->id;

		$this->objForm = $this->Database->prepare("SELECT id,title,formID,allowTags,storeValues,storeFormdata,sendConfirmationMail,confirmationMailText,sendFormattedMail,efgStoreValues FROM tl_form WHERE id=?")
						->execute($this->intFormId)
						->fetchAssoc();
		$this->updateConfig();
	}

	/**
	 * Callback edit button
	 * @return array
	 */
	public function callbackEditButton($row, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
	{
		if (!$this->arrStoreForms)
		{
			$this->getStoreForms();
		}

		$return = '';

		$strDcaKey = array_search($row['form'], $this->arrFormsDcaKey);
		if ($strDcaKey)
		{
			$return .= '<a href="'.$this->addToUrl($href.'&amp;do=fd_'.$strDcaKey.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		}

		return $return;
	}


	/**
	 * Update efg/config.php, dca-files
	 */
	private function updateConfig()
	{

		$this->import('String');

		$arrStoreForms = $this->getStoreForms();

		// config/config.php
		$tplConfig = $this->newTemplate('master/efg_config');
		$tplConfig->arrForm = $this->objForm;
		$tplConfig->arrStoreForms = $arrStoreForms;

		$objConfig = new File('system/modules/efg/config/config.php');
		$objConfig->write($tplConfig->parse());
		$objConfig->close();

		if (!$arrStoreForms || count($arrStoreForms) == 0)
		{
			return;
		}

		// languages/modules.php
		$arrModLangs = scan(TL_ROOT . '/system/modules/efg/languages');

		$arrLanguages = $this->getLanguages();

		foreach ($arrModLangs as $strModLang)
		{
			if (array_key_exists($strModLang, $arrLanguages))
			{
				//$this->loadLanguageFile('tl_efg_modules.php');
				$strFile = sprintf('%s/system/modules/%s/languages/%s/%s.php', TL_ROOT, 'efg', $strModLang, 'tl_efg_modules');
				if (file_exists($strFile))
				{
					include($strFile);
				}

				$tplMod = 'tplMod_' . $strModLang;
				//$tplMod = $this->newTemplate('master/efg_'.$strModLang.'_modules');
				$tplMod = $this->newTemplate('master/efg_modules');
				$tplMod->arrForm = $this->objForm;
				$tplMod->arrStoreForms = $arrStoreForms;

				$objMod = 'objMod_' . $strModLang;
				$objMod = new File('system/modules/efg/languages/'.$strModLang.'/modules.php');
				$objMod->write($tplMod->parse());
				$objMod->close();
			}
		}

		// dca/fd_FORMKEY.php
		if ($this->objForm && count($this->objForm)>0 )
		{
			$arrFields = array();
			$arrFieldNamesById = array();
			// Get all form fields of this form
			$objFields = $this->Database->prepare("SELECT id,pid,type,name,label,value,options,efgLookupOptions,multiple,mSize,mandatory,rgxp,maxlength,extensions,size,storeFile,uploadFolder,useHomeDir,efgMultiSRC,efgImageMultiple,efgImageSortBy,efgImagePerRow,efgImageSize,efgImageFullsize,efgImageMargin".($this->Database->fieldExists('conditionField', 'tl_form_field') ? ', conditionField' : '')." FROM tl_form_field WHERE pid=? ORDER BY sorting ASC")
								->execute($this->objForm['id']);
			if ($objFields->numRows)
			{
				while ($objFields->next())
				{
					$arrField = $objFields->row();
					$strFieldKey = ( strlen($arrField['name']) ) ? $arrField['name'] : $arrField['id'];
					if (in_array($arrField['type'], $this->arrFFstorable))
					{
						// ignore some special fields like checkbox CC, fields of type password ...
						if (($arrField['type']=='checkbox' && $strFieldKey=='cc') || $arrField['type']=='password' )
						{
							continue;
						}
						$arrFields[$strFieldKey] = $arrField;
						$arrFieldNamesById[$arrField['id']] = $strFieldKey;
					}
				}
			}

			$strFormKey = ( isset($this->objForm['formID']) && strlen($this->objForm['formID']) ) ? $this->objForm['formID'] : str_replace('-', '_', standardize($this->objForm['title']));
			$tplDca = 'tplDca_' . $strFormKey;
			$tplDca = $this->newTemplate('master/efg_dca_formdata');
			$tplDca->strFormKey = $strFormKey;
			$tplDca->arrForm = $this->objForm;
			$tplDca->arrStoreForms = $arrStoreForms;
			$tplDca->arrFields = $arrFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

			$blnBackendMail = false;
			if ($this->objForm['sendConfirmationMail'] || strlen($this->objForm['confirmationMailText']) )
			{
				$blnBackendMail = true;
			}
			$tplDca->blnBackendMail = $blnBackendMail;

			$objDca = 'objDca_' . $strFormKey;
			$objDca = new File('system/modules/efg/dca/fd_' . $strFormKey . '.php');
			$objDca->write($tplDca->parse());
			$objDca->close();
		}

		// overall dca/fd_feedback.php
		// Get all form fields of all storing forms
		if (count($arrStoreForms)>0)
		{
			$arrAllFields = array();
			$arrFieldNamesById = array();
			$objAllFields = $this->Database->prepare("SELECT ff.id,ff.pid,ff.type,ff.name,ff.label,ff.value,ff.options,ff.efgLookupOptions,ff.multiple,ff.mSize,ff.mandatory,ff.rgxp,ff.maxlength,ff.extensions,ff.size,ff.storeFile,ff.uploadFolder,ff.useHomeDir,ff.efgMultiSRC,ff.efgImageSortBy,ff.efgImagePerRow,ff.efgImageSize,ff.efgImageFullsize,ff.efgImageMargin".($this->Database->fieldExists('conditionField', 'tl_form_field') ? ', ff.conditionField' : '')." FROM tl_form_field ff, tl_form f WHERE ff.pid=f.id AND f.storeFormdata=? ORDER BY ff.pid ASC, ff.sorting ASC")
								->execute("1");
			if ($objAllFields->numRows)
			{
				while ($objAllFields->next())
				{
					$arrField = $objAllFields->row();
					$strFieldKey = (strlen($arrField['name']) ? $arrField['name'] : $arrField['id']);
					if (in_array($arrField['type'], $this->arrFFstorable))
					{
						// ignore some special fields like checkbox CC, fields of type password ...
						if (($arrField['type']=='checkbox' && $strFieldKey=='cc') || $arrField['type']=='password' )
						{
							continue;
						}
						$arrAllFields[$strFieldKey] = $arrField;
						$arrFieldNamesById[$arrField['id']] = $strFieldKey;
					}
				}
			}

			$strFormKey = 'feedback';
			$tplDca = 'tplDca_' . $strFormKey;
			$tplDca = $this->newTemplate('master/efg_dca_formdata');
			$tplDca->arrForm = array('key' => 'feedback', 'title'=>"Feedback");
			$tplDca->arrStoreForms = $arrStoreForms;
			$tplDca->arrFields = $arrAllFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

			$objDca = 'objDca_' . $strFormKey;
			$objDca = new File('system/modules/efg/dca/fd_' . $strFormKey . '.php');
			$objDca->write($tplDca->parse());
			$objDca->close();

		}
	}


	/**
	 * Return a new template object
	 * @param string
	 * @param object
	 * @return object
	 */
	private function newTemplate($strTemplate)
	{
		$objTemplate = new BackendTemplate($strTemplate);

		$objTemplate->folder = 'efg';

		return $objTemplate;
	}


}

?>