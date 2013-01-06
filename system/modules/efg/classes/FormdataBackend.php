<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   Efg
 * @author    Thomas Kuhn <mail@th-kuhn.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Thomas Kuhn 2007-2013
 */


/**
 * Namespace
 */
namespace Efg;

/**
 * Class FormdataBackend
 *
 * @copyright  Thomas Kuhn 2007-2013
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Efg
 */
class FormdataBackend extends \Backend
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

	protected $objForm;

	// Types of form fields with storable data
	protected $arrFFstorable = array();

	// Mapping of frontend form fields to backend widgets
	protected $arrMapTL_FFL = array();

	public function __construct()
	{
		parent::__construct();

		$this->loadDataContainer('tl_form_field');
		$this->import('Formdata');

		// Types of form fields with storable data
		$this->arrFFstorable = $this->Formdata->arrFFstorable;

		// Mapping of frontend form fields to backend widgets
		$this->arrMapTL_FFL = $this->Formdata->arrMapTL_FFL;
	}

	public function generate()
	{
		if (\Input::get('do') && \Input::get('do') != "feedback")
		{
			if ($this->Formdata->arrStoringForms[\Input::get('do')])
			{
				$session = $this->Session->getData();
				$session['filter']['tl_feedback']['form'] = $this->Formdata->arrStoringForms[\Input::get('do')]['title'];

				$this->Session->setData($session);
			}
		}

		if (\Input::get('act') == '')
		{
			return $this->objDc->showAll();
		}
		else
		{
			$act = \Input::get('act');
			return $this->objDc->$act();
		}
	}

	/**
	 * Create DCA files
	 */
	public function createFormdataDca(\DataContainer $dc)
	{
		$this->intFormId = $dc->id;

		$this->objForm = \Database::getInstance()->prepare("SELECT * FROM tl_form WHERE id=?")
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
		$return = '';

		$strDcaKey = array_search($row['form'], $this->Formdata->arrFormsDcaKey);
		if ($strDcaKey)
		{
			$return .= '<a href="'.$this->addToUrl($href.'&amp;do=fd_'.$strDcaKey.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		}

		return $return;
	}

	/**
	 * Update efg/config/config.php, dca and language files
	 */
	private function updateConfig()
	{
		$arrStoringForms = $this->Formdata->arrStoringForms;

		// config/config.php
		$tplConfig = $this->newTemplate('efg_internal_config');
		$tplConfig->arrForm = $this->objForm;
		$tplConfig->arrStoringForms = $arrStoringForms;

		$objConfig = new \File('system/modules/efg/config/config.php');
		$objConfig->write($tplConfig->parse());
		$objConfig->close();

		if (empty($arrStoringForms))
		{
			return;
		}

//		// Check for Contao 3 database assisted file manager
//		$blnDatabaseAssistedFileManager = false;
//		if (version_compare(VERSION, '3.0', '>='))
//		{
//			$this->loadDataContainer('tl_files');
//
//			if (isset($GLOBALS['TL_DCA']['tl_files']) && $GLOBALS['TL_DCA']['tl_files']['config']['databaseAssisted'])
//			{
//				$blnDatabaseAssistedFileManager = true;
//			}
//
//		}

		// languages/modules.php
		$arrModLangs = scan(TL_ROOT . '/system/modules/efg/languages');
		$arrLanguages = $this->getLanguages();

		foreach ($arrModLangs as $strModLang)
		{
			if (array_key_exists($strModLang, $arrLanguages))
			{
				$strFile = sprintf('%s/system/modules/%s/languages/%s/%s.php', TL_ROOT, 'efg', $strModLang, 'tl_efg_modules');
				if (file_exists($strFile))
				{
					include($strFile);
				}

				$tplMod = $this->newTemplate('efg_internal_modules');
				$tplMod->arrForm = $this->objForm;
				$tplMod->arrStoringForms = $arrStoringForms;

				$objMod = new \File('system/modules/efg/languages/'.$strModLang.'/modules.php');
				$objMod->write($tplMod->parse());
				$objMod->close();
			}
		}

		// dca/fd_FORMKEY.php
		if (!empty($this->objForm))
		{
			$arrFields = array();
			$arrFieldNamesById = array();
			// Get all form fields of this form
			$arrFormFields = $this->Formdata->getFormFieldsAsArray($this->objForm['id']);

			if (!empty($arrFormFields))
			{
				foreach ($arrFormFields as $strFieldKey => $arrField)
				{
					// Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
					if (!in_array($arrField['type'], $this->arrFFstorable)
						|| ($arrField['type'] == 'checkbox' && $strFieldKey == 'cc'))
					{
						continue;
					}

					$arrFields[$strFieldKey] = $arrField;
					$arrFieldNamesById[$arrField['id']] = $strFieldKey;
				}
			}

			$strFormKey = (isset($this->objForm['formID']) && strlen($this->objForm['formID'])) ? $this->objForm['formID'] : str_replace('-', '_', standardize($this->objForm['title']));

			$tplDca = $this->newTemplate('efg_internal_dca_formdata');
			$tplDca->strFormKey = $strFormKey;
			$tplDca->arrForm = $this->objForm;
			$tplDca->arrStoringForms = $arrStoringForms;
			$tplDca->arrFields = $arrFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

//			// Contao 3 database assisted file manager
//			$tplDca->blnDatabaseAssistedFileManager = $blnDatabaseAssistedFileManager;

			// Enable backend confirmation mail
			$blnBackendMail = false;
			if ($this->objForm['sendConfirmationMail'] || strlen($this->objForm['confirmationMailText']))
			{
				$blnBackendMail = true;
			}
			$tplDca->blnBackendMail = $blnBackendMail;

			$objDca = new \File('system/modules/efg/dca/fd_' . $strFormKey . '.php');
			$objDca->write($tplDca->parse());
			$objDca->close();
		}

		// overall dca/fd_feedback.php
		// Get all form fields of all storing forms
		if (!empty($arrStoringForms))
		{
			$arrAllFields = array();
			$arrFieldNamesById = array();

			foreach ($arrStoringForms as $strFormKey => $arrForm)
			{
				// Get all form fields of this form
				$arrFormFields = $this->Formdata->getFormFieldsAsArray($arrForm['id']);

				if (!empty($arrFormFields))
				{
					foreach ($arrFormFields as $strFieldKey => $arrField)
					{
						// Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
						if (!in_array($arrField['type'], $this->arrFFstorable)
							|| ($arrField['type'] == 'checkbox' && $strFieldKey == 'cc'))
						{
							continue;
						}

						$arrAllFields[$strFieldKey] = $arrField;
						$arrFieldNamesById[$arrField['id']] = $strFieldKey;
					}
				}

			}

			$strFormKey = 'feedback';

			$tplDca = $this->newTemplate('efg_internal_dca_formdata');
			$tplDca->arrForm = array('key' => 'feedback', 'title'=> $this->objForm['title']);
			$tplDca->arrStoringForms = $arrStoringForms;
			$tplDca->arrFields = $arrAllFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

//			// Contao 3 database assisted file manager
//			$tplDca->blnDatabaseAssistedFileManager = $blnDatabaseAssistedFileManager;

			$objDca = new \File('system/modules/efg/dca/fd_' . $strFormKey . '.php');
			$objDca->write($tplDca->parse());
			$objDca->close();

		}
	}

	/**
	 * Return a new template object
	 * @param string
	 * @return object
	 */
	private function newTemplate($strTemplate)
	{
		$objTemplate = new \BackendTemplate($strTemplate);
		$objTemplate->folder = 'efg';

		return $objTemplate;
	}


	/**
	 * Import Form data from CSV file
	 * @param object Datacontainer
	 * @return string CSV imort form
	 */
	public function importCsv($dc)
	{
		if (\Input::get('key') != 'import')
		{
			return '';
		}

		return $dc->importFile();
	}

}
