<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   Efg
 * @author    Thomas Kuhn <mail@th-kuhn.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Thomas Kuhn 2007-2012
 */


/**
 * Namespace
 */
namespace Efg;

/**
 * Class ModuleFormdata
 *
 * @copyright  Thomas Kuhn 2007-2012
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Efg
 */
class ModuleFormdata extends \Backend
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
		$this->import('Formdata');

		// Types of form fields with storable data
		$this->arrFFstorable = $this->Formdata->arrFFstorable;
	}

	public function generate()
	{
		if (\Input::get('do') && \Input::get('do') != "feedback")
		{
			if ($this->Formdata->arrStoreForms[\Input::get('do')])
			{
				$session = $this->Session->getData();
				$session['filter']['tl_feedback']['form'] = $this->Formdata->arrStoreForms[\Input::get('do')]['title'];

				$this->Session->setData($session);
			}
		}

		if (\Input::get('act') == "")
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
		$arrStoreForms = $this->Formdata->arrStoreForms;

		// config/config.php
		$tplConfig = $this->newTemplate('efg_internal_config');
		$tplConfig->arrForm = $this->objForm;
		$tplConfig->arrStoreForms = $arrStoreForms;

		$objConfig = new \File('system/modules/efg/config/config.php');
		$objConfig->write($tplConfig->parse());
		$objConfig->close();

		if (empty($arrStoreForms))
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
				$strFile = sprintf('%s/system/modules/%s/languages/%s/%s.php', TL_ROOT, 'efg', $strModLang, 'tl_efg_modules');
				if (file_exists($strFile))
				{
					include($strFile);
				}

				$tplMod = 'tplMod_' . $strModLang;
				$tplMod = $this->newTemplate('efg_internal_modules');
				$tplMod->arrForm = $this->objForm;
				$tplMod->arrStoreForms = $arrStoreForms;

				$objMod = 'objMod_' . $strModLang;
				$objMod = new \File('system/modules/efg/languages/'.$strModLang.'/modules.php');
				$objMod->write($tplMod->parse());
				$objMod->close();
			}
		}

		// dca/fd_FORMKEY.php
		if ($this->objForm && !empty($this->objForm))
		{
			$arrFields = array();
			$arrFieldNamesById = array();
			// Get all form fields of this form
			$objFields = \Database::getInstance()->prepare("SELECT * FROM tl_form_field WHERE pid=? ORDER BY sorting ASC")
				->execute($this->objForm['id']);
			if ($objFields->numRows)
			{
				while ($objFields->next())
				{
					$arrField = $objFields->row();
					$strFieldKey = (strlen($arrField['name'])) ? $arrField['name'] : $arrField['id'];
					if (in_array($arrField['type'], $this->arrFFstorable))
					{
						// ignore some special fields like checkbox CC, fields of type password ...
						if (($arrField['type'] == 'checkbox' && $strFieldKey == 'cc') || $arrField['type'] == 'password')
						{
							continue;
						}
						$arrFields[$strFieldKey] = $arrField;
						$arrFieldNamesById[$arrField['id']] = $strFieldKey;
					}
				}
			}

			$strFormKey = (isset($this->objForm['formID']) && strlen($this->objForm['formID'])) ? $this->objForm['formID'] : str_replace('-', '_', standardize($this->objForm['title']));
			$tplDca = 'tplDca_' . $strFormKey;
			$tplDca = $this->newTemplate('efg_internal_dca_formdata');
			$tplDca->strFormKey = $strFormKey;
			$tplDca->arrForm = $this->objForm;
			$tplDca->arrStoreForms = $arrStoreForms;
			$tplDca->arrFields = $arrFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

			$blnBackendMail = false;
			if ($this->objForm['sendConfirmationMail'] || strlen($this->objForm['confirmationMailText']))
			{
				$blnBackendMail = true;
			}
			$tplDca->blnBackendMail = $blnBackendMail;

			$objDca = 'objDca_' . $strFormKey;
			$objDca = new \File('system/modules/efg/dca/fd_' . $strFormKey . '.php');
			$objDca->write($tplDca->parse());
			$objDca->close();
		}

		// overall dca/fd_feedback.php
		// Get all form fields of all storing forms
		if (!empty($arrStoreForms))
		{
			$arrAllFields = array();
			$arrFieldNamesById = array();
			$objAllFields = \Database::getInstance()->prepare("SELECT ff.* FROM tl_form_field ff, tl_form f WHERE ff.pid=f.id AND f.storeFormdata=? ORDER BY ff.pid ASC, ff.sorting ASC")
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
						if (($arrField['type'] == 'checkbox' && $strFieldKey == 'cc') || $arrField['type'] == 'password')
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
			$tplDca = $this->newTemplate('efg_internal_dca_formdata');
			$tplDca->arrForm = array('key' => 'feedback', 'title'=> $this->objForm['title']);
			$tplDca->arrStoreForms = $arrStoreForms;
			$tplDca->arrFields = $arrAllFields;
			$tplDca->arrFieldNamesById = $arrFieldNamesById;

			$objDca = 'objDca_' . $strFormKey;
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
