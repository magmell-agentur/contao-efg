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
 * @copyright  Leo Feyer 2007
 * @author     Leo Feyer
 * @filesource
 */

/**
 * Class FormData
 *
 * Provide methods to handle data stored in tables tl_formdata and tl_formdata_details.
 *
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    Controller
 * @version    1.11.0
 */
class FormData extends Controller
{
	/**
	 * Items in tl_form, all forms marked to store data in tl_formdata
	 * @param array
	 */
	protected $arrStoreForms = null;

	protected $arrFormsDcaKey = null;
	protected $arrFormdataDetailsKey = null;
	/**
	 * Types of form fields with storable data
	 * @var array
	 */
	protected $arrFFstorable = array();

	protected $strFdDcaKey = null;

	protected $arrListingPages = null;
	protected $arrSearchableListingPages = null;

	public function __construct()
	{

		$this->import('Database');
		$this->import('String');

		$this->arrFFstorable = array('hidden','text','password','textarea','select', 'conditionalselect','efgLookupSelect','radio','efgLookupRadio','checkbox','efgLookupCheckbox','upload','efgImageSelect');

		$this->getStoreForms();

		parent::__construct();
	}

	/**
	 * Set an object property
	 * @param string
	 * @return mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'FdDcaKey':
				$this->strFdDcaKey = $varValue;
				break;
			default:
				$this->arrData[$strKey] = $varValue;
				break;
		}
	}

	/**
	 * Return an object property
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'FdDcaKey':
				return $this->strFdDcaKey;
				break;
			case 'arrFFstorable':
				return $this->arrFFstorable;
				break;
			case 'arrStoreForms':
				return $this->arrStoreForms;
				break;
			default:
				return $this->arrData[$strKey];
				break;
		}
	}


	/**
	 * Autogenerate an alias if it has not been set yet
	 * alias is created from formdata content related to first form field of type text not using rgxp=email
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateAlias($varValue=null, $strFormTitle=null, $intRecId=null)
	{

		$autoAlias = false;
		$strAliasField = '';

		if (is_null($strFormTitle))
		{
			return '';
		}
		if (intval($intRecId)==0)
		{
			return '';
		}

		// get field used to build alias
		$objForm = $this->Database->prepare("SELECT id, efgAliasField FROM tl_form WHERE title=?")
					->limit(1)
					->execute($strFormTitle);
		if ($objForm->numRows)
		{
			 if (strlen($objForm->efgAliasField))
			 {
			 	$strAliasField = $objForm->efgAliasField;
			 }
		}

		if ($strAliasField == '')
		{
			$objFormField = $this->Database->prepare("SELECT ff.name FROM tl_form f, tl_form_field ff WHERE (f.id=ff.pid) AND f.title=? AND ff.type=? AND ff.rgxp NOT IN ('email','date','datim','time') ORDER BY sorting")
							->limit(1)
							->execute($strFormTitle, 'text');

			if ($objFormField->numRows)
			{
				$strAliasField = $objFormField->name;
			}

		}

		// Generate alias if there is none
		if (is_null($varValue) || !strlen($varValue))
		{
			if (strlen($strAliasField))
			{
				// get value from post
				$autoAlias = true;
				$varValue = standardize($this->Input->post($strAliasField));
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_formdata WHERE alias=? AND id != ?")
								   ->execute($varValue, $intRecId);

		// Check whether the alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= (strlen($varValue) ? '.' : '') . $intRecId;
		}

		return $varValue;
	}

	/**
	 * Get Listing Detail page ID from url
	 * is used no longer, just for backwards compatibility (not actual formdata dca files)
	 */
	public function getDetailPageIdFromUrl($arrFragments) {
		return $arrFragments;
	}

	/**
	 * Add formdata details to the indexer
	 * @param array
	 * @param integer
	 * @return array
	 */
	public function getSearchablePages($arrPages, $intRoot=0)
	{

		$arrRoot = array();

		if ($intRoot > 0)
		{
			$arrRoot = $this->getChildRecords($intRoot, 'tl_page', true);
		}

		$this->getSearchableListingPages();

		if (is_array($this->arrSearchableListingPages) && count($this->arrSearchableListingPages)>0)
		{
			foreach ($this->arrSearchableListingPages as $pageId => $arrParams)
			{
				if (is_array($arrRoot) && count($arrRoot) > 0 && !in_array($pageId, $arrRoot))
				{
					continue;
				}

				$strForm = '';
				if (is_array($arrParams) && strlen($arrParams['list_formdata']))
				{
					$strFormsKey = substr($arrParams['list_formdata'], strlen('fd_'));
					if (isset($this->arrFormsDcaKey[$strFormsKey]))
					{
						$strForm = $this->arrFormsDcaKey[$strFormsKey];
					}
				}

				$pageAlias = (strlen($arrParams['alias']) ? $arrParams['alias'] : null);

				if (strlen($strForm))
				{
					$strFormdataDetailsKey = 'details';
					if (strlen($arrParams['formdataDetailsKey']))
					{
						$strFormdataDetailsKey = $arrParams['formdataDetailsKey'];
					}

					if (VERSION == '2.5' && intval(BUILD) <= 9)
					{
						$domain = '';
					}
					else
					{
						// Determine domain
						if (intval($pageId)>0)
						{
							$domain = $this->Environment->base;
							$objParent = $this->getPageDetails($pageId);
							if (strlen($objParent->domain))
							{
								$domain = ($this->Environment->ssl ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
							}
						}
					}

					$objData = $this->Database->prepare("SELECT id,alias FROM tl_formdata WHERE form=?")
								->execute($strForm);
					while ($objData->next())
					{
						$strUrl = $domain . $this->generateFrontendUrl(array('id'=>$pageId, 'alias'=>$pageAlias), '/'.$strFormdataDetailsKey.'/%s');
						$strUrl = sprintf($strUrl, ((strlen($objData->alias) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objData->alias : $objData->id));
						$arrPages[] = $strUrl;
					}
				}

			}

		}

		return $arrPages;

	}



	/*
	 * Get all forms marked to store data in tl_formdata
	 */
	private function getStoreForms()
	{
		if (!$this->arrStoreForms)
		{
			// get all forms marked to store data
			$objForms = $this->Database->prepare("SELECT id,title,formID,useFormValues,useFieldNames FROM tl_form WHERE storeFormdata=?")
											->execute("1");

			while ($objForms->next())
			{
				if (strlen($objForms->formID)) {
					$varKey = str_replace('-', '_', standardize($objForms->formID));
				}
				else
				{
					$varKey = str_replace('-', '_', standardize($objForms->title));
				}
				$this->arrStoreForms[$varKey] = $objForms->row();
				$this->arrFormsDcaKey[$varKey] = $objForms->title;
			}
		}
	}


	/*
	 * Get all pages containig frontend module formdata listing
	 */
	private function getListingPages()
	{
		if (!$this->arrListingPages)
		{
			// get all pages containig listing formdata
			$objListingPages = $this->Database->prepare("SELECT tl_page.id,tl_page.alias FROM tl_page, tl_content, tl_article, tl_module WHERE (tl_page.id=tl_article.pid AND tl_article.id=tl_content.pid AND tl_content.module=tl_module.id) AND tl_content.type=? AND tl_module.type=?")
									->execute("module", "formdatalisting");
			while ($objListingPages->next())
			{
				$this->arrListingPages[$objListingPages->id] = $objListingPages->alias;
			}
		}

		return $this->arrListingPages;
	}

	/*
	 * Get all pages for search indexer
	 */
	private function getSearchableListingPages()
	{
		if (!$this->arrSearchableListingPages)
		{
			// get all pages containig listing formdata
			$objListingPages = $this->Database->prepare("SELECT tl_page.id,tl_page.alias,tl_page.protected,tl_module.list_formdata,tl_module.efg_DetailsKey FROM tl_page, tl_content, tl_article, tl_module WHERE (tl_page.id=tl_article.pid AND tl_article.id=tl_content.pid AND tl_content.module=tl_module.id) AND tl_content.type=? AND tl_module.type=? AND (tl_page.start=? OR tl_page.start<?) AND (tl_page.stop=? OR tl_page.stop>?) AND tl_page.published=?")
									->execute("module", "formdatalisting", '', time(), '', time(), 1);
			while ($objListingPages->next())
			{
				$strFormdataDetailsKey = 'details';
				if (strlen($objListingPages->efg_DetailsKey)) {
					$strFormdataDetailsKey = $objListingPages->efg_DetailsKey;
				}
				$this->arrSearchableListingPages[$objListingPages->id] = array('formdataDetailsKey' => $strFormdataDetailsKey, 'alias' => $objListingPages->alias, 'protected' => $objListingPages->protected, 'list_formdata' => $objListingPages->list_formdata);
			}
		}
		return $this->arrSearchableListingPages;
	}


	/*
	 * Return record from tl_formdata as Array('fd_base' => base fields from tl_formdata, 'fd_details' => detail fields from tl_formdata_details)
	 * @param integer ID of tl_formdata record
	 * @return mixed
	 */
	public function getFormdataAsArray($intId=0)
	{

		$varReturn = array();

		if($intId > 0)
		{
			$objFormdata = $this->Database->prepare("SELECT * FROM tl_formdata WHERE id=?")
										->execute($intId);
			if ($objFormdata->numRows == 1)
			{
				$varReturn['fd_base'] = $objFormdata->fetchAssoc();

				$objFormdataDetails = $this->Database->prepare("SELECT * FROM tl_formdata_details WHERE pid=?")
										->execute($intId);
				if ($objFormdataDetails->numRows)
				{
					$arrTemp = $objFormdataDetails->fetchAllAssoc();
					foreach ($arrTemp as $k => $arr)
					{
						$varReturn['fd_details'][$arr['ff_name']] = $arr;
					}
					unset($arrTemp);
				}
			}

			return $varReturn;

		}
		else
		{
			return false;
		}
	}

	/*
	 * Return form fields as associative array
	 * @param integer ID of tl_form record
	 * @return mixed
	 */
	public function getFormfieldsAsArray($intId=0)
	{

		$varReturn = array();

		if ($intId > 0)
		{
			$objFormFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE pid=? ORDER BY sorting ASC")
											->execute($intId);

			while ($objFormFields->next())
			{
				if (strlen($objFormFields->name)) {
					$varKey = $objFormFields->name;
				}
				else
				{
					$varKey = $objFormFields->id;
				}
				$varReturn[$varKey] = $objFormFields->row();
			}

			return $varReturn;

		}
		else
		{
			return false;
		}
	}

	/*
	 * Prepare post value for tl_formdata / tl_formdata_details DB record
	 * @param mixed post value
	 * @param array form field properties
	 * @param mixed file
	 * @return mixed
	 */
	public function preparePostValForDb($varSubmitted='', $arrField=false, $varFile=false)
	{

		if (!is_array($arrField))
		{
			return false;
		}

		$strType = $arrField['type'];
		$strVal = '';


		if ( in_array($strType, $this->arrFFstorable) )
		{

			switch ($strType)
			{
				case 'efgLookupCheckbox':
				case 'checkbox':
					$strSep = '';
					$strVal = '';
					$arrOptions = $this->prepareDcaOptions($arrField);

					$arrSel = array();
					if (is_string($varSubmitted))
					{
						$arrSel[] = $varSubmitted;
					}
					if (is_array($varSubmitted))
					{
						$arrSel = $varSubmitted;
					}

					foreach ($arrOptions as $o => $mxVal)
					{
						if (in_array($mxVal['value'], $arrSel))
						{
							//$strVal .= $strSep . $arrOptions[$o]['label'];
							if ($strType=='checkbox' && $arrField['eval']['efgStoreValues'])
							{
								$strVal .= $strSep . $arrOptions[$o]['value'];
							}
							else
							{
								$strVal .= $strSep . $arrOptions[$o]['label'];
							}
							$strSep = '|';
						}
					}

					if ( $strVal == '')
					{
						$strVal = $varSubmitted;
					}
				break;
				case 'efgLookupRadio':
				case 'radio':
					$strVal = $varSubmitted;
					$arrOptions = $this->prepareDcaOptions($arrField);
					foreach ($arrOptions as $o => $mxVal)
					{
						if ($mxVal['value'] == $varSubmitted)
						{
							//$strVal = $arrOptions[$o]['label'];
							if ($strType=='radio' && $arrField['eval']['efgStoreValues'])
							{
								$strVal = $arrOptions[$o]['value'];
							}
							else
							{
								$strVal = $arrOptions[$o]['label'];
							}
						}
					}
				break;
				case 'efgLookupSelect':
				case 'conditionalselect':
				case 'select':
					$strSep = '';
					$strVal = '';
					$arrOptions = $this->prepareDcaOptions($arrField);

					// select multiple
					if (is_array($varSubmitted))
					{
						foreach ($arrOptions as $o => $mxVal)
						{
							if (in_array($mxVal['value'], $varSubmitted))
							{
								//$strVal .= $strSep . $arrOptions[$o]['label'];
								if (($strType=='select' || $strType=='conditionalselect' ) && $arrField['eval']['efgStoreValues'])
								{
									$strVal .= $strSep . $arrOptions[$o]['value'];
								}
								else
								{
									$strVal .= $strSep . $arrOptions[$o]['label'];
								}
								$strSep = '|';
							}
						}
					}

					// select single
					if (is_string($varSubmitted))
					{
						foreach ($arrOptions as $o => $mxVal)
						{
							if ($mxVal['value'] == $varSubmitted)
							{
								//$strVal = $arrOptions[$o]['label'];
								if (($strType=='select' || $strType=='conditionalselect' ) && $arrField['eval']['efgStoreValues'])
								{
									$strVal = $arrOptions[$o]['value'];
								}
								else
								{
									$strVal = $arrOptions[$o]['label'];
								}
							}
						}
					}
				break;
				case 'upload':
					$strVal = '';
					if (strlen($varFile['name']))
					{
						if ($arrField['storeFile'] == "1")
						{
							if ($arrField['useHomeDir'] == "1")
							{
								// Overwrite upload folder with user home directory
								if (FE_USER_LOGGED_IN)
								{
									$this->import('FrontendUser', 'User');
									if ($this->User->assignDir && $this->User->homeDir && is_dir(TL_ROOT . '/' . $this->User->homeDir))
									{
										$arrField['uploadFolder'] = $this->User->homeDir;
									}
								}
							}
							$strVal = $arrField['uploadFolder'] . '/' . $varFile['name'];
						}
						else
						{
							$strVal = $varFile['name'];
						}
					}

				break;
				case 'password':
				case 'hidden':
				case 'text':
				case 'textarea':
				default:
					$strVal = $varSubmitted;
					if (strlen($strVal) && in_array($arrField['rgxp'], array('date', 'time', 'datim')))
					{
						$objDate = new Date($strVal, $GLOBALS['TL_CONFIG'][$arrField['rgxp'] . 'Format']);
						$strVal = $objDate->tstamp;
					}
					else
					{
						$strVal = $varSubmitted;
					}
				break;
			}

			return $this->String->decodeEntities($strVal);
			//return $strVal;

		} // if in_array arrFFstorable
		else
		{
			return $varSubmitted;
		}

	}

	/*
	 * Prepare post value for Mail / Text
	 * @param mixed post value
	 * @param array form field properties
	 * @param mixed file
	 * @return mixed
	 */
	public function preparePostValForMail($varSubmitted='', $arrField=false, $varFile=false)
	{

		if (!is_array($arrField))
		{
			return false;
		}

		$strType = $arrField['type'];
		$strVal = '';

		if ( in_array($strType, $this->arrFFstorable) )
		{

			switch ($strType)
			{
				case 'efgLookupCheckbox':
				case 'checkbox':
					$strSep = '';
					$strVal = '';
					$arrOptions = $this->prepareDcaOptions($arrField);

					$arrSel = array();
					if (is_string($varSubmitted))
					{
						$arrSel[] = $varSubmitted;
					}
					if (is_array($varSubmitted))
					{
						$arrSel = $varSubmitted;
					}
					foreach ($arrOptions as $o => $mxVal)
					{
						if (in_array($mxVal['value'], $arrSel))
						{
							$strVal .= $strSep . $arrOptions[$o]['label'];
							$strSep = ', ';
						}
					}

					if ( $strVal == '')
					{
						$strVal = $varSubmitted;
					}
				break;
				case 'efgLookupRadio':
				case 'radio':
					$strVal = (is_array($varSubmitted) ? $varSubmitted[0] : $varSubmitted);
					$arrOptions = $this->prepareDcaOptions($arrField);
					foreach ($arrOptions as $o => $mxVal)
					{
						if ($mxVal['value'] == $varSubmitted)
						{
							//$strVal = $arrOptions[$o]['label'];
							$strVal = $mxVal['label'];
						}
					}
				break;
				case 'efgLookupSelect':
				case 'conditionalselect':
				case 'select':
					$strSep = '';
					$strVal = '';
					$arrOptions = $this->prepareDcaOptions($arrField);

					// select multiple
					if (is_array($varSubmitted))
					{
						foreach ($arrOptions as $o => $mxVal)
						{
							if (in_array($mxVal['value'], $varSubmitted))
							{
								//$strVal .= $strSep . $arrOptions[$o]['label'];
								$strVal .= $strSep . $mxVal['label'];
								$strSep = ', ';
							}
						}
					}

					// select single
					if (is_string($varSubmitted))
					{
						foreach ($arrOptions as $o => $mxVal)
						{
							if ($mxVal['value'] == $varSubmitted)
							{
								//$strVal = $arrOptions[$o]['label'];
								$strVal = $mxVal['label'];
							}
						}
					}
				break;
				case 'efgImageSelect':
					$strVal = '';
					if (strlen($varSubmitted))
					{
						$strVal = $varSubmitted;
					}
				break;
				case 'upload':
					$strVal = '';
					if (strlen($varFile['name']))
					{
						$strVal = $varFile['name'];
					}
				break;
				case 'password':
				case 'hidden':
				case 'text':
				case 'textarea':
				default:
					$strVal = $varSubmitted;
				break;
			}

			return $this->String->decodeEntities($strVal);

		} // if in_array arrFFstorable
		else
		{
			return $this->String->decodeEntities($varSubmitted);
		}

	}


	/*
	 * Prepare database value for Mail / Text
	 * @param mixed database value
	 * @param array form field properties
	 * @param mixed file
	 * @return mixed
	 */
	public function prepareDbValForMail($varValue='', $arrField=false, $varFile=false)
	{
		if (!is_array($arrField))
		{
			return false;
		}

		$strType = $arrField['type'];
		$strVal = '';

		if ( in_array($strType, $this->arrFFstorable) )
		{

			switch ($strType)
			{
				case 'efgLookupCheckbox':
				case 'checkbox':
					$blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

					$strVal = '';

					$arrSel = array();
					if (is_string($varValue))
					{
						$arrSel[] = $varValue;
					}
					if (is_array($varValue))
					{
						$arrSel = $varValue;
					}

					if (count($arrSel))
					{
						// get options labels instead of values for mail / text
						if ($blnEfgStoreValues && is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options']))
						{
							foreach ( $arrSel as $kSel => $vSel)
							{
								foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts)
								{
									if (is_array($varOpts))
									{
										if (isset($varOpts[$vSel]))
										{
											$arrSel[$kSel] = $varOpts[$vSel];
											break;
										}
									}
									else
									{
										if ($strOptsKey == $vSel)
										{
											$arrSel[$kSel] = $varOpts;
											break;
										}
									}
								}
							}
							$strVal = implode(', ', $arrSel);
						}
						else
						{
							$strVal = implode(', ', $arrSel);
						}
					}
				break;
				case 'efgLookupRadio':
				case 'radio':
					$blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

					$strVal = (is_array($varValue) ? $varValue[0] : $varValue);

					if ($blnEfgStoreValues && is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options']))
					{
						foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts)
						{
							if (is_array($varOpts))
							{
								if (isset($varOpts[$strVal]))
								{
									$strVal = $varOpts[$vSel];
									break;
								}
							}
							else
							{
								if ($strOptsKey == $strVal)
								{
									$strVal = $varOpts;
									break;
								}
							}
						}
					}
				break;
				case 'efgLookupSelect':
				case 'conditionalselect':
				case 'select':
					$blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

					$strVal = '';

					$arrSel = array();
					if (is_string($varValue))
					{
						$arrSel[] = $varValue;
					}
					if (is_array($varValue))
					{
						$arrSel = $varValue;
					}

					if (count($arrSel))
					{
						// get options labels instead of values for mail / text
						if ($blnEfgStoreValues && is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options']))
						{
							foreach ( $arrSel as $kSel => $vSel)
							{
								foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts)
								{
									if (is_array($varOpts))
									{
										if (isset($varOpts[$vSel]))
										{
											$arrSel[$kSel] = $varOpts[$vSel];
											break;
										}
									}
									else
									{
										if ($strOptsKey == $vSel)
										{
											$arrSel[$kSel] = $varOpts;
											break;
										}
									}
								}
							}
							$strVal = implode(', ', $arrSel);
						}
						else
						{
							$strVal = implode(', ', $arrSel);
						}
					}
				break;
				case 'efgImageSelect':
					$strVal = '';
					if (strlen($varValue))
					{
						$strVal = $varValue;
					}
				break;
				case 'upload':
					$strVal = '';
					if (strlen($varFile['name']))
					{
						$strVal = $varFile['name'];
					}
				break;
				case 'password':
				case 'hidden':
				case 'text':
				case 'textarea':
				default:
					$strVal = $varValue;
				break;
			}

			return $this->String->decodeEntities($strVal);

		} // if in_array arrFFstorable
		else
		{
			return $this->String->decodeEntities($varValue);
		}

	}


	/*
	 * Prepare database value from tl_formdata / tl_formdata_details for widget
	 * @param mixed stored value
	 * @param array form field properties
	 * @return mixed
	 */
	public function prepareDbValForWidget($varValue='', $arrField=false, $varFile=false)
	{

		if (!is_array($arrField))
		{
			return false;
		}

		$strType = $arrField['type'];
		$varVal = $varValue;

		if ( in_array($strType, $this->arrFFstorable) )
		{

			switch ($strType)
			{
				case 'efgLookupCheckbox':
				case 'checkbox':
					if ($arrField['options'])
					{
						$arrOptions = $arrField['options'];
					}
					else
					{
						$arrOptions = $this->prepareDcaOptions($arrField);
					}

					if (is_string($varVal))
					{
						$varVal = explode('|', $varVal);
					}

					if (is_array($arrOptions))
					{
						$arrTempOptions = array();
						foreach ($arrOptions as $sK => $mxVal)
						{
							$arrTempOptions[$mxVal['value']] = $mxVal['label'];
						}
					}

					if (is_array($varVal))
					{
						foreach ($varVal as $k => $v) {
							$sNewVal = array_search($v, $arrTempOptions);
							if ($sNewVal)
							{
								$varVal[$k] = $sNewVal;
							}
						}
					}
				break;
				case 'efgLookupRadio':
				case 'radio':
					if ($arrField['options'])
					{
						$arrOptions = $arrField['options'];
					}
					else
					{
						$arrOptions = $this->prepareDcaOptions($arrField);
					}

					if (is_string($varVal))
					{
						$varVal = explode('|', $varVal);
					}

					if (is_array($arrOptions))
					{
						$arrTempOptions = array();
						foreach ($arrOptions as $sK => $mxVal)
						{
							$arrTempOptions[$mxVal['value']] = $mxVal['label'];
						}
					}

					if (is_array($varVal))
					{
						foreach ($varVal as $k => $v) {
							$sNewVal = array_search($v, $arrTempOptions);
							if ($sNewVal)
							{
								$varVal[$k] = $sNewVal;
							}
						}
					}
				break;
				case 'efgLookupSelect':
				case 'conditionalselect':
				case 'select':
					if ($arrField['options'])
					{
						$arrOptions = $arrField['options'];
					}
					else
					{
						$arrOptions = $this->prepareDcaOptions($arrField);
					}

					if (is_string($varVal))
					{
						$varVal = explode('|', $varVal);
					}

					if (is_array($arrOptions))
					{
						$arrTempOptions = array();
						foreach ($arrOptions as $sK => $mxVal)
						{
							$arrTempOptions[$mxVal['value']] = $mxVal['label'];
						}
					}

					if (is_array($varVal))
					{
						foreach ($varVal as $k => $v) {
							$sNewVal = array_search($v, $arrTempOptions);
							if ($sNewVal)
							{
								$varVal[$k] = $sNewVal;
							}
						}
					}
				break;
				case 'upload':
					$varVal = '';
					if (strlen($varValue))
					{
						if ($arrField['storeFile'] == "1")
						{
							$strVal = $varValue;
						}
						else
						{
							$strVal = $varValue;
						}
						$varVal = $strVal;
					}
				break;
				case 'password':
				case 'hidden':
				case 'text':
				case 'textarea':
				default:
					if ($arrField['rgxp'] && in_array($arrField['rgxp'], array('date', 'datim', 'time')))
					{
						if ($varVal)
						{
							if ($arrField['rgxp'] == 'date')
							{
								$varVal = date($GLOBALS['TL_CONFIG']['dateFormat'], $varVal);
							}
							elseif ($arrField['rgxp'] == 'datim')
							{
								$varVal = date($GLOBALS['TL_CONFIG']['datimFormat'], $varVal);
							}
							elseif ($arrField['rgxp'] == 'time')
							{
								$varVal = date($GLOBALS['TL_CONFIG']['timeFormat'], $varVal);
							}
						}
					}
					else
					{
						$varVal = $varValue;
					}
				break;
			}

			return $varVal;

		} // if in_array arrFFstorable
		else
		{
			return $varVal;
		}

	}

	/*
	 * Prepare dca options array
	 * @param array form field
	 * @return array DCA options
	 */
	public function prepareDcaOptions($arrField=false)
	{

		if (!$arrField)
		{
			return false;
		}

		$strType = $arrField['type'];
		if ($arrField['inputType'] == 'efgLookupSelect')
		{
			$strType = 'efgLookupSelect';
		}
		if ($arrField['inputType'] == 'efgLookupCheckbox')
		{
			$strType = 'efgLookupCheckbox';
		}
		if ($arrField['inputType'] == 'efgLookupRadio')
		{
			$strType = 'efgLookupRadio';
		}

		switch ($strType)
		{

			case 'efgLookupCheckbox':
			case 'efgLookupRadio':
			case 'efgLookupSelect':
				// get efgLookupOptions: array('lookup_field' => TABLENAME.FIELDNAME, 'lookup_val_field' => TABLENAME.FIELDNAME, 'lookup_where' => CONDITION)
				$arrLookupOptions = deserialize($arrField['efgLookupOptions']);
				$strLookupField = $arrLookupOptions['lookup_field'];
				$strLookupValField = (strlen($arrLookupOptions['lookup_val_field'])) ? $arrLookupOptions['lookup_val_field'] : null;

				$strLookupWhere = $this->String->decodeEntities($arrLookupOptions['lookup_where']);
				if (strlen($strLookupWhere))
				{
					$strLookupWhere = $this->replaceInsertTags($strLookupWhere);
				}

				$arrLookupField = explode('.', $strLookupField);
				$sqlLookupTable = $arrLookupField[0];
				$sqlLookupField = $arrLookupField[1];
				$sqlLookupValField = (strlen($strLookupValField)) ? substr($strLookupValField, strpos($strLookupValField, '.')+1) : null;

				$sqlLookupIdField = 'id';
				$sqlLookupWhere = (strlen($strLookupWhere) ? " WHERE " . $strLookupWhere : "");
				$sqlLookupOrder = $arrLookupField[0] . '.' . $arrLookupField[1];

				$arrOptions = array();

				// handle lookup formdata
				if (substr($sqlLookupTable, 0, 3) == 'fd_')
				{

					// load formdata specific dca
					if (!isset($GLOBALS['TL_DCA']['tl_formdata'])) {
						$this->loadDataContainer($sqlLookupTable);
					}

					$strFormKey = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterValue'];
					$strFormKey = $this->arrFormsDcaKey[substr($sqlLookupTable, 3)];

					$sqlLookupTable = 'tl_formdata f, tl_formdata_details fd';
					$sqlLookupIdField = 'f.id';
					$sqlLookupWhere = " WHERE (f.id=fd.pid AND f.form='".$strFormKey."' AND ff_name='".$arrLookupField[1]."')";

					if (strlen($strLookupWhere))
					{
						// special treatment for fields in tl_formdata_details
						$arrPattern = array();
						$arrReplace = array();
						foreach($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'] as $strDetailField)
						{
							$arrPattern[] = '/\b' . $strDetailField . '\b/i';
							$arrReplace[] = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$strDetailField.'\'))';
						}
						$sqlLookupWhere .= (strlen($sqlLookupWhere) ? " AND " : " WHERE ") . "(" .preg_replace($arrPattern, $arrReplace, $strLookupWhere) .")";
					}
					$sqlLookupField = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$arrLookupField[1].'\') ) AS `'. $arrLookupField[1] .'`';
					$sqlLookupOrder = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$arrLookupField[1].'\'))';
				} // end lookup formdata

				// handle lookup calendar events
				if ($sqlLookupTable == 'tl_calendar_events')
				{

					$strReferer = $this->getReferer();

					// if form is placed on an events detail page, automatically add restriction to event(s)
					if (strlen($this->Input->get('events')))
					{
						if (is_numeric($this->Input->get('events')))
						{
							$strLookupWhere = (strlen($strLookupWhere)) ? " AND " : "" . " tl_calendar_events.id=".intval($this->Input->get('events'))." ";
						}
						elseif (is_string($this->Input->get('events')))
						{
							$strLookupWhere = (strlen($strLookupWhere)) ? " AND " : "" . " tl_calendar_events.alias='".$this->Input->get('events')."' ";
						}
					}
					// if linked from event reader page
					if (strpos($strReferer, 'event-reader/events/') || strpos($strReferer, '&events=') )
					{
						if (strpos($strReferer, 'events/'))
						{
							$strEvents = substr($strReferer, strrpos($strReferer, '/')+1);
						}
						elseif (strpos($strReferer, '&events='))
						{
							$strEvents = substr($strReferer, strpos($strReferer, '&events=')+strlen('&events='));
						}

						if (is_numeric($strEvents))
						{
							$strLookupWhere = (strlen($strLookupWhere)) ? " AND " : "" . " tl_calendar_events.id=".intval($strEvents)." ";
						}
						elseif (is_string($strEvents))
						{
							$strEvents = str_replace('.html', '', $strEvents);
							$strLookupWhere = (strlen($strLookupWhere)) ? " AND " : "" . " tl_calendar_events.alias='".$strEvents."' ";
						}

					}

					$sqlLookup = "SELECT tl_calendar_events.* FROM tl_calendar_events, tl_calendar WHERE (tl_calendar.id=tl_calendar_events.pid) " . (strlen($strLookupWhere) ? " AND (" . $strLookupWhere . ")" : "") . (strlen($sqlLookupOrder) ? " ORDER BY " . $sqlLookupOrder : "");

					$objEvents = $this->Database->prepare($sqlLookup)->execute();

					$arrEvents = array();

					if ($objEvents->numRows)
					{
						while ($arrEvent = $objEvents->fetchAssoc())
						{
							$intDate = $arrEvent['startDate'];

							$intStart = time();
							$intEnd = time() + 60*60*24*178 ; // max. 1/2 Jahr

							$span = Calendar::calculateSpan($arrEvent['startTime'], $arrEvent['endTime']);

							$strTime = '';
							$strTime .= date($GLOBALS['TL_CONFIG']['dateFormat'], $arrEvent['startDate']);

							if ($arrEvent['addTime'])
							{
								if ($span > 0)
								{
									$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']) . ' - ' . date($GLOBALS['TL_CONFIG']['dateFormat'], $arrEvent['endTime']) . ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['endTime']);
								}
								elseif ($arrEvent['startTime'] == $arrEvent['endTime'])
								{
									$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']);
								}
								else
								{
									$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']) . ' - ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['endTime']);
								}
							}
							else
							{
								if ($span > 1)
								{
									$strTime .= ' - ' . date($GLOBALS['TL_CONFIG']['dateFormat'], $arrEvent['endTime']);
								}
							}

							if ($sqlLookupValField)
							{
								$arrEvents[$arrEvent[$sqlLookupValField].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
							}
							else
							{
								$arrEvents[$arrEvent['id'].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
							}

							// Recurring events
							if ($arrEvent['recurring'])
							{
								$count = 0;
								$arrRepeat = deserialize($arrEvent['repeatEach']);
								$blnSummer = date('I', $arrEvent['startTime']);

								$intEnd = time() + 60*60*24*178; // max. 1/2 Jahr

								while ($arrEvent['endTime'] < $intEnd)
								{

									if ($arrEvent['recurrences'] > 0 && $count++ >= $arrEvent['recurrences'])
									{
										break;
									}

									$arg = $arrRepeat['value'];
									$unit = $arrRepeat['unit'];

									if ($arg == 1)
									{
										$unit = substr($unit, 0, -1);
									}

									$strtotime = '+ ' . $arg . ' ' . $unit;

									$arrEvent['startTime'] = strtotime($strtotime, $arrEvent['startTime']);
									$arrEvent['endTime'] = strtotime($strtotime, $arrEvent['endTime']);

									if ($arrEvent['startTime'] >= $intStart || $arrEvent['endTime'] <= $intEnd)
									{

										$strTime = '';
										$strTime .= date($GLOBALS['TL_CONFIG']['dateFormat'], $arrEvent['startTime']);

										if ($arrEvent['addTime'])
										{

											if ($span > 0)
											{
												$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']) . ' - ' . date($GLOBALS['TL_CONFIG']['dateFormat'], $arrEvent['endTime']) . ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['endTime']);
											}
											elseif ($arrEvent['startTime'] == $arrEvent['endTime'])
											{
												$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']);
											}
											else
											{
												$strTime .= ' ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['startTime']) . ' - ' . date($GLOBALS['TL_CONFIG']['timeFormat'], $arrEvent['endTime']);
											}
										}

										if ($sqlLookupValField)
										{
											$arrEvents[$arrEvent[$sqlLookupValField].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
										}
										else
										{
											$arrEvents[$arrEvent['id'].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
										}

									}
								}

							}

						}
					}

					// set options
					$arrOptions = $arrEvents;
					// sort events on key
					ksort($arrOptions);

					if ( count($arrOptions) == 1 )
					{
						$blnDoNotAddEmptyOption = true;
					}

				} // end handle calendar events

				else // normal lookup table or formdata lookup table
				{
					$sqlLookup = "SELECT " . $sqlLookupIdField . (strlen($sqlLookupField) ? ', ' : '') . $sqlLookupField . ( strlen($sqlLookupValField) ? ', ' : '') . $sqlLookupValField . " FROM " . $sqlLookupTable . $sqlLookupWhere . (strlen($sqlLookupOrder)>1 ? " ORDER BY " . $sqlLookupOrder : "");

					if (strlen($sqlLookupTable))
					{
						$objOptions = $this->Database->prepare($sqlLookup)->execute();
					}
					if ($objOptions->numRows)
					{
						$arrOptions = array();
						while ($arrOpt = $objOptions->fetchAssoc())
						{
							//$arrOptions['~'.$arrOpt['id']. (($sqlLookupValField) ? '::'.$arrOpt[$sqlLookupValField] : '') . '~'] = $arrOpt[$arrLookupField[1]];
							if ($sqlLookupValField)
							{
								$arrOptions[$arrOpt[$sqlLookupValField]] = $arrOpt[$arrLookupField[1]];
							}
							else
							{
								//$arrOptions['~'.$arrOpt['id'].'~'] = $arrOpt[$arrLookupField[1]];
								$arrOptions[$arrOpt['id']] = $arrOpt[$arrLookupField[1]];
							}
						}
					}

				} // end normal lookup table

				$arrTempOptions = array();
				// include blank option to input type select
				if ( $strType == 'efgLookupSelect' )
				{
					if ( !$blnDoNotAddEmptyOption )
					{
						$arrTempOptions[] = array('value'=>'', 'label'=>'-');
					}
				}

				foreach ($arrOptions as $sK => $sV)
				{
					$strKey = (string) $sK;
					$arrTempOptions[] = array('value'=>$strKey, 'label'=>$sV);
				}
				$arrOptions = $arrTempOptions;

			break; // strType efgLookupCheckbox, efgLookupRadio or efgLookupSelect
			default:
				$arrOptions = deserialize($arrField['options']);
			break;
		} // end switch $arrField['type']

		return $arrOptions;

	}

	public function parseInsertTagParams($strTag='')
	{
		if ($strTag == '')
		{
			return null;
		}
		if (strpos($strTag, '?') == false)
		{
			return null;
		}
		$strTag = str_replace(array('{{', '}}'), array('', ''), $strTag);

		$arrTag = explode('?', $strTag);
		$strKey = $arrTag[0];
		if (isset($arrTag[1]) && strlen($arrTag[1]))
		{
			$arrTag[1] = str_replace('[&]', '__AMP__', $arrTag[1]);
			$strParams = $this->String->decodeEntities($arrTag[1]);
			$arrParams = preg_split('/&/sim', $strParams);

			$arrReturn = array();
			foreach ($arrParams as $strParam)
			{
				list($key, $value) = explode('=', $strParam);
				$arrReturn[$key] = str_replace('__AMP__', '&', $value);
			}
		}

		return $arrReturn;

	}

}

?>
