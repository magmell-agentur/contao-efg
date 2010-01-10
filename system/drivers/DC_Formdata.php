<?php

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
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    System
 * @license    LGPL
 * @filesource
 */


/**
 * Class DC_Formdata
 * modified version of DC_Table by Leo Feyer
 *
 * Provide methods to modify data stored in tables tl_formdata and tl_formdata_details.
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    Controller
 * @version    1.12.0
 */
class DC_Formdata extends DataContainer implements listable, editable
{

	/**
	 * Name of the parent table
	 * @param  string
	 */
	protected $ptable;

	/**
	 * Names of one or more child tables
	 * @param  array
	 */
	protected $ctable;

	/**
	 * ID of the current record
	 * @param  int
	 */
	protected $id;

	/**
	 * IDs of all root records
	 * @param  mixed
	 */
	protected $root;

	/**
	 * ID of the button container
	 * @param string
	 */
	protected $bid;

	/**
	 * Limit (database query)
	 * @param  string
	 */
	protected $limit;

	/**
	 * First sorting field
	 * @param  string
	 */
	protected $firstOrderBy;

	/**
	 * Order by (database query)
	 * @param  array
	 */
	protected $orderBy = array();

	/**
	 * Fields of a new or duplicated record
	 * @param  array
	 */
	protected $set = array();

	/**
	 * IDs of all records that are currently displayed
	 * @param  array
	 */
	protected $current = array();

	/**
	 * Show the current table as tree
	 * @param  boolean
	 */
	protected $treeView = false;

	/**
	 * True if a new version has to be created
	 * @param boolean
	 */
	protected $blnCreateNewVersion = false;

	/**
	 * True if one of the form fields is uploadable
	 * @param boolean
	 */
	protected $blnUploadable = false;

	/**
	 * Related form, like fd_frm_contact
	 * @param string
	 */
	protected $strFormKey;

	/**
	 * Related form filter key, name of field in table tl_formdata holding form-identifier
	 * @param string
	 */
	protected $strFormFilterKey;

	/**
	 * Related form filter value, title of related form like 'Contact Form"
	 * @param string
	 */
	protected $strFormFilterValue;

	/**
	 * sql condition for form to filter
	 * @param string
	 */
	protected $sqlFormFilter;

	/**
	 * Items in tl_form, all forms marked to store data in tl_formdata
	 * @param array
	 */
	protected $arrStoreForms;

	protected $arrFormsDcaKey = null;

	/**
	 * Base fields in table tl_formdata
	 * @param mixed
	 */
	protected $arrBaseFields = null;

	/**
	 * Base fields for owner restriction (member,user,..)
	 * @param mixed
	 */
	protected $arrOwnerFields = null;

	/**
	 * Detail fields names in table tl_formdata_details
	 * @param mixed
	 */
	protected $arrDetailFields = null;

	/**
	 * Detail fields in table tl_formdata_details
	 */
	protected $arrDetailFieldsObj = null;

	/**
	 * Sql statements for detail fields
	 * @param mixed
	 */
	protected $arrSqlDetails;

	protected $arrMembers = null;

	protected $arrUsers = null;

	// convert UTF8 to cp1251 on CSV-/XLS-Export
	protected $blnExportUTF8Decode = true;

	/**
	 * Initialize the object
	 * @param string
	 */
	public function __construct($strTable)
	{
		parent::__construct();
		$this->intId = $this->Input->get('id');

		$this->import('String');
		$this->import('FormData');

		// in Backend: Check BE User, Admin...
		if (TL_MODE == 'BE' || BE_USER_LOGGED_IN)
		{
			$this->import('BackendUser', 'User');
		}

		// in Frontend:
		if (TL_MODE == 'FE')
		{
			$this->import('FrontendUser', 'Member');
		}

		if ($this->Input->get('key') == 'export')
		{
			$this->strMode = 'export';
		}
		if ($this->Input->get('key') == 'exportxls')
		{
			$this->strMode = 'exportxls';
		}

		$this->blnExportUTF8Decode = true;
		if (isset($GLOBALS['EFG']['exportUTF8Decode']) && $GLOBALS['EFG']['exportUTF8Decode'] == false)
		{
			$this->blnExportUTF8Decode = false;
		}

		// get all forms marked to store data
		$objForms = $this->Database->prepare("SELECT id,title,formID,useFormValues,useFieldNames FROM tl_form WHERE storeFormdata=?")
										->execute("1");
		if ( !$this->arrStoreForms )
		{
			while ($objForms->next())
			{
				if (strlen($objForms->formID))
				{
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

		// all field names of table tl_formdata
		$this->arrBaseFields = array('id','sorting','tstamp','form','ip','date','fd_member','fd_user','published','alias','be_notes');
		$this->arrOwnerFields = array('fd_member','fd_user');

		$this->getMembers();
		$this->getUsers();

		// Check whether the table is defined
		if (!strlen($strTable) || !count($GLOBALS['TL_DCA'][$strTable]))
		{
			$this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}

		// Set IDs and redirect
		if ($this->Input->post('FORM_SUBMIT') == 'tl_select')
		{
			$ids = deserialize($this->Input->post('IDS'));

			if (!is_array($ids) || count($ids) < 1)
			{
				$this->reload();
			}

			$session = $this->Session->getData();
			$session['CURRENT']['IDS'] = deserialize($this->Input->post('IDS'));
			$this->Session->setData($session);

			$next = array_key_exists('edit', $_POST) ? 'editAll' : (array_key_exists('delete', $_POST) ? 'deleteAll' : 'select');
			$this->redirect(str_replace('act=select', 'act='.$next, $this->Environment->request));
		}

		$this->strTable = $strTable;
		$this->ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
		$this->ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];
		$this->treeView = in_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'], array(5, 6));
		$this->root = null;

		// Key of a form or '' for no specific form
		$this->strFormKey = '';
		$this->strFormFilterKey = '';
		$this->strFormFilterValue = '';

		if ($this->Input->get('do'))
		{
			if ($this->Input->get('do') != 'feedback' )
			{
				if (array_key_exists($this->Input->get('do'), $GLOBALS['BE_MOD']['formdata']) )
				{
					$this->strFormKey = $this->Input->get('do');
					$this->strFormFilterKey = 'form';
					$this->strFormFilterValue = $this->arrStoreForms[str_replace('fd_', '', $this->strFormKey)]['title'];
					$this->sqlFormFilter = ' AND ' . $this->strFormFilterKey . '=\'' . $this->strFormFilterValue . '\' ';

					// add sql where condition 'form'=TILTE_OF_FORM
					if ($this->strTable == 'tl_formdata')
					{
						$this->procedure[] = $this->strFormFilterKey . '=?';
						$this->values[] = $this->strFormFilterValue;
					}
				}
			}
		}

		// Call onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this);
				}
			}
		}

		// check names of detail fields
		// .. after call to onload_callback we have the form specific dca in $GLOBALS['TL_DCA'][$this->strTable]
		if (strlen($this->strFormKey))
		{
			$arrFFNames = array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']);
		}
		else // get all FormField names of forms storing formdata
		{
			$objFFNames = $this->Database->prepare("SELECT DISTINCT ff.name FROM tl_form_field ff, tl_form f WHERE (ff.pid=f.id) AND ff.name != '' AND f.storeFormdata=?")
											->execute("1");
			if ( $objFFNames->numRows)
			{
				$arrFFNames = $objFFNames->fetchEach('name');
			}
		}
		if ( count($arrFFNames) )
		{
			$this->arrDetailFields = array_diff($arrFFNames, $this->arrBaseFields);
		}

		// store array of sql-stmts for detail fields
		if (count($this->arrDetailFields))
		{
			$this->arrSqlDetails = array();
			foreach ($this->arrDetailFields as $strFName)
			{
				$this->arrSqlDetails[] = '(SELECT value FROM tl_formdata_details WHERE ff_name=\'' .$strFName. '\' AND pid=f.id) AS `' . $strFName .'`';
			}
		}

		// Get the IDs of all root records
		if ($this->treeView)
		{
			$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;

			 // Unless there are any root records specified, use all records with parent ID 0
			if (!$GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false)
			{
				$objIds = $this->Database->prepare("SELECT id FROM " . $table ." WHERE pid=?" . ($this->Database->fieldExists('sorting', $strTable) ? ' ORDER BY sorting' : ''))
										 ->execute(0);

				if ($objIds->numRows > 0)
				{
					$this->root = $objIds->fetchEach('id');
				}
			}

			// Get root records from global configuration file
			elseif (is_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']))
			{
				$childs = array();
				$root = (array) $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'];
				$this->root = array_intersect($this->getChildRecords(0, $table), $root);

				foreach ($this->root as $id)
				{
					$childs = array_merge($childs, $this->getChildRecords($id, $table));
				}

				// Eliminate all child IDs (child records are included by default)
				$this->root = array_values(array_diff($this->root, $childs));
			}
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
			case 'id':
				return $this->intId;
				break;

			case 'parentTable':
				return $this->ptable;
				break;

			case 'childTable':
				return $this->ctable;
				break;

			case 'rootIds':
				return $this->root;
				break;

			case 'strFormFilterValue':
				return $this->strFormFilterValue;
				break;

			default:
				return parent::__get($strKey);
				break;
		}
	}


	/**
	 * List all records of a particular table
	 * @return string
	 */
	public function showAll()
	{
		$return = '';
		$this->limit = '';
		$this->bid = 'tl_buttons';

		// Clean up old tl_undo and tl_log entries
		if ($this->strTable == 'tl_undo' && strlen($GLOBALS['TL_CONFIG']['undoPeriod']))
		{
			$this->Database->prepare("DELETE FROM tl_undo WHERE tstamp<?")->execute((int) time() - $GLOBALS['TL_CONFIG']['undoPeriod']);
		}

		elseif ($this->strTable == 'tl_log' && strlen($GLOBALS['TL_CONFIG']['logPeriod']))
		{
			$this->Database->prepare("DELETE FROM tl_log WHERE tstamp<?")->execute((int) time() - $GLOBALS['TL_CONFIG']['logPeriod']);
		}

		$this->reviseTable();

		// Add to clipboard
		if ($this->Input->get('act') == 'paste')
		{
			$arrClipboard = $this->Session->get('CLIPBOARD');

			$arrClipboard[$this->strTable] = array
			(
				'id' => $this->Input->get('id'),
				'childs' => $this->Input->get('childs'),
				'mode' => $this->Input->get('mode')
			);

			$this->Session->set('CLIPBOARD', $arrClipboard);
		}

		if ($this->treeView)
		{
			$return .= $this->treeView();
		}

		else
		{
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
			{
				$this->procedure[] = 'pid=?';
				$this->values[] = CURRENT_ID;
			}

			$return .= $this->panel();
			$return .= ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->parentView() : $this->listView();

			// Add another panel at the end of the page
			if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'], 'limit') !== false && ($strLimit = $this->limitMenu(true)) != false)
			{
				$return .= '

<form action="'.ampersand($this->Environment->request, true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters_limit" />

<div class="tl_panel_bottom">

<div class="tl_submit_panel tl_subpanel">
<input type="image" name="btfilter" id="btfilter" src="system/themes/' . $this->getTheme() . '/images/reload.gif" class="tl_img_submit" alt="apply changes" value="apply changes" />
</div>' . $strLimit . '

<div class="clear"></div>

</div>

</div>
</form>
';
			}
		}

		// Store the current IDs
		$session = $this->Session->getData();
		$session['CURRENT']['IDS'] = $this->current;
		$this->Session->setData($session);

		return $return;
	}


	/**
	 * Return all non-excluded fields of a record as HTML table
	 * @return string
	 */
	public function show()
	{
		if (!strlen($this->intId))
		{
			return '';
		}

		$strFormFilter = ($this->strTable == 'tl_formdata' && strlen($this->strFormKey) ? $this->sqlFormFilter : '');
		$table_alias = ($this->strTable == 'tl_formdata' ? ' f' : '');

		$sqlQuery = "SELECT * " .(count($this->arrSqlDetails) > 0 ? ', '.implode(',' , $this->arrSqlDetails) : '') ." FROM " . $this->strTable . $table_alias;
		$sqlWhere = " WHERE id=?";
		if ( $sqlWhere != '')
		{
			$sqlQuery .= $sqlWhere;
		}

		$objRow = $this->Database->prepare($sqlQuery)
								 ->limit(1)
								 ->execute($this->intId);

		if ($objRow->numRows < 1)
		{
			return '';
		}

		$count = 1;
		$return = '';
		$row = $objRow->row();

		// Get all fields
		$fields = array_keys($row);

		$allowedFields = array('id', 'pid', 'sorting');

		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields']))
		{
			$allowedFields = array_merge($allowedFields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));
		}

		// Show all allowed fields
		foreach ($fields as $i)
		{
			if (!in_array($i, $allowedFields) || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'password' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['doNotShow'])
			{
				continue;
			}

			// Special treatment for table tl_undo
			if ($this->strTable == 'tl_undo' && $i == 'data')
			{
				continue;
			}

			$value = deserialize($row[$i]);

			$class = (($count++ % 2) == 0) ? ' class="tl_bg"' : '';

			// ignore display of empty detail-fields if this is overall "feedback"
			if (empty($this->strFormKey) && in_array($i, $this->arrDetailFields) && empty($value))
			{
				continue;
			}

			// Get field value
			if (is_array($value))
			{
				foreach ($value as $kk=>$vv)
				{
					if (is_array($vv))
					{
						$vals = array_values($vv);
						$value[$kk] = $vals[0].' ('.$vals[1].')';
					}
				}

				$row[$i] = implode(', ', $value);
			}
			elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['rgxp'] == 'date' && in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['flag'], array(5, 6, 7, 8, 9, 10))) {
				$row[$i] = ($value ? date($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '');
			}
			elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['rgxp'] == 'datim' && in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['flag'], array(5, 6, 7, 8, 9, 10))) {
				$row[$i] = ($value ? date($GLOBALS['TL_CONFIG']['datimFormat'], $value) : '');
			}
			elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['flag'], array(5, 6, 7, 8, 9, 10)))
			{
				$row[$i] = ($value ? date($GLOBALS['TL_CONFIG']['datimFormat'], $value) : '');
			}
			elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['multiple'])
			{
				$row[$i] = strlen($value) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'][0] : '-';
			}
			elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'checkbox'
					|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'efgLookupCheckbox'
					|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'select'
					|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType'] == 'efgLookupSelect')
					&& $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['multiple'])
			{
				$row[$i] = strlen($value) ? str_replace('|', ', ', $value) : $value;
			}
			elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference']))
			{
				$row[$i] = strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]]) ? ((is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]]) : $row[$i];
			}

			if (in_array($i, $this->arrBaseFields) || in_array($i, $this->arrOwnerFields))
			{
				if ($i == 'fd_member')
				{
					$row[$i] = $this->arrMembers[intval($value)];
				}
				if ($i == 'fd_user')
				{
					$row[$i] = $this->arrUsers[intval($value)];
				}
			}


			// Replace foreign keys with their values
			// .. but not if foreignKey table is formdata table
			if (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['foreignKey']))
			{
				$chunks = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['foreignKey']);

				if (substr($chunks[0], 0, 3) == 'fd_')
				{
					$row[$i] = $value;
				}
				else
				{

					$objKey = $this->Database->prepare("SELECT " . $chunks[1] . " FROM " . $chunks[0] . " WHERE id=?")
											 ->limit(1)
											 ->execute($row[$i]);

					if ($objKey->numRows)
					{
						$row[$i] = $objKey->$chunks[1];
					}
				}
			}


			// check multiline value
			if (!is_bool(strpos($row[$i], "\n")))
			{
				$strVal = $row[$i];
				$strVal = preg_replace('/(<\/|<)(h\d|p|div|ul|ol|li)([^>]*)(>)(\n)/si', "\\1\\2\\3\\4", $strVal);
				$strVal = nl2br($strVal);
				$strVal = preg_replace('/(<\/)(h\d|p|div|ul|ol|li)([^>]*)(>)/si', "\\1\\2\\3\\4\n", $strVal);
				$row[$i] = $strVal;
				unset($strVal);
			}

			// Label
			if (count($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label']))
			{
				$label = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'];
			}

			else
			{
				$label = is_array($GLOBALS['TL_LANG']['MSC'][$i]) ? $GLOBALS['TL_LANG']['MSC'][$i][0] : $GLOBALS['TL_LANG']['MSC'][$i];
			}

			if (!strlen($label))
			{
				$label = $i;
			}

			$return .= '
  <tr>
    <td'.$class.'><span class="tl_label">'.$label.': </span></td>
    <td'.$class.'>'.$row[$i].'</td>
  </tr>';
		}

		// Special treatment for tl_undo
		if ($this->strTable == 'tl_undo')
		{
			$arrData = deserialize($objRow->data);

			foreach ($arrData as $strTable=>$arrTableData)
			{
				$this->loadLanguageFile($strTable);
				$this->loadDataContainer($strTable);

				foreach ($arrTableData as $arrRow)
				{
					$count = 0;
					$return .= '
  <tr>
    <td colspan="2" style="padding:0px;"><div style="margin-bottom:26px; line-height:24px; border-bottom:1px dotted #cccccc;"> </div></td>
  </tr>';

					foreach ($arrRow as $i=>$v)
					{
						if (is_array(deserialize($v)))
						{
							continue;
						}

						$class = (($count++ % 2) == 0) ? ' class="tl_bg"' : '';

						// Get the field label
						if (count($GLOBALS['TL_DCA'][$strTable]['fields'][$i]['label']))
						{
							$label = is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$i]['label']) ? $GLOBALS['TL_DCA'][$strTable]['fields'][$i]['label'][0] : $GLOBALS['TL_DCA'][$strTable]['fields'][$i]['label'];
						}

						else
						{
							$label = is_array($GLOBALS['TL_LANG']['MSC'][$i]) ? $GLOBALS['TL_LANG']['MSC'][$i][0] : $GLOBALS['TL_LANG']['MSC'][$i];
						}

						if (!strlen($label))
						{
							$label = $i;
						}

						$return .= '
  <tr>
    <td'.$class.'><span class="tl_label">'.$label.': </span></td>
    <td'.$class.'>'.$v.'</td>
  </tr>';
					}
				}
			}
		}

		// Return table
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>

<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry">'.$return.'
</table>';
	}


	/**
	 * Insert a new row into a database table
	 * @param array
	 */
	public function create($set=array())
	{

		if (isset($this->strFormKey) && strlen($this->strFormKey))
		{
			$set['form'] = $this->arrStoreForms[str_replace('fd_', '', $this->strFormKey)]['title'];
			$set['date'] = time();
			$set['ip'] = $this->Environment->ip;

			if ($this->User && intval($this->User->id)>0)
			{
				$set['fd_user'] = intval($this->User->id);
			}

		}

		// Get all default values for the new entry
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if (isset($v['default']))
			{
				// tom, 2007-09-27, default values of base fields only
				if (!in_array($k, $this->arrBaseFields))
				{
					continue;
				}
				$this->set[$k] = is_array($v['default']) ? serialize($v['default']) : $v['default'];
			}
		}

		// Set passed values
		if (is_array($set) && count($set))
		{
			$this->set = array_merge($this->set, $set);
		}

		// Get the new position
		$this->getNewPosition('new', (strlen($this->Input->get('pid')) ? $this->Input->get('pid') : null), ($this->Input->get('mode') == '2' ? true : false));

		// Empty clipboard
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$this->Session->set('CLIPBOARD', $arrClipboard);

		// Insert the record if the table is not closed and switch to edit mode
		if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])
		{
			$this->set['tstamp'] = 0;
			$objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
											->set($this->set)
											->execute();

			if ($objInsertStmt->affectedRows)
			{
				$s2e = $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ? '&s2e=1' : '';
				$insertID = $objInsertStmt->insertId;

				foreach ($this->arrDetailFields as $strDetailField)
				{
					$strVal = '';
					$arrDetailSet = array('pid' => $insertID, 'tstamp' => time(), 'ff_id' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['ff_id'], 'ff_type' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['inputType'], 'ff_label' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['label'][0] , 'ff_name' => $strDetailField, 'ff_label' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['label'][0] );

   					// default value
   					if ( strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default']) )
   					{
   						$strVal = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default'];
   					}
   					// default value in case of field type checkbox, select, radio
   					if ( is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default']) && count($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default'])>0 )
   					{
   						$strVal = implode(',', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default']);
   					}

					$arrDetailSet['value'] = $strVal;

					$objInsertStmt = $this->Database->prepare("INSERT INTO tl_formdata_details %s")
											->set($arrDetailSet)
											->execute();
				}

				// Add a log entry
				$this->log('A new entry in table "'.$this->strTable.'" has been created (ID: '.$insertID.')', 'DC_Table create()', TL_GENERAL);
				$this->redirect($this->switchToEdit($insertID).$s2e);
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Assign a new position to an existing record
	 */
	public function cut()
	{
		$cr = array();

		// ID and PID are mandatory
		if (!$this->intId || !strlen($this->Input->get('pid')))
		{
			$this->redirect($this->getReferer());
		}

		// Get the new position
		$this->getNewPosition('cut', $this->Input->get('pid'), ($this->Input->get('mode') == '2' ? true : false));

		// Avoid circular references when there is no parent table
		if ($this->Database->fieldExists('pid', $this->strTable) && !strlen($this->ptable))
		{
			$cr = $this->getChildRecords($this->intId, $this->strTable);
			$cr[] = $this->intId;
		}

		// Update the record
		if (in_array($this->set['pid'], $cr))
		{
			$this->log('Attempt to relate record "'.$this->intId.'" of table "'.$this->strTable.'" to its child record "'.$this->Input->get('pid').'" (circular reference)', 'DC_Table cut()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$this->set['tstamp'] = time();

		$this->Database->prepare("UPDATE " . $this->strTable . " %s WHERE id=?")
					   ->set($this->set)
					   ->execute($this->intId);

		// Empty clipboard
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$this->Session->set('CLIPBOARD', $arrClipboard);

		$this->redirect($this->getReferer());
	}


	/**
	 * Duplicate a particular record of the current table
	 */
	public function copy()
	{
		if (!$this->intId)
		{
			$this->redirect($this->getReferer());
		}

		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($this->intId);

		// Copy values if the record contains data
		if ($objRow->numRows)
		{
			foreach ($objRow->fetchAssoc() as $k=>$v)
			{
				if (in_array($k, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'])))
				{
					// Reset all unique, excluded and fallback fields to their default value
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['exclude'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['unique'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['doNotCopy'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['fallback'])
					{
						$v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default'] ? ((is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default'])) ? serialize($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default']) : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default']) : '';
					}

					// Set fields (except password fields)
					$this->set[$k] = ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType'] == 'password' ? '' : $v);
				}
			}
		}

		// Get the new position
		$this->getNewPosition('copy', (strlen($this->Input->get('pid')) ? $this->Input->get('pid') : null), ($this->Input->get('mode') == '2' ? true : false));

		// Empty clipboard
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$this->Session->set('CLIPBOARD', $arrClipboard);

		// Insert the record if the table is not closed and switch to edit mode
		if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])
		{
			$this->set['tstamp'] = 0;

			$objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
											->set($this->set)
											->execute();

			if ($objInsertStmt->affectedRows)
			{
				$insertID = $objInsertStmt->insertId;

				// Duplicate records of the child table
				$this->copyChilds($this->strTable, $insertID, $this->intId, $insertID);

				// Add a log entry and switch to edit mode
				$this->log('A new entry in table "'.$this->strTable.'" has been created (ID: '.$insertID.')', 'DC_Table copy()', TL_GENERAL);
				$this->redirect($this->switchToEdit($insertID));
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Duplicate all child records of a duplicated record
	 * @param string
	 * @param int
	 * @param int
	 * @param int
	 */
	private function copyChilds($table, $insertID, $id, $parentId)
	{
		$time = time();
		$copy = array();
		$cctable = array();
		$ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'];

		if (!$GLOBALS['TL_DCA'][$table]['config']['ptable'] && strlen($this->Input->get('childs')) && $this->Database->fieldExists('pid', $table) && $this->Database->fieldExists('sorting', $table))
		{
			$ctable[] = $table;
		}

		if (!is_array($ctable))
		{
			return;
		}

		// Walk through each child table
		foreach ($ctable as $v)
		{
			$this->loadDataContainer($v);
			$cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'];

			if (!$GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] && strlen($v))
			{
				$objCTable = $this->Database->prepare("SELECT * FROM " . $v . " WHERE pid=?" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
											->execute($id);

				foreach ($objCTable->fetchAllAssoc() as $row)
				{
					foreach ($row as $kk=>$vv)
					{
						// Exclude the duplicated record itself
						if ($v == $table && $row['id'] == $parentId)
						{
							continue;
						}

						if ($kk != 'id')
						{
							// Reset all unique, excluded and fallback fields to their default value
							if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['exclude'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['eval']['unique'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['eval']['fallback'])
							{
								$vv = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['default'] ? ((is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['default'])) ? serialize($GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['default']) : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$kk]['default']) : '';
							}

							$copy[$v][$row['id']][$kk] = $vv;
						}
					}

					$copy[$v][$row['id']]['pid'] = $insertID;
					$copy[$v][$row['id']]['tstamp'] = $time;
				}
			}
		}

		// Duplicate the child records
		foreach ($copy as $k=>$v)
		{
			if (count($v))
			{
				foreach ($v as $kk=>$vv)
				{
					$objInsertStmt = $this->Database->prepare("INSERT INTO " . $k . " %s")
													->set($vv)
													->execute();

					if ($objInsertStmt->affectedRows && count($cctable[$k]) && $kk != $parentId)
					{
						$this->copyChilds($k, $objInsertStmt->insertId, $kk, $parentId);
					}
				}
			}
		}
	}


	/**
	 * Calculate the new position of a moved or inserted record
	 * @param string
	 * @param integer
	 * @param boolean
	 */
	private function getNewPosition($mode, $pid=null, $insertInto=false)
	{
		// If a sorting value and a parent ID are set
		if ($this->Database->fieldExists('pid', $this->strTable) && $this->Database->fieldExists('sorting', $this->strTable))
		{
			// PID is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if (is_null($pid) && $this->intId && $mode == 'copy')
			{
				$pid = $this->intId;
			}

			// PID is set (insert after or into the parent record)
			if (is_numeric($pid))
			{
				// Insert the current record at the beginning when inserting into the parent record
				if ($insertInto)
				{
					$newPID = $pid;
					$objSorting = $this->Database->prepare("SELECT MIN(sorting) AS `sorting` FROM " . $this->strTable . " WHERE pid=?")
												 ->execute($pid);

					// Select sorting value of the first record
					if ($objSorting->numRows)
					{
						$curSorting = $objSorting->sorting;

						// Resort if the new sorting value is not an integer or smaller than 1
						if (($curSorting % 2) != 0 || $curSorting < 1)
						{
							$objNewSorting = $this->Database->prepare("SELECT id, sorting FROM " . $this->strTable . " WHERE pid=? ORDER BY sorting" )
															->execute($pid);

							$count = 2;
							$newSorting = 128;

							while ($objNewSorting->next())
							{
								$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
											   ->limit(1)
											   ->execute(($count++*128), $objNewSorting->id);
							}
						}

						// Else new sorting = (current sorting / 2)
						else $newSorting = ($curSorting / 2);
					}

					// Else new sorting = 128
					else $newSorting = 128;
				}

				// Else insert the current record after the parent record
				elseif ($pid > 0)
				{
					$objSorting = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												 ->limit(1)
												 ->execute($pid);

					// Set parent ID of the current record as new parent ID
					if ($objSorting->numRows)
					{
						$newPID = $objSorting->pid;
						$curSorting = $objSorting->sorting;

						// Do not proceed without a parent ID
						if (is_numeric($newPID))
						{
							$objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS `sorting` FROM " . $this->strTable . " WHERE pid=? AND sorting>?")
											  				 ->execute($newPID, $curSorting);

							// Select sorting value of the next record
							if (!is_null($objNextSorting->sorting))
							{
								$nxtSorting = $objNextSorting->sorting;

								// Resort if the new sorting value is no integer or bigger than a MySQL integer
								if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
								{
									$count = 1;

									$objNewSorting = $this->Database->prepare("SELECT id, sorting FROM " . $this->strTable . " WHERE pid=? ORDER BY sorting")
																	->execute($newPID);

									while ($objNewSorting->next())
									{
										$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
													   ->execute(($count++*128), $objNewSorting->id);

										if ($objNewSorting->sorting == $curSorting)
										{
											$newSorting = ($count++*128);
										}
									}
								}

								// Else new sorting = (current sorting + next sorting) / 2
								else $newSorting = (($curSorting + $nxtSorting) / 2);
							}

							// Else new sorting = (current sorting + 128)
							else $newSorting = ($curSorting + 128);
						}
					}

					// Use the given parent ID as parent ID
					else
					{
						$newPID = $pid;
						$newSorting = 128;
					}
				}

				// Set new sorting and new parent ID
				$this->set['pid'] = intval($newPID);
				$this->set['sorting'] = intval($newSorting);
			}
		}

		// If only a parent ID is set
		elseif ($this->Database->fieldExists('pid', $this->strTable))
		{
			// PID is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if (is_null($pid) && $this->intId && $mode == 'copy')
			{
				$pid = $this->intId;
			}

			// PID is set (insert after or into the parent record)
			if (is_numeric($pid))
			{
				$objParentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												  ->limit(1)
												  ->execute($pid);

				// Insert into the parent record
				if ($insertInto)
				{
					$this->set['pid'] = $pid;
				}

				// Else insert after the parent record
				elseif ($pid > 0 && $objParentRecord->numRows)
				{
					$this->set['pid'] = $objParentRecord->pid;
				}

				// Use the given parent ID as parent ID
				else
				{
					$this->set['pid'] = $pid;
				}
			}
		}

		// If only a sorting value is set
		elseif ($this->Database->fieldExists('sorting', $this->strTable))
		{
			// ID is set (insert after the current record)
			if ($this->intId)
			{
				$objCurrentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												   ->limit(1)
											 	   ->execute($this->intId);

				// Select current record
				if ($objCurrentRecord->numRows)
				{
					$curSorting = $objCurrentRecord->sorting;

					$objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS `sorting` FROM " . $this->strTable . " WHERE sorting>?")
													 ->execute($curSorting);

					// Select sorting value of the next record
					if ($objNextSorting->numRows)
					{
						$nxtSorting = $objNextSorting->sorting;

						// Resort if the new sorting value is no integer or bigger than a MySQL integer field
						if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
						{
							$count = 1;

							$objNewSorting = $this->Database->execute("SELECT id, sorting FROM " . $this->strTable . " ORDER BY sorting");

							while ($objNewSorting->next())
							{
								$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
											   ->execute(($count++*128), $objNewSorting->id);

								if ($objNewSorting->sorting == $curSorting)
								{
									$newSorting = ($count++*128);
								}
							}
						}

						// Else new sorting = (current sorting + next sorting) / 2
						else $newSorting = (($curSorting + $nxtSorting) / 2);
					}

					// Else new sorting = (current sorting + 128)
					else $newSorting = ($curSorting + 128);

					// Set new sorting
					$this->set['sorting'] = intval($newSorting);
				}

				// ID is not set (insert at the end)
				else
				{
					$objNextSorting = $this->Database->execute("SELECT MAX(sorting) AS `sorting` FROM " . $this->strTable);

					if ($objNextSorting->numRows)
					{
						$this->set['sorting'] = intval($objNextSorting->sorting + 128);
					}
				}
			}
		}
	}


	/**
	 * Delete a record of the current table table and save it to tl_undo
	 * @param boolean
	 */
	public function delete($blnDoNotRedirect=false)
	{
		if (!$this->intId)
		{
			$this->redirect($this->getReferer());
		}

		$data = array();
		$delete = array();

		// Do not save records from tl_undo itself
		if ($this->strTable == 'tl_undo')
		{
			$this->Database->prepare("DELETE FROM " . $this->strTable . " WHERE id=?")
						   ->limit(1)
						   ->execute($this->intId);

			$this->redirect($this->getReferer());
		}

		// If there is a PID field but no parent table
		if ($this->Database->fieldExists('pid', $this->strTable) && !strlen($this->ptable))
		{
			$delete[$this->strTable] = $this->getChildRecords($this->intId, $this->strTable);
			array_unshift($delete[$this->strTable], $this->intId);
		}

		else
		{
			$delete[$this->strTable] = array($this->intId);
		}

		// Delete all child records if there is a child table
		if (count($this->ctable))
		{
			foreach ($delete[$this->strTable] as $id)
			{
				$this->deleteChilds($this->strTable, $id, $delete);
			}
		}

		$affected = 0;

		// Save each record of each table
		foreach ($delete as $table=>$fields)
		{
			foreach ($fields as $k=>$v)
			{
				$objSave = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
										  ->limit(1)
										  ->execute($v);

				if ($objSave->numRows)
				{
					$data[$table][$k] = $objSave->fetchAssoc();
				}

				$affected++;
			}
		}

		$objUndoStmt = $this->Database->prepare("INSERT INTO tl_undo (tstamp, fromTable, query, affectedRows, data) VALUES (?, ?, ?, ?, ?)")
									  ->execute(time(), $this->strTable, 'DELETE FROM '.$this->strTable.' WHERE id='.$this->intId, $affected, serialize($data));

		if ($objUndoStmt->affectedRows)
		{
			// Delete data and add a log entry
			foreach ($delete as $table=>$fields)
			{
				foreach ($fields as $k=>$v)
				{
					$this->Database->prepare("DELETE FROM " . $table . " WHERE id=?")
								   ->limit(1)
								   ->execute($v);
				}
			}

			// Add a log entry unless we are deleting from tl_log itself
			if ($this->strTable != 'tl_log')
			{
				$this->log('DELETE FROM '.$this->strTable.' WHERE id='.$data[$this->strTable][0]['id'], 'DC_Table delete()', TL_GENERAL);
			}
		}

		if (!$blnDoNotRedirect)
		{
			$this->redirect($this->getReferer());
		}
	}


	/**
	 * Delete all records that are currently shown
	 */
	public function deleteAll()
	{
		$session = $this->Session->getData();
		$ids = $session['CURRENT']['IDS'];

		if (is_array($ids) && strlen($ids[0]))
		{
			foreach ($ids as $id)
			{
				$this->intId = $id;
				$this->delete(true);
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Recursively get all related table names and records
	 * @param string
	 * @param integer
	 * @param array
	 */
	public function deleteChilds($table, $id, &$delete)
	{
		$cctable = array();
		$ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'];

		if (!is_array($ctable))
		{
			return;
		}

		// Walk through each child table
		foreach ($ctable as $v)
		{
			$this->loadDataContainer($v);
			$cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'];

			$objDelete = $this->Database->prepare("SELECT id FROM " . $v . " WHERE pid=?")
										->execute($id);

			if (!$GLOBALS['TL_DCA'][$v]['config']['doNotDeleteRecords'] && strlen($v) && $objDelete->numRows)
			{
				foreach ($objDelete->fetchAllAssoc() as $row)
				{
					$delete[$v][] = $row['id'];

					if (count($cctable[$v]))
					{
						$this->deleteChilds($v, $row['id'], $delete);
					}
				}
			}
		}
	}



	/**
	 * Send confirmation mail
	 * @param integer
	 * @param integer
	 * @return string
	 */

	public function mail($intID=false, $ajaxId=false)
	{

		$blnSend = false;

		if (strlen($this->Input->get('token')) && $this->Input->get('token') == $this->Session->get('fd_mail_send'))
		{
			$blnSend = true;
		}

		$strFormFilter = ($this->strTable == 'tl_formdata' && strlen($this->strFormKey) ? $this->sqlFormFilter : '');
		$table_alias = ($this->strTable == 'tl_formdata' ? ' f' : '');

		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		if ($intID)
		{
			$this->intId = $intID;
		}

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';
		$this->blnCreateNewVersion = false;


		// Get current record
		$sqlQuery = "SELECT * " .(count($this->arrSqlDetails) > 0 ? ', '.implode(',' , $this->arrSqlDetails) : '') ." FROM " . $this->strTable . $table_alias;
		$sqlWhere = " WHERE id=?";
		if ( $sqlWhere != '')
		{
			$sqlQuery .= $sqlWhere;
		}

		$objRow = $this->Database->prepare($sqlQuery)
								 ->limit(1)
								 ->execute($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1)
		{
			$this->log('Could not load record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_Table edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$arrSubmitted = $objRow->fetchAssoc();

		// Form
		$intFormId = 0;
		if (count($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['detail_fields']))
		{
			// try to get Form ID
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['detail_fields'] as $strField)
			{
				if ($intFormId > 0) break;
				if(strlen($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['fields'][$strField]['f_id']))
				{
					$intFormId = intval($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['fields'][$strField]['f_id']);
					$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")
						->limit(1)
						->execute($intFormId);
				}
			}
		}

		if ($intFormId == 0)
		{
			$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE title=?")
					->limit(1)
					->execute($arrSubmitted['form']);
		}

		if ($objForm->numRows < 1)
		{
			$this->log('Could not load form by ID ' . $intFormId . ' or title "'.$arrSubmitted['form'].'" of table "tl_form"!', 'DC_Formdata mail()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$arrForm = $objForm->fetchAssoc();

		if (strlen($arrForm['id']))
		{
			$arrFormFields = $this->FormData->getFormfieldsAsArray($arrForm['id']);
		}

		// Types of form fields with storable data
		$arrFFstorable = $this->FormData->arrFFstorable;

		if (empty($arrForm['confirmationMailSubject']) || (empty($arrForm['confirmationMailText']) && empty($arrForm['confirmationMailTemplate'])))
		{
			return '<p class="tl_error">Can not send this form data record.<br />Missing "Subject", "Text of confirmation mail" or "HTML-template for confirmation mail"<br />Please check configuration of form in form generator.</p>';
		}

		$this->import('String');
		$messageText = '';
		$messageHtml = '';
		$messageHtmlTmpl = '';
		$recipient  = '';
		$sender = '';
		$senderName = '';
		$attachments = array();

		$blnSkipEmpty = ($arrForm['confirmationMailSkipEmpty']) ? true : false;

		$dirImages = '';

		$sender = $arrForm['confirmationMailSender'];
		if(strlen($sender))
		{
			$sender = str_replace(array('[', ']'), array('<', '>'), $sender);
			if (strpos($sender, '<')>0)
			{
				preg_match('/(.*)?<(\S*)>/si', $sender, $parts);
				$sender = $parts[2];
				$senderName = trim($parts[1]);
			}
		}

		$recipientFieldName = $arrForm['confirmationMailRecipientField'];
		$recipient = $arrSubmitted[$recipientFieldName];
		if (is_array($recipient))
		{
			$recipient = implode(',' , $recipient);
		}
		if ($this->Input->get('recipient'))
		{
			$recipient = $this->Input->get('recipient');
		}


		$subject = $arrForm['confirmationMailSubject'];
		$messageText = $this->String->decodeEntities($arrForm['confirmationMailText']);
		$messageHtmlTmpl = $arrForm['confirmationMailTemplate'];

		if ( $messageHtmlTmpl != '' )
		{
			$fileTemplate = new File($messageHtmlTmpl);
			if ( $fileTemplate->mime == 'text/html' )
			{
				$messageHtml = $fileTemplate->getContent();
			}
		}

		// Replace tags in messageText and messageHtml
 		$tags = array();
 		preg_match_all('/{{[^{}]+}}/i', $messageText . $messageHtml, $tags);


 		// Replace tags of type {‎{form::<form field name>}}
 		foreach ($tags[0] as $tag)
 		{
 			$elements = explode('::', preg_replace(array('/^{{/i', '/}}$/i'), array('',''), $tag));

			switch (strtolower($elements[0]))
 			{
 				// Form
 				case 'form':
 					$strKey = $elements[1];
					$arrKey = explode('?', $strKey);
					$strKey = $arrKey[0];

 					$arrTagParams = null;
					if (isset($arrKey[1]) && strlen($arrKey[1]))
					{
						$arrTagParams = $this->FormData->parseInsertTagParams($tag);
					}

 					$arrField = $arrFormFields[$strKey];
 					$strType = $arrField['type'];

					$strLabel = '';
					$strVal = '';

					if ($arrTagParams && strlen($arrTagParams['label']))
					{
						$strLabel = $arrTagParams['label'];
					}

					if ( in_array($strType, $arrFFstorable) )
					{
						if ( $strType == 'efgImageSelect' )
						{
							//$strVal = $this->FormData->preparePostValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
							$strVal = trim($this->FormData->prepareDbValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]));
							$strVal = $this->formatValue($strKey, $strVal);

							if (strlen($strVal)==0 &&  $blnSkipEmpty)
							{
								$strLabel = '';
							}
							$messageText = str_replace($tag, $strLabel . ((strlen($strVal)) ? $this->Environment->base . $strVal : ''), $messageText);
		 					$messageHtml = str_replace($tag, ((strlen($strVal)) ? $strLabel .'<img src="' . $strVal . '">' : ''), $messageHtml);
						}
						elseif ($strType=='upload')
						{
							if ($arrTagParams && ((array_key_exists('attachment', $arrTagParams) && $arrTagParams['attachment'] == true) || (array_key_exists('attachement', $arrTagParams) && $arrTagParams['attachement'] == true)) )
							{
								if (strlen($arrFiles[$strKey]['tmp_name']) && is_file($arrFiles[$strKey]['tmp_name']))
								{
									if (!isset($attachments[$arrFiles[$strKey]['tmp_name']]))
									{
										$attachments[$arrFiles[$strKey]['tmp_name']] = array('name'=>$arrFiles[$strKey]['name'], 'file'=>$arrFiles[$strKey]['tmp_name'], 'mime'=>$arrFiles[$strKey]['type']);
									}

								}
								$strVal = '';
							}
							else
							{
								//$strVal = $this->FormData->preparePostValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								$strVal = $this->FormData->prepareDbValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								$strVal = $this->formatValue($strKey, $strVal);
							}
							if (!strlen($strVal) && $blnSkipEmpty)
							{
								$strLabel = '';
							}
							$messageText = str_replace($tag, $strLabel . $strVal, $messageText);
		 					$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);
						}
						else
						{
							//$strVal = $this->FormData->preparePostValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
							$strVal = $this->FormData->prepareDbValForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
							$strVal = $this->formatValue($strKey, $strVal);

							if (!strlen($strVal) && $blnSkipEmpty)
							{
								$strLabel = '';
							}
							$messageText = str_replace($tag, $strLabel . $strVal, $messageText);

							if (!is_bool(strpos($strVal, "\n")))
							{
								$strVal = preg_replace('/(<\/|<)(h\d|p|div|ul|ol|li)([^>]*)(>)(\n)/si', "\\1\\2\\3\\4", $strVal);
								$strVal = nl2br($strVal);
								$strVal = preg_replace('/(<\/)(h\d|p|div|ul|ol|li)([^>]*)(>)/si', "\\1\\2\\3\\4\n", $strVal);
							}
		 					$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);

		 				}
					}

					// replace insert tags in subject
					if (strlen($subject))
					{
						$subject = str_replace($tag, $strVal, $subject);
					}

					// replace insert tags in sender
					if (strlen($sender))
					{
						$sender = str_replace($tag, $strVal, $sender);
					}

 				break;
			}
		}

		// Replace standard insert tags
		if (strlen($messageText))
		{
			$messageText = $this->replaceInsertTags($messageText);
			$messageText = strip_tags($messageText);
		}
		if (strlen($messageHtml))
		{
			$messageHtml = $this->replaceInsertTags($messageHtml);
		}
		// replace insert tags in subject
		if (strlen($subject))
		{
			$subject = $this->replaceInsertTags($subject);
		}
		// replace insert tags in sender
		if (strlen($sender))
		{
			$sender = $this->replaceInsertTags($sender);
		}

		$confEmail = new Email();
		$confEmail->from = $sender;
		if (strlen($senderName))
		{
			$confEmail->fromName = $senderName;
		}
		$confEmail->subject = $subject;

		if (is_array($attachments) && count($attachments)>0)
		{
			foreach ($attachments as $attachment)
			{
				$confEmail->attachFile(TL_ROOT . '/' . $attachment);
			}
		}

		if ($dirImages != '')
		{
			$confEmail->imageDir = $dirImages;
		}
		if ( $messageText != '' )
		{
			$confEmail->text = $messageText;
		}
		if ( $messageHtml != '' )
		{
			$confEmail->html = $messageHtml;
		}


		// Send Mail
		if (strlen($this->Input->get('token')) && $this->Input->get('token') == $this->Session->get('fd_mail_send'))
		{

			$this->Session->set('fd_mail_send', null);
			$blnSend = true;

			// USED TO DEBUG ONLY
			/*
			$fp = fopen('../efg_mail_debug_be.txt', 'ab');
			fwrite($fp, "\n--- [".date("d-m-Y H:i")."] Mail Debug ---");
			fwrite($fp, "\n confirmation Mail:");
			fwrite($fp, "\n sender=".$sender);
			fwrite($fp, "\n mail to=".$recipient);
			fwrite($fp, "\n subject=".$subject);
			fwrite($fp, "\n plain text:\n");
			fwrite($fp, $messageText);
			fwrite($fp, "\n html text:\n");
			fwrite($fp, $messageHtml);
			fclose($fp);
			*/

			if ($blnSend)
			{
				$confEmail->sendTo($recipient);

				$_SESSION['TL_INFO'][] = sprintf($GLOBALS['TL_LANG']['tl_formdata']['mail_sent'], $recipient);
				$url = $this->Environment->base . preg_replace('/&(amp;)?(token|recipient)=[^&]*/', '', $this->Environment->request);
			}

		}


		$strToken = md5(uniqid('', true));
		$this->Session->set('fd_mail_send', $strToken);

		// Preview Mail
		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_formdata']['mail'][0].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->script, ENCODE_AMPERSANDS).'" id="tl_formdata_send" class="tl_form" method="get">
<div class="tl_formbody_edit fd_mail_send">
<input type="hidden" name="do" value="' . $this->Input->get('do') . '" />
<input type="hidden" name="table" value="' . $this->Input->get('table') . '" />
<input type="hidden" name="act" value="' . $this->Input->get('act') . '" />
<input type="hidden" name="id" value="' . $this->Input->get('id') . '" />
<input type="hidden" name="token" value="' . $strToken . '" />

<table cellpadding="0" cellspacing="0" class="prev_header" summary="">
  <tr class="row_0">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_formdata']['mail_sender'][0] . '</td>
    <td class="col_1">' . $sender . '</td>
  </tr>

  <tr class="row_1">
    <td class="col_0"><label for="ctrl_formdata_recipient">' . $GLOBALS['TL_LANG']['tl_formdata']['mail_recipient'][0]. '</label></td>
    <td class="col_1"><input name="recipient" type="ctrl_recipient" class="tl_text" value="' . $recipient . '" '.($blnSend ? 'disabled="disabled"' : '').'/></td>
  </tr>

  <tr class="row_2">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_formdata']['mail_subject'][0] . '</td>
    <td class="col_1">' . $subject . '</td>
  </tr>';

		if (is_array($attachments) && count($attachments) > 0)
		{
  	$return .= '
  <tr class="row_3">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_formdata']['attachments'] . '</td>
    <td class="col_1">' . implode(', ', $attachments) . '</td>
  </tr>';
		}

  $return .= '
</table>

<h3>' . $GLOBALS['TL_LANG']['tl_formdata']['mail_body_plaintext'][0] . '</h3>
<div class="preview_plaintext">
' . nl2br($messageText) . '
</div>';

		if (strlen($messageHtml))
		{
	$return .= '
<h3>' . $GLOBALS['TL_LANG']['tl_formdata']['mail_body_html'][0] . '</h3>
<div class="preview_html">
' . preg_replace(array('/.*?<body.*?>/si','/<\/body>.*$/si'), array('', ''), $messageHtml) . '
</div>';
		}

$return .= '
</div>';

		if (!$blnSend)
		{
	$return .= '
<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" id="send" class="tl_submit" alt="send mail" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_formdata']['mail'][0]).'" />
</div>

</div>';
		}

$return .= '
</form>';

		return $return;
	}


	/**
	 * Format a value
	 * @param mixed
	 * @return mixed
	 */
	public function formatValue($k, $value)
	{
		$value = deserialize($value);

		$rgxp = '';
		if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['rgxp'] )
		{
			$rgxp = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['rgxp'];
		}
		else
		{
			$rgxp = $this->arrFF[$k]['rgxp'];
		}

		// Array
		if (is_array($value))
		{
			$value = implode(', ', $value);
		}

		// Date and time
		if ($value && $rgxp == 'date')
		{
			$value = date($GLOBALS['TL_CONFIG']['dateFormat'], $value);
		}
		elseif ($value && $rgxp == 'time')
		{
			$value = date($GLOBALS['TL_CONFIG']['timeFormat'], $value);
		}
		elseif ($value && $rgxp == 'datim')
		{
			$value = date($GLOBALS['TL_CONFIG']['datimFormat'], $value);
		}
		elseif ($value && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='checkbox'
				|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='efgLookupCheckbox'
				|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='select'
				|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='conditionalselect'
				|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='efgLookupSelect'
				|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType']=='radio') )
		{
			$value = str_replace('|', ', ', $value);
		}

		// owner fields fd_member, fd_user
		if (in_array($k, $this->arrBaseFields) && in_array($k, $this->arrOwnerFields))
		{
			if ($k == 'fd_member')
			{
				$value = $this->arrMembers[$value];
			}
			if ($k == 'fd_user')
			{
				$value = $this->arrUsers[$value];
			}
		}

		return $value;
	}



	/**
	 * Restore one or more deleted records
	 */
	public function undo()
	{
		$objRecords = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
									 ->limit(1)
								 	 ->execute($this->intId);

		// Check whether there is a record
		if ($objRecords->numRows < 1)
		{
			$this->redirect($this->getReferer());
		}

		$error = false;
		$query = $objRecords->query;
		$data = deserialize($objRecords->data);

		if (!is_array($data))
		{
			$this->redirect($this->getReferer());
		}

		// Restore the data
		foreach ($data as $table=>$fields)
		{
			foreach ($fields as $row)
			{
				$restore = array();

				foreach ($row as $k=>$v)
				{
					$restore[$k] = $v;
				}

				$objInsertStmt = $this->Database->prepare("INSERT INTO " . $table . " %s")
												->set($restore)
												->execute();

				// Do not delete record from tl_undo if there is an error
				if ($objInsertStmt->affectedRows < 1)
				{
					$error = true;
				}
			}
		}

		// Add log entry and delete record from tl_undo if there was no error
		if (!$error)
		{
			$this->log('Undone '.$query, 'DC_Table undo()', TL_GENERAL);

			$this->Database->prepare("DELETE FROM " . $this->strTable . " WHERE id=?")
						   ->limit(1)
						   ->execute($this->intId);
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Change the order of two neighbour database records
	 */
	public function move()
	{
		// Proceed only if all mandatory variables are set
		if ($this->intId && $this->Input->get('sid') && (!$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'] || !in_array($this->intId, $this->root)))
		{
			$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=? OR id=?")
									 ->limit(2)
									 ->execute($this->intId, $this->Input->get('sid'));

			$row = $objRow->fetchAllAssoc();

			if ($row[0]['pid'] == $row[1]['pid'])
			{
				$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
							   ->execute($row[0]['sorting'], $row[1]['id']);

				$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
							   ->execute($row[1]['sorting'], $row[0]['id']);
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Autogenerate a form to edit the current database record
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function edit($intID=false, $ajaxId=false)
	{

		$strFormFilter = ($this->strTable == 'tl_formdata' && strlen($this->strFormKey) ? $this->sqlFormFilter : '');
		$table_alias = ($this->strTable == 'tl_formdata' ? ' f' : '');

		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		if ($intID)
		{
			$this->intId = $intID;
		}

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';
		$this->blnCreateNewVersion = false;

		// Change version
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && $this->Input->post('FORM_SUBMIT') == 'tl_version' && strlen($this->Input->post('version')))
		{
			$objData = $this->Database->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND version=?")
									  ->limit(1)
									  ->execute($this->strTable, $this->intId, $this->Input->post('version'));

			if ($objData->numRows)
			{
				$data = deserialize($objData->data);

				if (is_array($data))
				{
					$this->Database->prepare("UPDATE " . $objData->fromTable . " %s WHERE id=?")
								   ->set($data)
								   ->execute($this->intId);

					$this->Database->prepare("UPDATE tl_version SET active='' WHERE pid=?")
								   ->execute($this->intId);

					$this->Database->prepare("UPDATE tl_version SET active=1 WHERE pid=? AND version=?")
								   ->execute($this->intId, $this->Input->post('version'));

					$this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $this->intId, $this->strTable), 'DC_Table edit()', TL_GENERAL);
				}
			}

			$this->reload();
		}

		// Get current record
		$sqlQuery = "SELECT * " .(count($this->arrSqlDetails) > 0 ? ', '.implode(',' , $this->arrSqlDetails) : '') ." FROM " . $this->strTable . $table_alias;
		$sqlWhere = " WHERE id=?";
		if ( $sqlWhere != '')
		{
			$sqlQuery .= $sqlWhere;
		}

		$objRow = $this->Database->prepare($sqlQuery)
								 ->limit(1)
								 ->execute($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1)
		{
			$this->log('Could not load record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_Table edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		// Create a new version if there is none
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
		{
			$objVersion = $this->Database->prepare("SELECT id FROM tl_version WHERE fromTable=? AND pid=?")
										 ->limit(1)
										 ->execute($this->strTable, $this->intId);

			if ($objVersion->numRows < 1)
			{
				$this->createNewVersion($this->strTable, $this->intId);
			}
		}


		// Build an array from boxes and rows
		$this->strPalette = $this->getPalette();
		$boxes = trimsplit(';', $this->strPalette);
		$legends = array();

		if (count($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*\]$/i', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^\{.*\}$/i', $vv))
					{
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					}

					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]))
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
			}

			$class = 'tl_tbox block';
			$fs = $this->Session->get('fieldset_states');

			// Render boxes
			foreach ($boxes as $k=>$v)
			{
				$strAjax = '';
				$blnAjax = false;
				$legend = '';

				if (isset($legends[$k]))
				{
					list($key, $cls) = explode(':', $legends[$k]);
					$legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this, \'' . $key . '\', \'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
				}

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					if (isset($fs[$this->strTable][$key]))
					{
						$class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
					}
					else
					{
						$class .= (($cls && $legend) ? ' ' . $cls : '');
					}

					$return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_'.$key.'"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
				}
				else
				{
					$return .= "\n\n" . '<div class="'.$class.'">';
				}

				// Build rows of the current box
				foreach ($v as $kk=>$vv)
				{
					if ($vv == '[EOF]')
					{
						if ($this->Input->post('isAjax') && $blnAjax)
						{
							return $strAjax . '<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'" />';
						}

						$blnAjax = false;
						$return .= "\n" . '</div>';

						continue;
					}

					if (preg_match('/^\[.*\]$/i', $vv))
					{
						$thisId = 'sub_' . substr($vv, 1, -1);
						$blnAjax = ($this->Input->post('isAjax') && $ajaxId == $thisId) ? true : false;
						$return .= "\n" . '<div id="'.$thisId.'">';

						continue;
					}

					$this->strField = $vv;
					$this->strInputName = $vv;
					$this->varValue = $objRow->$vv;

					// Call options_callback
					if (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback']))
					{
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][1];

						$this->import($strClass);
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $this->$strClass->$strMethod($this);
					}

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->varValue = $this->$callback[0]->$callback[1]($this->varValue, $this);
							}
						}
					}


					// prepare values of special fields like rado, select and checkbox
					$strInputType = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'];

					// render inputType hidden as inputType text in Backend
					if ($strInputType == 'hidden')
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'text';
					}

					// field types radio, select, multi checkbox
					if ( $strInputType=='radio' || $strInputType=='select' || $strInputType=='conditionalselect' || ( $strInputType=='checkbox'  && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] ) )
					{

						if (in_array($this->strField, $this->arrBaseFields) && in_array($this->strField, $this->arrOwnerFields) )
						{
							if ($this->strField == 'fd_user')
							{
								if ($this->User && $this->User->id)
								{
									$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] = $this->User->id;
								}
							}
						}
						elseif (!is_array($this->varValue))
						{

							// foreignKey fields
							if ($strInputType == 'select' && strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']))
							{
								// include blank Option
								//$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][0] = "-";
								$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['includeBlankOption'] = true;

								$arrKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
								$strForeignTable = $arrKey[0];
								$strForeignField = $arrKey[1];

								// WHERE condition for foreignKey
								$strForeignKeyCond = '';
								if (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere']))
								{
									$strForeignKeyCond = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'];
								}

								// check if foreignKey table is a formdata table
								if (substr($strForeignTable, 0, 3) == 'fd_')
								{
									$strFormKey = substr($strForeignTable, 3);
									$strForeignDcaKey = $strForeignTable;
									$strForeignTable = 'tl_formdata';

									// backup current dca and load dca for foreign formdata
									$BAK_DCA = $GLOBALS['TL_DCA'][$this->strTable];
									$this->loadDataContainer($strForeignDcaKey);

									$strForeignField = $arrKey[1];
									$strForeignSqlField = '(SELECT value FROM tl_formdata_details WHERE ff_name="' .$strForeignField. '" AND pid=f.id ) AS `' . $strForeignField . '`';

									$sqlForeignFd = "SELECT f.id," . $strForeignSqlField . " FROM tl_formdata f, tl_formdata_details fd ";
									$sqlForeignFd .= "WHERE (f.id=fd.pid) AND f." . $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterKey'] . "='" . $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterValue'] . "' AND fd.ff_name='" . $strForeignField . "'";


									if (strlen($strForeignKeyCond))
									{
										$arrForeignKeyCond = preg_split('/([\s!=><]+)/', $strForeignKeyCond, -1, PREG_SPLIT_DELIM_CAPTURE);
										$strForeignCondField = $arrForeignKeyCond[0];
										unset($arrForeignKeyCond[0]);
										if (in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['baseFields']))
										{
											$sqlForeignFd .= ' AND f.' . $strForeignCondField . implode('', $arrForeignKeyCond);
										}
										if (in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['detailFields']))
										{
											$sqlForeignFd .= ' AND (SELECT value FROM tl_formdata_details WHERE ff_name="' .$strForeignCondField. '" AND pid=f.id ) ' . implode('', $arrForeignKeyCond);
										}
									}

									$objForeignFd = $this->Database->prepare($sqlForeignFd)->execute();

									// reset current dca
									$GLOBALS['TL_DCA'][$this->strTable] = $BAK_DCA;
									unset($BAK_DCA);

									if ($objForeignFd->numRows)
									{
										$arrForeignRecords = $objForeignFd->fetchAllAssoc();
										if (count($arrForeignRecords))
										{
											foreach ($arrForeignRecords as $arrForeignRecord )
											{
												$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField] .  ' [~' . $arrForeignRecord['id'] . '~]';
											}
										}
										unset($arrForeignRecords);
									}

									// unset dca 'foreignKey': prevents Controller->prepareForWidget to read options from table instead handle as normal select
									unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
									unset($objForeignFd);
								}
								// foreignKey table is 'normal' table
								elseif ($this->Database->fieldExists($strForeignField, $strForeignTable))
								{
									$blnAlias = $this->Database->fieldExists('alias', $strForeignTable);

									$sqlForeign = "SELECT id," . ($blnAlias ? "alias," : "") . $strForeignField . " FROM " . $strForeignTable . ( strlen($strForeignKeyCond) ? " WHERE ".$strForeignKeyCond : '' ) . " ORDER BY " . $strForeignField;

									$objForeign = $this->Database->prepare($sqlForeign)->execute();

									if ($objForeign->numRows)
									{
										$arrForeignRecords = $objForeign->fetchAllAssoc();
										if (count($arrForeignRecords))
										{
											foreach ($arrForeignRecords as $arrForeignRecord )
											{
												$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField] . ' [~' . ( ($blnAlias && strlen($arrForeignRecord['alias'])) ? $arrForeignRecord['alias'] : $arrForeignRecord['id'] ) . '~]';
											}
										}
										unset($arrForeignRecords);
									}

									// unset dca 'foreignKey': prevents Controller->prepareForWidget to read options from table instead handle as normal select
									unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
									unset($objForeign);
								}
								// sort options on label
								asort($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
							} // foreignKey field

							$arrValues = explode('|', $this->varValue);

							if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['efgStoreValues'])
							{
								$this->varValue = $arrValues;
							}
							else
							{
								// prepare values
								$arrNewValues = array();

								foreach($arrValues as $kVal => $vVal)
								{
									$vVal = trim($vVal);
									$strK = false;
	 								if (strlen($vVal) && $strK == false)
	 								{
										// handle grouped options
										foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] as $strOptsKey => $varOpts)
										{
											if (is_array($varOpts))
											{
												$strK = array_search($vVal, $varOpts);
											}
											else
											{
												$strK = array_search($vVal, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
											}

											if ($strK !== false)
											{
												$arrNewValues[] = $strK;
												break;
											}
										}

										// add saved option to avaliable options if not exists
										if ($strK === false)
										{
											$strK = preg_replace('/(.*?\[)(.*?)(\])/si', '$2', $vVal);
											$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$strK] = $vVal;
											$arrNewValues[] = $strK;
										}

	 								}
								}

								$this->varValue = $arrNewValues;
							}
						}
					} // field types radio, select, multi checkbox

					// field type single checkbox
					if ( $strInputType=='checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] )
					{
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']))
						{
							$arrVals = array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
						}
						else
						{
							$arrVals = array($this->varValue);
						}

						// tom, 2007-09-27, bugfix:
						// .. not if value is empty or does not exist at all
						// .. for example record is created by frontend form, checkbox was not checked, then no record in tl_formdata_details exisits
						if (strlen($arrVals[0]) && strlen($this->varValue))
						{
							$this->varValue = $arrVals[0];
						}
						else
						{
							$this->varValue = "";
						}
					} // field typ single checkbox

					// field type efgLookupSelect
					if ( $strInputType=='efgLookupSelect' )
					{

						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v)
							{
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupSelect as SelectMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'select';

					} // field type efgLookupSelect

					// field type efgLookupCheckbox
					if ( $strInputType=='efgLookupCheckbox' )
					{
						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v)
							{
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupCheckbox as CheckboxMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';

					} // field type efgLookupCheckbox

					// field type efgLookupRadio
					if ( $strInputType=='efgLookupRadio' )
					{

						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v)
							{
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupRadio as RadioMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'radio';

					} // field type efgLookupRadio


					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->varValue = $this->$callback[0]->$callback[1]($this->varValue, $this);
							}
						}
					}

					// Build row
					$blnAjax ? $strAjax .= $this->row() : $return .= $this->row();
				}

				$class = 'tl_box block';

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					$return .= "\n" . '</fieldset>';
				}
				else
				{
					$return .= "\n" . '</div>';
				}

			}
		}

		// Add some buttons and end the form
		$return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="save all changes" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'" />
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" alt="save all changes and return" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" alt="save all changes and create new record" accesskey="n" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']).'" />' : '') .'
</div>

</div>
</form>';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"'.(count($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.specialchars($this->strTable).'" />
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'" />'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;


		// Reload the page to prevent _POST variables from being sent twice
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
		{
			$arrValues = $this->values;
			array_unshift($arrValues, time());

			// Call onsubmit_callback
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this);
				}
			}

			// Save current version
			if ($this->blnCreateNewVersion && $this->Input->post('SUBMIT_TYPE') != 'auto')
			{
				$this->createNewVersion($this->strTable, $this->intId);
				$this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table edit()', TL_GENERAL);
			}

			// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
			$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE " . implode(' AND ', $this->procedure))
						   ->execute($arrValues);

			// Redirect
			if (isset($_POST['saveNclose']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->getReferer());
			}

			elseif (isset($_POST['saveNedit']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

				$strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
				$strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

				$this->redirect($strUrl);
			}

			elseif (isset($_POST['saveNback']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->Environment->script . '?do=' . $this->Input->get('do'));
			}

			elseif (isset($_POST['saveNcreate']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->Environment->script . '?do=' . $this->Input->get('do');

				if (isset($_GET['table']))
				{
					$strUrl .= '&amp;table=' . $this->Input->get('table');
				}

				// Tree view
				if ($this->treeView)
				{
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
				{
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . CURRENT_ID : '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID;
				}

				// List view
				else
				{
					$strUrl .= strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
				}

				$this->redirect($strUrl);
			}

			$this->reload();
		}

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent(\'domready\', function()
{
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'div.tl_error\').getPosition().y - 20));
});
//--><!]]>
</script>';
		}

		return $return;
	}


	/**
	 * Autogenerate a form to edit all records that are currently shown
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function editAll($intId=false, $ajaxId=false)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table editAll()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$return = '';
		$this->import('BackendUser', 'User');

		// Get current IDs from session
		$session = $this->Session->getData();
		$ids = $session['CURRENT']['IDS'];

		if ($this->Input->post('isAjax'))
		{
			$ids = array($intId);
		}

		// Save field selection in session
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable.'_all' && $this->Input->get('fields'))
		{
			$session['CURRENT'][$this->strTable] = deserialize($this->Input->post('all_fields'));
			$this->Session->setData($session);
		}

		// Add fields
		$fields = $session['CURRENT'][$this->strTable];

		if (is_array($fields) && count($fields) && $this->Input->get('fields'))
		{
			$class = 'tl_tbox block';

			// Walk through each record
			foreach ($ids as $id)
			{
				$this->intId = $id;
				$this->procedure = array('id=?');
				$this->values = array($this->intId);
				$this->blnCreateNewVersion = false;
				$this->strPalette = trimsplit('[;,]', $this->getPalette());

				// Create a new version if there is none
				if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
				{
					$objVersion = $this->Database->prepare("SELECT id FROM tl_version WHERE fromTable=? AND pid=?")
												 ->limit(1)
												 ->execute($this->strTable, $this->intId);

					if ($objVersion->numRows < 1)
					{
						$this->createNewVersion($this->strTable, $this->intId);
					}
				}

				// Add meta fields if the current user is an administrator
				if ($this->User->isAdmin)
				{
					if ($this->Database->fieldExists('sorting', $this->strTable))
					{
						array_unshift($this->strPalette, 'sorting');
					}

					if ($this->Database->fieldExists('pid', $this->strTable))
					{
						array_unshift($this->strPalette, 'pid');
					}

					$GLOBALS['TL_DCA'][$this->strTable]['fields']['pid'] = array('label'=>&$GLOBALS['TL_LANG']['MSC']['pid'], 'inputType'=>'text', 'eval'=>array('rgxp'=>'digit'));
					$GLOBALS['TL_DCA'][$this->strTable]['fields']['sorting'] = array('label'=>&$GLOBALS['TL_LANG']['MSC']['sorting'], 'inputType'=>'text', 'eval'=>array('rgxp'=>'digit'));
				}

				// Begin current row
				$strAjax = '';
				$blnAjax = false;
				$return .= '
<div class="'.$class.'">';

				$class = 'tl_box block';
				$formFields = array();

				$arrBaseFields = array();
				$arrDetailFields = array();
				$arrSqlDetails = array();

				foreach ($fields as $strField)
				{
					if (in_array($strField, $this->arrBaseFields))
					{
						$arrBaseFields[] = $strField;
					}
					if (in_array($strField, $this->arrDetailFields))
					{
						$arrDetailFields[] = $strField;
						$arrSqlDetails[] = '(SELECT value FROM tl_formdata_details WHERE ff_name=\'' .$strField. '\' AND pid=f.id) AS `' . $strField .'`';
					}
				}

				$strSqlFields = (count($arrBaseFields)>0 ? implode(', ', $arrBaseFields) : '');
				$strSqlFields .= (count($arrSqlDetails)>0 ? (strlen($strSqlFields) ? ', ' : '') . implode(', ', $arrSqlDetails) : '');


				// Get field values
				$objValue = $this->Database->prepare("SELECT " . $strSqlFields . " FROM " . $this->strTable . " f WHERE id=?")
										   ->limit(1)
										   ->execute($this->intId);

				foreach ($this->strPalette as $v)
				{
					// Check whether field is excluded
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['exclude'])
					{
						continue;
					}

					if ($v == '[EOF]')
					{
						if ($this->Input->post('isAjax') && $blnAjax)
						{
							return $strAjax . '<input type="hidden" name="FORM_FIELDS_'.$id.'[]" value="'.specialchars(implode(',', $formFields)).'" />';
						}

						$blnAjax = false;
						$return .= "\n  " . '</div>';

						continue;
					}

					if (preg_match('/^\[.*\]$/i', $v))
					{
						$thisId = 'sub_' . substr($v, 1, -1) . '_' . $id;
						$blnAjax = ($this->Input->post('isAjax') && $ajaxId == $thisId) ? true : false;
						$return .= "\n  " . '<div id="'.$thisId.'">';

						continue;
					}

					if (!in_array($v, $fields))
					{
						continue;
					}

					$this->strField = $v;
					$this->strInputName = $v.'_'.$this->intId;
					$formFields[] = $v.'_'.$this->intId;

					// Set default value and try to load the current value from DB
					$this->varValue = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] : '';

					if ($objValue->$v !== false)
					{
						$this->varValue = $objValue->$v;
					}


					// prepare values of special fields like rado, select and checkbox
					$strInputType = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'];

					// render inputType hidden as inputType text in Backend
					if ($strInputType == 'hidden')
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'text';
					}


					// field types radio, select, multi checkbox
					if ( $strInputType=='radio' || $strInputType=='select' || $strInputType=='conditionalselect' || ( $strInputType=='checkbox'  && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] ) )
					{

						if (in_array($this->strField, $this->arrBaseFields) && in_array($this->strField, $this->arrOwnerFields) )
						{
							if ($this->strField == 'fd_user')
							{
								if ($this->User && $this->User->id)
								{
									$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] = $this->User->id;
								}
							}
						}
						elseif (!is_array($this->varValue))
						{
							// foreignKey fields
							if ($strInputType == 'select' && strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']))
							{
								// include blank Option
								$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][0] = "-";

								$arrKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
								$strForeignTable = $arrKey[0];
								$strForeignField = $arrKey[1];

								// WHERE condition for foreignKey
								$strForeignKeyCond = '';
								if (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere']))
								{
									$strForeignKeyCond = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'];
								}

								// check if foreignKey table is a formdata table
								if (substr($strForeignTable, 0, 3) == 'fd_')
								{
									$strFormKey = substr($strForeignTable, 3);
									$strForeignDcaKey = $strForeignTable;
									$strForeignTable = 'tl_formdata';

									// backup current dca and load dca for foreign formdata
									$BAK_DCA = $GLOBALS['TL_DCA'][$this->strTable];
									$this->loadDataContainer($strForeignDcaKey);

									$strForeignField = $arrKey[1];
									$strForeignSqlField = '(SELECT value FROM tl_formdata_details WHERE ff_name="' .$strForeignField. '" AND pid=f.id ) AS `' . $strForeignField . '`';

									$sqlForeignFd = "SELECT f.id," . $strForeignSqlField . " FROM tl_formdata f, tl_formdata_details fd ";
									$sqlForeignFd .= "WHERE (f.id=fd.pid) AND f." . $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterKey'] . "='" . $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterValue'] . "' AND fd.ff_name='" . $strForeignField . "'";


									if (strlen($strForeignKeyCond))
									{
										$arrForeignKeyCond = preg_split('/([\s!=><]+)/', $strForeignKeyCond, -1, PREG_SPLIT_DELIM_CAPTURE);
										$strForeignCondField = $arrForeignKeyCond[0];
										unset($arrForeignKeyCond[0]);
										if (in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['baseFields']))
										{
											$sqlForeignFd .= ' AND f.' . $strForeignCondField . implode('', $arrForeignKeyCond);
										}
										if (in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['detailFields']))
										{
											$sqlForeignFd .= ' AND (SELECT value FROM tl_formdata_details WHERE ff_name="' .$strForeignCondField. '" AND pid=f.id ) ' . implode('', $arrForeignKeyCond);
										}
									}

									$objForeignFd = $this->Database->prepare($sqlForeignFd)->execute();

									// reset current dca
									$GLOBALS['TL_DCA'][$this->strTable] = $BAK_DCA;
									unset($BAK_DCA);

									if ($objForeignFd->numRows)
									{
										$arrForeignRecords = $objForeignFd->fetchAllAssoc();
										if (count($arrForeignRecords))
										{
											foreach ($arrForeignRecords as $arrForeignRecord )
											{
												$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField] .  ' [~' . $arrForeignRecord['id'] . '~]';
											}
										}
										unset($arrForeignRecords);
									}

									// unset dca 'foreignKey': prevents Controller->prepareForWidget to read options from table instead handle as normal select
									unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
									unset($objForeignFd);
								}
								// foreignKey table is 'normal' table
								elseif ($this->Database->fieldExists($strForeignField, $strForeignTable))
								{
									$blnAlias = $this->Database->fieldExists('alias', $strForeignTable);

									$sqlForeign = "SELECT id," . ($blnAlias ? "alias," : "") . $strForeignField . " FROM " . $strForeignTable . ( strlen($strForeignKeyCond) ? " WHERE ".$strForeignKeyCond : '' ) . " ORDER BY " . $strForeignField;

									$objForeign = $this->Database->prepare($sqlForeign)->execute();

									if ($objForeign->numRows)
									{
										$arrForeignRecords = $objForeign->fetchAllAssoc();
										if (count($arrForeignRecords))
										{
											foreach ($arrForeignRecords as $arrForeignRecord )
											{
												$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField] . ' [~' . ( ($blnAlias && strlen($arrForeignRecord['alias'])) ? $arrForeignRecord['alias'] : $arrForeignRecord['id'] ) . '~]';
											}
										}
										unset($arrForeignRecords);
									}

									// unset dca 'foreignKey': prevents Controller->prepareForWidget to read options from table instead handle as normal select
									unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
									unset($objForeign);
								}
								// sort options on label
								asort($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
							}

							$arrValues = explode('|', $this->varValue);


							if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['efgStoreValues'])
							{
								$this->varValue = $arrValues;
							}
							else
							{
								// prepare values
								$arrNewValues = array();

								foreach($arrValues as $kVal => $vVal)
								{
									$vVal = trim($vVal);
									$strK = false;
	 								if (strlen($vVal) && $strK == false)
	 								{
										// handle grouped options
										foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] as $strOptsKey => $varOpts)
										{
											if (is_array($varOpts))
											{
												$strK = array_search($vVal, $varOpts);
											}
											else
											{
												$strK = array_search($vVal, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
											}

											if ($strK !== false)
											{
												$arrNewValues[] = $strK;
												break;
											}
										}

										// add saved option to avaliable options if not exists
										if ($strK === false)
										{
											$strK = preg_replace('/(.*?\[)(.*?)(\])/si', '$2', $vVal);
											$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$strK] = $vVal;
											$arrNewValues[] = $strK;
										}

	 								}
								}

								$this->varValue = $arrNewValues;
							}

						}
					} // field types radio, select, multi checkbox

					// field type single checkbox
					if ( $strInputType=='checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] )
					{
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']))
						{
							$arrVals = array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
						}
						else
						{
							$arrVals = array($this->varValue);
						}

						// tom, 2007-09-27, bugfix:
						// .. not if value is empty or does not exist at all
						// .. for example record is created by frontend form, checkbox was not checked, then no record in tl_formdata_details exisits
						if (strlen($arrVals[0]) && strlen($this->varValue))
						{
							$this->varValue = $arrVals[0];
						}
						else
						{
							$this->varValue = "";
						}
					} // field typ single checkbox

					// field type efgLookupSelect
					if ( $strInputType=='efgLookupSelect' )
					{

						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v) {
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupSelect as SelectMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'select';

					} // field type efgLookupSelect

					// field type efgLookupCheckbox
					if ( $strInputType=='efgLookupCheckbox' )
					{

						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v) {
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupCheckbox as CheckboxMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';

					} // field type efgLookupCheckbox

					// field type efgLookupRadio
					if ( $strInputType=='efgLookupRadio' )
					{

						$arrFieldOptions = $this->FormData->prepareDcaOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

						// prepare options array and value
						if (is_array($arrFieldOptions))
						{
							// prepare options array
							$arrNewOptions = array();
							foreach ($arrFieldOptions as $k => $v)
							{
								$arrNewOptions[$v['value']] = $v['label'];
							}
						}

						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

						// prepare varValue
						if (strlen($this->varValue))
						{
							if (!is_array($this->varValue))
							{
								$this->varValue = explode('|', $this->varValue);
							}
							foreach ($this->varValue as $k => $v) {
								$sNewVal = array_search($v, $arrNewOptions);
								if ($sNewVal)
								{
									$this->varValue[$v] = $sNewVal;
								}
							}
						}

						// render type efgLookupRadio as RadioMenu
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'radio';

					} // field type efgLookupRadio


					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							$this->import($callback[0]);
							$this->varValue = $this->$callback[0]->$callback[1]($this->varValue, $this);
						}
					}

					// input type efgLookupCheckbox: modify DCA to render as CheckboxMenu
					if ( $strInputType=='efgLookupCheckbox' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';
					}
					// input type efgLookupRadio: modify DCA to render as RadioMenu
					if ( $strInputType=='efgLookupRadio' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'radio';
					}
					// input type efgLookupCheckbox: modify DCA to render as CheckboxMenu
					if ( $strInputType=='efgLookupCheckbox' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';
					}

					// Build the current row
					$blnAjax ? $strAjax .= $this->row() : $return .= $this->row();

					// input type efgLookupCheckbox: reset DCA inputType
					if ( $strInputType=='efgLookupCheckbox' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'efgLookupCheckbox';
					}
					// input type efgLookupRadio: reset DCA inputType
					if ( $strInputType=='efgLookupRadio' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'efgLookupRadio';
					}
					// input type efgLookupSelect: reset DCA inputType
					if ( $strInputType=='efgLookupSelect' )
					{
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'efgLookupSelect';
					}

				}

				// Close box
				$return .= '
  <input type="hidden" name="FORM_FIELDS_'.$this->intId.'[]" value="'.specialchars(implode(',', $formFields)).'" />
</div>';

				// Create a new version
				if ($this->blnCreateNewVersion && $this->Input->post('SUBMIT_TYPE') != 'auto')
				{
					$this->createNewVersion($this->strTable, $this->intId);
					$this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
				}

				// Call onsubmit_callback
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
				{
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
					{
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($this);
					}
				}
			}

			// Add the form
			$return = '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'" />'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="save all changes" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'" />
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" alt="save all changes and return" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'" />
</div>

</div>
</form>';

			// Set the focus if there is an error
			if ($this->noReload)
			{
				$return .= '

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent(\'domready\', function()
{
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'div.tl_error\').getPosition().y - 20));
});
//--><!]]>
</script>';
			}

			// Reload the page to prevent _POST variables from being sent twice
			if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
			{
				if ($this->Input->post('saveNclose'))
				{
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		}

		// Else show a form to select the fields
		else
		{
			$options = '';
			$fields = array();

			// Add fields of the current table
			$fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

			// Add meta fields if the current user is an administrator
			if ($this->User->isAdmin)
			{
				if ($this->Database->fieldExists('sorting', $this->strTable) && !in_array('sorting', $fields))
				{
					array_unshift($fields, 'sorting');
				}

				if ($this->Database->fieldExists('pid', $this->strTable) && !in_array('pid', $fields))
				{
					array_unshift($fields, 'pid');
				}
			}

			// Show all non-excluded fields
			foreach ($fields as $field)
			{
				if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType']) || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback']))))
				{
					$options .= '
<input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.specialchars($field).'" /> <label for="all_'.$field.'" class="tl_checkbox_label">'.(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field][0]).'</label><br />';
				}
			}

			// Return select menu
			$return .= (($_POST && !count($_POST['all_fields'])) ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all" />

<div class="tl_tbox block">
<h3><label for="fields">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</label></h3>'.(($_POST && !count($_POST['all_fields'])) ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : '').'
<div id="fields" class="tl_checkbox_container">
<input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)" /> <label for="check_all" style="color:#a6a6a6;"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br />'.$options.'
</div>'.(($GLOBALS['TL_CONFIG']['showHelp'] && strlen($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
<p class="tl_help">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="continue" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['continue']).'" />
</div>

</div>
</form>';
		}

		// Return
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
	}


	/**
	 * Save the current value
	 * @param mixed
	 * @throws Exception
	 */
	protected function save($varValue)
	{
		// table to write to tl_formdata (base fields) or tl_formdata_details (detail fields)
		$strTargetTable = $this->strTable;
		$strTargetField = $this->strField;
		$blnDetailField = false;

		if ($this->Input->post('FORM_SUBMIT') != $this->strTable)
		{
			return;
		}

		$arrData = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField];

		// if field is one of detail fields
		if (in_array($strTargetField, $this->arrDetailFields))
		{
			$strTargetTable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'][0];
			$blnDetailField = true;
		}

		// Convert date formats into timestamps
		if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
		{
			$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
			$varValue = $objDate->tstamp;
		}

		// Convert checkbox, radio, select, conditionalselect to store the values instead of keys
		if ( ($arrData['inputType']=='checkbox' && $arrData['eval']['multiple']) || $arrData['inputType']=='radio' || $arrData['inputType']=='select' || $arrData['inputType']=='conditionalselect')
		{

			if (!in_array($this->strField, $this->arrOwnerFields))
			{
				$arrOpts = $arrData['options'];

				// OptGroups can not be saved
				$arrNewOpts = array();

				foreach ($arrOpts as $strKey => $varOpt)
				{
					if (is_array($varOpt) && count($varOpt))
					{
						foreach ($varOpt as $keyOpt => $valOpt)
						{
							$arrNewOpts[$keyOpt] = $valOpt;
						}
					}
					else
					{
						$arrNewOpts[$strKey] = $varOpt;
					}
				}
				$arrOpts = $arrNewOpts;
				unset($arrNewOpts);

				$arrSel = deserialize($varValue, true);
				if (is_array($arrSel) && count($arrSel)>0)
				{
					$arrSel = array_flip($arrSel);
					// use options value or options label
					if ($arrData['eval']['efgStoreValues'])
					{
						$arrVals = array_keys(array_intersect_key($arrOpts, $arrSel));
					}
					else
					{
						$arrVals = array_values(array_intersect_key($arrOpts, $arrSel));
					}
				}
				$varValue = (is_array($arrVals) && count($arrVals) > 0 ? implode('|', $arrVals) : '');
			}
		}

		if ( $arrData['inputType']=='checkbox' && !$arrData['eval']['multiple'])
		{
			if (is_array($arrData['options']))
			{
				$arrVals = ($arrData['eval']['efgStoreValues'] ? array_keys($arrData['options']) : array_values($arrData['options']));
			}
			else
			{
				$arrVals = array("1");
			}

			if (strlen($varValue)) {

				$varValue =  $arrVals[0];
			}
			else
			{
				$varValue = '';
			}
		}


		// Make sure unique fields are unique
		if (strlen($varValue) && $arrData['eval']['unique'])
		{
			$objUnique = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . $this->strField . "=? AND id!=?")
										->execute($varValue, $this->intId);

			if ($objUnique->numRows)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $this->strField)));
			}
		}

		// Call save_callback
		if (is_array($arrData['save_callback']))
		{
			foreach ($arrData['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
			}
		}

		// Save the value if there was no error
		if ((strlen($varValue) || !$arrData['eval']['doNotSaveEmpty']) && ($this->varValue != $varValue || $arrData['eval']['alwaysSave']))
		{
			// If the field is a fallback field, empty all other columns
			if ($arrData['eval']['fallback'] && strlen($varValue))
			{
				$this->Database->execute("UPDATE " . $this->strTable . " SET " . $this->strField . "=''");
			}

			$arrValues = $this->values;
			$arrProcedures = $this->procedure;

			if($blnDetailField)
			{
				// add condition ff_name
				$arrProcedures[] = 'ff_name=?';
				$arrValues[] = $strTargetField;

				foreach($arrProcedures as $kP => $kV)
				{
					if ($kV == 'id=?')
					{
						$arrProcedures[$kP] = 'pid=?';
					}

					if ($kV == 'form=?')
					{
						$arrProcedures[$kP] = 'ff_name=?';
						$arrValues[$kP] = $strTargetField;
					}
				}
			}
			array_unshift($arrValues, $varValue);

			$sqlUpd = "UPDATE " . $strTargetTable . " SET " . $strTargetField . "=? WHERE " . implode(' AND ', $arrProcedures);
			if ($blnDetailField)
			{
				// if record does not exist insert an empty record
				$objExist = $this->Database->prepare("SELECT id FROM tl_formdata_details WHERE pid=? AND ff_name=?")
											->execute(array($this->intId, $strTargetField));

				if ($objExist->numRows == 0)
				{
					$arrSetInsert = array('pid' => $this->intId, 'tstamp' => time(), 'ff_id' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strTargetField]['ff_id'], 'ff_type' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strTargetField]['inputType'], 'ff_label' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strTargetField]['label'][0] , 'ff_name' => $strTargetField );
					$objInsertStmt = $this->Database->prepare("INSERT INTO " . $strTargetTable . " %s")
										->set($arrSetInsert)
										->execute();
				}

				$sqlUpd = "UPDATE " . $strTargetTable . " SET value=? WHERE " . implode(' AND ', $arrProcedures);
			}

			$objUpdateStmt = $this->Database->prepare($sqlUpd)
											->execute($arrValues);

			if ($objUpdateStmt->affectedRows)
			{
				if ($varValue != $this->varValue)
				{
					if (!$arrData['eval']['submitOnChange'])
					{
						$this->blnCreateNewVersion = true;
					}
				}

				$this->varValue = deserialize($varValue);
			}
		}
	}


	/**
	 * Create a new version of the current record
	 * @param mixed
	 * @throws Exception
	 */
	protected function createNewVersion($strTable, $intId)
	{
		if (!$GLOBALS['TL_DCA'][$strTable]['config']['enableVersioning'])
		{
			return;
		}

		// Delete old versions from the database
		$this->Database->prepare("DELETE FROM tl_version WHERE tstamp<?")
					   ->execute((time() - $GLOBALS['TL_CONFIG']['versionPeriod']));

		// Get new record
		$objRecord = $this->Database->prepare("SELECT * FROM " . $strTable . " WHERE id=?")
									->limit(1)
									->execute($intId);

		if ($objRecord->numRows < 1 || $objRecord->tstamp < 1)
		{
			return;
		}

		$intVersion = 1;
		$this->import('BackendUser', 'User');

		$objVersion = $this->Database->prepare("SELECT MAX(version) AS `version` FROM tl_version WHERE pid=? AND fromTable=?")
									 ->execute($intId, $strTable);

		if (!is_null($objVersion->version))
		{
			$intVersion = $objVersion->version + 1;
		}

		$this->Database->prepare("UPDATE tl_version SET active='' WHERE pid=? AND fromTable=?")
					   ->execute($intId, $strTable);

		$this->Database->prepare("INSERT INTO tl_version (pid, tstamp, version, fromTable, username, active, data) VALUES (?, ?, ?, ?, ?, 1, ?)")
					   ->execute($intId, time(), $intVersion, $strTable, $this->User->username, serialize($objRecord->row()));
	}


	/**
	 * Return the name of the current palette
	 * @return string
	 */
	public function getPalette()
	{
		$palette = 'default';
		$strPalette = $GLOBALS['TL_DCA'][$this->strTable]['palettes'][$palette];

		// Check whether there are selector fields
		if (count($GLOBALS['TL_DCA'][$this->strTable]['palettes']['__selector__']))
		{
			$sValues = array();
			$subpalettes = array();

			$objFields = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
										->limit(1)
										->execute($this->intId);

			// Get selector values from DB
			if ($objFields->numRows > 0)
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['palettes']['__selector__'] as $name)
				{
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['eval']['multiple'])
					{
						$trigger = $objFields->$name;

						// Overwrite trigger if the page is not reloaded
						if ($this->Input->post('FORM_SUBMIT') == $this->strTable)
						{
							$key = ($this->Input->get('act') == 'editAll') ? $name.'_'.$this->intId : $name;

							if (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['eval']['submitOnChange'])
							{
								$trigger = $this->Input->post($key);
							}
						}

						if (strlen($trigger))
						{
							$sValues[] = $name;

							// Look for a subpalette
							if (strlen($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$name]))
							{
								$subpalettes[$name] = $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$name];
							}
						}
					}

					elseif ($objFields->$name)
					{
						$sValues[] = $objFields->$name;
					}
				}
			}

			// Build possible palette names from the selector values
			if (!count($sValues))
			{
				$names = array('default');
			}

			elseif (count($sValues) > 1)
			{
				$names = $this->combiner($sValues);
			}

			else
			{
				$names = array($sValues[0]);
			}

			// Get an existing palette
			foreach ($names as $paletteName)
			{
				if (strlen($GLOBALS['TL_DCA'][$this->strTable]['palettes'][$paletteName]))
				{
					$palette = $paletteName;
					$strPalette = $GLOBALS['TL_DCA'][$this->strTable]['palettes'][$paletteName];

					break;
				}
			}

			// Include subpalettes
			foreach ($subpalettes as $k=>$v)
			{
				$strPalette = preg_replace('/\b'. preg_quote($k, '/').'\b/i', $k.',['.$k.'],'.$v.',[EOF]', $strPalette);
			}
		}

		return $strPalette;
	}


	/**
	 * Delete all incomplete and unrelated records
	 */
	protected function reviseTable()
	{
		$reload = false;
		$ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
		$ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];

		// Delete all new but incomplete records (tstamp=0)
		$objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE tstamp=0");
		if ($objStmt->affectedRows > 0)
		{
			$reload = true;
		}

		// Delete all records of the current table that are not related to the parent table
		if (strlen($ptable))
		{
			$objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $this->strTable . ".pid = " . $ptable . ".id)");
			if ($objStmt->affectedRows > 0)
			{
				$reload = true;
			}
		}

		// Delete all records of the child table that are not related to the current table
		if (count($ctable))
		{
			foreach ($ctable as $v)
			{
				if (strlen($v))
				{
					$objStmt = $this->Database->execute("DELETE FROM " . $v . " WHERE NOT EXISTS (SELECT * FROM " . $this->strTable . " WHERE " . $v . ".pid = " . $this->strTable . ".id)");
					if ($objStmt->affectedRows > 0)
					{
						$reload = true;
					}
				}
			}
		}

		// Reload the page
		if ($reload)
		{
			$this->reload();
		}
	}


	/**
	 * List all records of the current table as tree and return them as HTML string
	 * @return string
	 */
	protected function treeView()
	{
		$table = $this->strTable;

		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6)
		{
			$table = $this->ptable;

			$this->loadLanguageFile($table);
			$this->loadDataContainer($table);
		}

		// Get session data and toggle nodes
		if ($this->Input->get('ptg') == 'all')
		{
			$session = $this->Session->getData();
			$node = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';

			// Expand tree
			if (!is_array($session[$node]) || count($session[$node]) < 1 || current($session[$node]) != 1)
			{
				$session[$node] = array();
				$objNodes = $this->Database->execute("SELECT DISTINCT pid FROM tl_page WHERE pid>0");

				while ($objNodes->next())
				{
					$session[$node][$objNodes->pid] = 1;
				}
			}

			// Collapse tree
			else
			{
				$session[$node] = array();
			}

			$this->Session->setData($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)ptg=[^& ]*/i', '', $this->Environment->request));
		}

		// Return if a mandatory field (id, pid, sorting) is missing
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && (!$this->Database->fieldExists('id', $table) || !$this->Database->fieldExists('pid', $table) || !$this->Database->fieldExists('sorting', $table)))
		{
			return '
<p class="tl_empty">strTable "'.$table.'" can not be shown as tree!</p>';
		}

		// Return if there is no parent table
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6 && !strlen($this->ptable))
		{
			return '
<p class="tl_empty">strTable "'.$table.'" can not be shown as extended tree!</p>';
		}

		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');

		// Check clipboard
		if (is_array($arrClipboard) && is_array($arrClipboard[$this->strTable]) && count($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$label = $GLOBALS['TL_DCA'][$table]['config']['label'];
		$icon = strlen($GLOBALS['TL_DCA'][$table]['list']['sorting']['icon']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['icon'] : 'pagemounts.gif';
		$label = $this->generateImage($icon).' <label>'.$label.'</label>';

		// Begin buttons container
		$return = '
<div id="tl_buttons">'.(($this->Input->get('act') == 'select') ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' : '') . (($this->Input->get('act') != 'select' && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed']) ? '
<a href="'.$this->addToUrl('act=paste&amp;mode=create').'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . (($this->Input->get('act') != 'select') ? $this->generateGlobalButtons() . ($blnClipboard ? ' &nbsp; :: &nbsp; <a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a>' : '') : '') . '
</div>';

		$tree = '';
		$blnHasSorting = $this->Database->fieldExists('sorting', $table);

		// Call a recursive function that builds the tree
		for ($i=0; $i<count($this->root); $i++)
		{
			$tree .= $this->generateTree($table, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), $blnHasSorting, -20, ($blnClipboard ? $arrClipboard : false));
		}

		// Return if there are no records
		if (!strlen($tree) && $this->Input->get('act') != 'paste')
		{
			return $return . '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select" />' : '').'

<div class="tl_listing_container" id="tl_listing">'.(($this->Input->get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox" />
</div>' : '').'

<ul class="tl_listing">
  <li class="tl_folder_top" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left">'.$label.'</div> <div class="tl_right">';

		$_buttons = '&nbsp;';

		// Show paste button only if there are no root records specified
		if ($this->Input->get('act') != 'select' && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $blnClipboard && !count($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false)
		{
			// Call paste_button_callback (&$dc, $row, $table, $cr, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons = $this->$strClass->$strMethod($this, array('id'=>0), $table, false, $arrClipboard);
			}
			else
			{
				$imagePasteInto = $this->generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0], 'class="blink"');
				$_buttons = '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid=0&amp;id='.$arrClipboard['id']).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
			}
		}

		// End table
		$return .= $_buttons . '</div><div style="clear:both;"></div></li>'.$tree.'
</ul>

</div>';

		// Close form
		if ($this->Input->get('act') == 'select')
		{
			$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">
  <input type="submit" name="delete" id="delete" class="tl_submit" alt="delete selected records" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\');" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="edit" id="edit" class="tl_submit" alt="edit selected records" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'" />' : '') . '
</div>

</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * Generate a particular subpart of the tree and return it as HTML string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function ajaxTreeView($id, $level)
	{
		if (!$this->Input->post('isAjax'))
		{
			return '';
		}

		$return = '';
		$table = $this->strTable;
		$blnPtable = false;

		// Load parent table
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6)
		{
			$table = $this->ptable;

			$this->loadLanguageFile($table);
			$this->loadDataContainer($table);

			$blnPtable = true;
		}

		$blnProtected = false;

		// Check protected pages
		if ($table == 'tl_page')
		{
			$objParent = $this->getPageDetails($id);
			$blnProtected = $objParent->protected ? true : false;
		}

		$margin = ($level * 20);
		$hasSorting = $this->Database->fieldExists('sorting', $table);
		$arrIds = array();

		// Get records
		$objRows = $this->Database->prepare("SELECT id FROM " . $table . " WHERE pid=?" . ($hasSorting ? " ORDER BY sorting" : ""))
							 	  ->execute($id);

		while ($objRows->next())
		{
			$arrIds[] = $objRows->id;
		}

		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');

		// Check clipboard
		if (is_array($arrClipboard) && is_array($arrClipboard[$this->strTable]) && count($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		for ($i=0; $i<count($arrIds); $i++)
		{
			$return .= '  ' . trim($this->generateTree($table, $arrIds[$i], array('p'=>$arrIds[($i-1)], 'n'=>$arrIds[($i+1)]), $hasSorting, $margin, ($blnClipboard ? $arrClipboard : false), ($id == $arrClipboard ['id'] || (!$blnPtable && in_array($id, $this->getChildRecords($arrClipboard['id'], $table, true)))), $blnProtected));
		}

		return $return;
	}


	/**
	 * Recursively generate the tree and return it as HTML string
	 * @param string
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param boolean
	 * @return string
	 */
	protected function generateTree($table, $id, $arrPrevNext, $blnHasSorting, $intMargin=0, $arrClipboard=false, $blnCircularReference=false, $protectedPage=false)
	{
		static $session;

		$session = $this->Session->getData();
		$node = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';

		// Toggle nodes
		if ($this->Input->get('ptg'))
		{
			$session[$node][$this->Input->get('ptg')] = (isset($session[$node][$this->Input->get('ptg')]) && $session[$node][$this->Input->get('ptg')] == 1) ? 0 : 1;
			$this->Session->setData($session);

			$this->redirect(preg_replace('/(&(amp;)?|\?)ptg=[^& ]*/i', '', $this->Environment->request));
		}

		$objRow = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
								 ->limit(1)
								 ->execute($id);

		// Return if there is no result
		if ($objRow->numRows < 1)
		{
			$this->Session->setData($session);
			return '';
		}

		$return = '';
		$intSpacing = 20;

		// Add the ID to the list of current IDs
		if ($this->strTable == $table)
		{
			$this->current[] = $objRow->id;
		}

		// Check whether there are child records
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 || $this->strTable != $table)
		{
			$objChilds = $this->Database->prepare("SELECT id FROM " . $table . " WHERE pid=?" . ($blnHasSorting ? " ORDER BY sorting" : ''))
										->execute($id);

			if ($objChilds->numRows)
			{
				$childs = $objChilds->fetchEach('id');
			}
		}

		// Check whether the page is protected
		$objRow->protected = ($table == 'tl_page') ? ($objRow->protected || $protectedPage) : false;
		$session[$node][$id] = (is_int($session[$node][$id])) ? $session[$node][$id] : 0;

		$return .= "\n  " . '<li class="'.((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $objRow->type == 'root') || $table != $this->strTable) ? 'tl_folder' : 'tl_file').'" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

		// Calculate label and add a toggle button
		$args = array();
		$folderAttribute = 'style="margin-left:20px;"';
		$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];
		$level = ($intMargin / $intSpacing + 1);

		if (count($childs))
		{
			$folderAttribute = '';
			$img = ($session[$node][$id] == 1) ? 'folMinus.gif' : 'folPlus.gif';
			$alt = ($session[$node][$id] == 1) ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
			$return .= '<a href="'.$this->addToUrl('ptg='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); return AjaxRequest.toggleStructure(this, \''.$node.'_'.$id.'\', '.$level.', '.$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'].');">'.$this->generateImage($img, specialchars($alt), 'style="margin-right:2px;"').'</a>';
		}

		foreach ($showFields as $k=>$v)
		{
			if (strpos($v, ':') !== false)
			{
				list($strKey, $strTable) = explode(':', $v);
				list($strTable, $strField) = explode('.', $strTable);

				$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
										 ->limit(1)
										 ->execute($objRow->$strKey);

				$args[$k] = $objRef->numRows ? $objRef->$strField : '';
			}
			elseif (in_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
			{
				$args[$k] = strlen($objRow->$v) ? $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objRow->$v) : '';
			}
			elseif ($GLOBALS['TL_DCA'][$table]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['multiple'])
			{
				$args[$k] = strlen($objRow->$v) ? (strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : $v) : '';
			}
			else
			{
				$args[$k] = strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v] : $objRow->$v;
			}
		}

		$label = vsprintf(((strlen($GLOBALS['TL_DCA'][$table]['list']['label']['format'])) ? $GLOBALS['TL_DCA'][$table]['list']['label']['format'] : '%s'), $args);

		// Shorten label it if it is too long
		if ($GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
		{
			$this->import('String');
			$label = trim($this->String->substrHtml($label, $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'])) . ' …';
		}

		$label = preg_replace('/\(\) ?|\[\] ?|\{\} ?|<> ?/i', '', $label);

		// Call label_callback ($row, $label, $this)
		if (is_array($GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']))
		{
			$strClass = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][0];
			$strMethod = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][1];

			$this->import($strClass);
			$return .= $this->$strClass->$strMethod($objRow->row(), $label, $folderAttribute, $this);
		}
		else
		{
			$return .= $this->generateImage('iconPLAIN.gif', '', $folderAttribute) . ' ' . $label;
		}

		$return .= '</div> <div class="tl_right">';
		$previous = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['pp'] : $arrPrevNext['p'];
		$next = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['nn'] : $arrPrevNext['n'];
		$_buttons = '';

		// Regular buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
		if ($this->strTable == $table)
		{
			$_buttons .= ($this->Input->get('act') == 'select') ? '<input type="checkbox" name="IDS[]" id="ids_'.$id.'" class="tl_tree_checkbox" value="'.$id.'" />' : $this->generateButtons($objRow->row(), $table, $this->root, $blnCircularReference, $childs, $previous, $next);
		}

		// Paste buttons
		if ($arrClipboard !== false && $this->Input->get('act') != 'select')
		{
			// Call paste_button_callback(&$dc, $row, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons .= $this->$strClass->$strMethod($this, $objRow->row(), $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next);
			}

			else
			{
				$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id), 'class="blink"');
				$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id), 'class="blink"');

				// Regular tree (on cut: disable buttons of the page all its childs to avoid circular references)
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5)
				{
					$_buttons .= ($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || (count($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && in_array($id, $this->root))) ? $this->generateImage('pasteafter_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
					$_buttons .= ($arrClipboard['mode'] == 'paste' && ($blnCircularReference || $arrClipboard['id'] == $id)) ? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
				}

				// Extended tree
				else
				{
					$_buttons .= ($this->strTable == $table) ? '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ' : '';
					$_buttons .= ($this->strTable != $table) ? '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ' : '';
				}
			}
		}

		$return .= (strlen($_buttons) ? $_buttons : '&nbsp;') . '</div><div style="clear:both;"></div></li>';

		// Add records of the table itself
		if ($table != $this->strTable)
		{
			$objChilds = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE pid=?" . ($blnHasSorting ? " ORDER BY sorting" : ''))
							 			->execute($id);

			if ($objChilds->numRows)
			{
				$ids = $objChilds->fetchEach('id');

				for ($j=0; $j<count($ids); $j++)
				{
					$return .= $this->generateTree($this->strTable, $ids[$j], array('pp'=>$ids[($j-1)], 'nn'=>$ids[($j+1)]), $blnHasSorting, ($intMargin + $intSpacing + 20), $arrClipboard, false, ($j<(count($ids)-1) || count($childs)));
				}
			}
		}

		// Begin new submenu
		if (count($childs) && $session[$node][$id] == 1)
		{
			$return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';
		}

		// Add records of the parent table
		if ($session[$node][$id] == 1)
		{
			if (is_array($childs))
			{
				for ($k=0; $k<count($childs); $k++)
				{
					$return .= $this->generateTree($table, $childs[$k], array('p'=>$childs[($k-1)], 'n'=>$childs[($k+1)]), $blnHasSorting, ($intMargin + $intSpacing), $arrClipboard, ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $childs[$k] == $arrClipboard['id']) || $blnCircularReference) ? true : false), ($objRow->protected || $protectedPage));
				}
			}
		}

		// Close submenu
		if (count($childs) && $session[$node][$id] == 1)
		{
			$return .= '</ul></li>';
		}

		$this->Session->setData($session);
		return $return;
	}


	/**
 	 * Show header of the parent table and list all records of the current table
	 * @return string
	 */
	protected function parentView()
	{
		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$blnHasSorting = $this->Database->fieldExists('sorting', $table);

		// Check clipboard
		if (is_array($arrClipboard) && is_array($arrClipboard[$table]) && count($arrClipboard[$table]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$table];
		}

		// Load language file and data container array of the parent table
		$this->loadLanguageFile($this->ptable);
		$this->loadDataContainer($this->ptable);

		$return = '
<div id="tl_buttons">
<a href="'.(($this->Input->get('act') == 'select') ? $this->getReferer(true) : $this->Environment->script.'?do='.$this->Input->get('do')).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' . (($this->Input->get('act') != 'select') ? ' &#160; :: &#160; ' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<a href="'.$this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;pid='.$this->intId)).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . $this->generateGlobalButtons(). ($blnClipboard ? ' &nbsp; :: &nbsp; <a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a>' : '') : '') . '
</div>';

		// Get all details of the parent record
		$objParent = $this->Database->prepare("SELECT * FROM " . $this->ptable . " WHERE id=?")
									->limit(1)
									->execute(CURRENT_ID);

		if ($objParent->numRows < 1)
		{
			return $return;
		}

		$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select" />' : '').'

<div class="tl_listing_container">

<div class="tl_header" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);">';

		// List all records of the child table
		if (!$this->Input->get('act') || $this->Input->get('act') == 'paste' || $this->Input->get('act') == 'select')
		{

			// Header
			$imagePasteNew = $this->generateImage('new.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]);
			$imagePasteAfter = $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0], 'class="blink"');
			$imageEditHeader = $this->generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->strTable]['editheader'][0]);

			$return .= '
<div style="text-align:right;">'.(($this->Input->get('act') == 'select') ? '
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox" />' : '
<a href="'.preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->ptable) ? '&amp;table='.$this->ptable : ''), $this->addToUrl('act=edit')).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['editheader'][1]).'">'.$imageEditHeader.'</a>' . (($blnHasSorting && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed']) ? ' <a href="'.$this->addToUrl('act=create&amp;mode=2&amp;pid='.$objParent->id.'&amp;id='.$this->intId).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0]).'">'.$imagePasteNew.'</a>' : '') . ($blnClipboard ? ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$objParent->id.'&amp;id='.$arrClipboard['id']).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a>' : '')) . '
</div>';

			// Format header fields
			$add = array();
			$headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

			foreach ($headerFields as $v)
			{
				$_v = deserialize($objParent->$v);

				if (is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['multiple'])
				{
					$_v = strlen($_v) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
				}
				elseif ($_v && $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'date')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $_v);
				}
				elseif ($_v && $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'datim')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
				}
				elseif ($v == 'tstamp')
				{
					$objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM " . $this->strTable . " WHERE pid=?")
												   ->execute($objParent->id);

					if (!$objMaxTstamp->tstamp)
					{
						$objMaxTstamp->tstamp = $objParent->tstamp;
					}

					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objMaxTstamp->tstamp);
				}
				elseif (strlen($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']))
				{
					$arrForeignKey = trimsplit('\.', $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']);

					$objLabel = $this->Database->prepare("SELECT " . $arrForeignKey[1] . " FROM " . $arrForeignKey[0] . " WHERE id=?")
											   ->limit(1)
											   ->execute($_v);

					if ($objLabel->numRows)
					{
						$_v = $objLabel->$arrForeignKey[1];
					}
				}
				elseif (strlen($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v]))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v];
				}

				// Add sorting field
				if (strlen($_v))
				{
					$key = strlen($GLOBALS['TL_LANG'][$this->ptable][$v][0]) ? $GLOBALS['TL_LANG'][$this->ptable][$v][0]  : $v;
					$add[$key] = $_v;
				}
			}

			// Output header data
			$return .= '

<table cellpadding="0" cellspacing="0" class="tl_header_table" summary="Table lists all details of the header record">';

			foreach ($add as $k=>$v)
			{
				if (is_array($v))
				{
					$v = $v[0];
				}

				$return .= '
  <tr>
    <td><span class="tl_label">'.$k.':</span> </td>
    <td>'.$v.'</td>
  </tr>';
			}

			$return .= '
</table>
</div>';

			// Add all records of the current table
			$query = "SELECT * FROM " . $this->strTable;

			if (count($this->procedure))
			{
				$query .= " WHERE " . implode(' AND ', $this->procedure);
			}

			if (is_array($this->root))
			{
				$query .= (count($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', $this->root) . ")";
			}

			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']))
			{
				$query .= " ORDER BY " . implode(', ', $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']);
			}

			$objOrderByStmt = $this->Database->prepare($query);

			if (strlen($this->limit))
			{
				$arrLimit = explode(',', $this->limit);
				$objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
			}

			$objOrderBy = $objOrderByStmt->execute($this->values);

			if ($objOrderBy->numRows < 1)
			{
				return $return . '
<p class="tl_empty_parent_view">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>

</div>';
			}

			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][1];

				$this->import($strClass);
				$row = $objOrderBy->fetchAllAssoc();

				// Make items sortable
				if ($blnHasSorting)
				{
					$return .= '

<ul id="ul_' . CURRENT_ID . '" class="sortable">';
				}

				for ($i=0; $i<count($row); $i++)
				{
					$this->current[] = $row[$i]['id'];
					$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id']), 'class="blink"');
					$imagePasteNew = $this->generateImage('new.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id']));

					// Make items sortable
					if ($blnHasSorting)
					{
						$return .= '
<li id="li_' . $row[$i]['id'] . '">';
					}

					$return .= '

<div class="tl_content" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);">
<div style="text-align:right;">';

					// Edit multiple
					if ($this->Input->get('act') == 'select')
					{
						$return .= '<input type="checkbox" name="IDS[]" id="ids_'.$row[$i]['id'].'" class="tl_tree_checkbox" value="'.$row[$i]['id'].'" />';
					}

					// Regular buttons
					else
					{
						$return .= $this->generateButtons($row[$i], $this->strTable, $this->root, false, null, $row[($i-1)]['id'], $row[($i+1)]['id']);

						// Sortable table
						if ($blnHasSorting)
						{
							// Create new button
							if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])
							{
								$return .= ' <a href="'.$this->addToUrl('act=create&amp;mode=1&amp;pid='.$row[$i]['id'].'&amp;id='.$objParent->id).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id'])).'">'.$imagePasteNew.'</a>';
							}

							// Prevent circular references
							if ($blnClipboard && $arrClipboard['mode'] == 'cut' && $row[$i]['id'] == $arrClipboard['id'])
							{
								$return .= ' ' . $this->generateImage('pasteafter_.gif', '', 'class="blink"');
							}

							// Paste buttons
							elseif ($blnClipboard)
							{
								$return .= ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row[$i]['id'].'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a>';
							}
						}
					}

					$return .= '
</div>'.$this->$strClass->$strMethod($row[$i]).'</div>';

					// Make items sortable
					if ($blnHasSorting)
					{
						$return .= '

</li>';
					}
				}
			}
		}

		// Make items sortable
		if ($blnHasSorting)
		{
			$return .= '
</ul>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
Backend.makeParentViewSortable("ul_' . CURRENT_ID . '");
//--><!]]>
</script>';
		}

		$return .= '

</div>';

		// Close form
		if ($this->Input->get('act') == 'select')
		{
			$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">
  <input type="submit" name="delete" id="delete" class="tl_submit" alt="delete selected records" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\');" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="edit" id="edit" class="tl_submit" alt="edit selected records" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'" />' : '') . '
</div>

</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * List all records of the current table and return them as HTML string
	 * @return string
	 */
	protected function listView()
	{

		$return = '';
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$table_alias = ($table == 'tl_formdata' ? ' f' : '');

		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		if (is_array($this->orderBy) && strlen($this->orderBy[0]))
		{
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		if ($this->Input->get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Database->fieldExists('pid', $this->strTable))
		{
			$this->procedure[] = 'pid=?';
			$this->values[] = $this->Input->get('id');
		}

		$query = "SELECT * " .(count($this->arrSqlDetails) > 0 ? ', '.implode(',' , $this->arrSqlDetails) : '') ." FROM " . $this->strTable . $table_alias;

		$sqlWhere = '';

		if (count($this->procedure))
		{
			$arrProcedure = $this->procedure;

			foreach ($arrProcedure as $kProc => $vProc)
			{
				//$strProcField = substr($vProc, 0, strpos($vProc, '='));
				$arrParts = preg_split('/[\s=><\!]/si', $vProc);
				$strProcField = $arrParts[0];
				if ( in_array($strProcField, $this->arrDetailFields) )
				{
					$arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='" . $strProcField . "' AND pid=f.id)=?";
				}
			}
			$sqlWhere = " WHERE " . implode(' AND ', $arrProcedure);
		}

		if ( $sqlWhere != '')
		{
			$query .= $sqlWhere;
		}

		if (is_array($orderBy) && strlen($orderBy[0]))
		{
			foreach ($orderBy as $o => $strVal)
			{
				$arrOrderField = explode(' ', $strVal);
				$strOrderField = $arrOrderField[0];
				unset($arrOrderField);
				if (!in_array($strOrderField, $this->arrBaseFields))
				{
					$orderBy[$o] = "(SELECT value FROM tl_formdata_details WHERE ff_name='" . $strOrderField . "' AND pid=f.id)";
				}
			}

			$query .= " ORDER BY " . implode(', ', $orderBy);
		}
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
		{
			$query .= " DESC";
		}

		$objRowStmt = $this->Database->prepare($query);

		if (strlen($this->limit))
		{
			$arrLimit = explode(',', $this->limit);
			$objRowStmt->limit($arrLimit[1], $arrLimit[0]);
		}

		$objRow = $objRowStmt->execute($this->values);
		$this->bid = strlen($return) ? $this->bid : 'tl_buttons';

		// Display buttons
		if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] || count($GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations']) && $objRow->numRows)
		{
			$return .= '
<div id="'.$this->bid.'">'.(($this->Input->get('act') == 'select' || $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' : '') . (($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Input->get('act') != 'select') ? ' &nbsp; :: &nbsp;' : '') . (($this->Input->get('act') != 'select') ? '
'.(!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '<a href="'.(strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? $this->addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->intId) : $this->addToUrl('act=create')).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . $this->generateGlobalButtons() : '') . '
</div>';
		}

		// List records
		if ($objRow->numRows)
		{
			$result = $objRow->fetchAllAssoc();

			$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select" />' : '').'

<div class="tl_listing_container">'.(($this->Input->get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox" />
</div>' : '').'

<table cellpadding="0" cellspacing="0" class="tl_listing" summary="Table lists records">';

			// Rename each pid to its label and resort the result (sort by parent table)
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3 && $this->Database->fieldExists('pid', $this->strTable))
			{
				$firstOrderBy = 'pid';
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				foreach ($result as $k=>$v)
				{
					$objField = $this->Database->prepare("SELECT " . $showFields[0] . " FROM " . $this->ptable . " WHERE id=?")
											   ->limit(1)
											   ->execute($v['pid']);

					$result[$k]['pid'] = $objField->$showFields[0];
				}

				$aux = array();

				foreach ($result as $row)
				{
					$aux[] = $row['pid'];
				}

				array_multisort($aux, SORT_ASC, $result);
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';

			foreach ($result as $row)
			{

				$arrRowFormatted = array();

				$args = array();
				$this->current[] = $row['id'];
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				// Label
				foreach ($showFields as $k=>$v)
				{

					if (in_array($v, $this->arrDetailFields) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'radio' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupRadio' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'select' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupSelect' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupCheckbox') )
					{
						$row[$v] = str_replace('|', ', ', $row[$v]);
					}

					if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
					{

						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date')
						{
							$args[$k] = strlen($row[$v]) ? $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '';
						}
						elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time')
						{
							$args[$k] = strlen($row[$v]) ? $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '';
						}
						else
						{
							$args[$k] = strlen($row[$v]) ? $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '';
						}
						$arrRowFormatted[$v] = $args[$k];
					}

					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'])
					{
						$args[$k] = strlen($row[$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : '-';
						$arrRowFormatted[$v] = $args[$k];
					}

					elseif (in_array($v, $this->arrBaseFields) && in_array($v , $this->arrOwnerFields))
					{
						if ($v == 'fd_member')
						{
							$args[$k] = $this->arrMembers[$row[$v]];
							$arrRowFormatted[$v] = $args[$k];
						}
						if ($v == 'fd_user')
						{
							$args[$k] = $this->arrUsers[$row[$v]];
							$arrRowFormatted[$v] = $args[$k];
						}
					}

					else
					{
						$row_v = deserialize($row[$v]);

						if (is_array($row_v))
						{
							$args_k = array();

							foreach ($row_v as $option)
							{
								$args_k[] = strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] : $option;
							}

							$args[$k] = implode(', ', $args_k);
							$arrRowFormatted[$v] = $args[$k];

						}
						elseif (is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]))
						{
							$args[$k] = is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
							$arrRowFormatted[$v] = $args[$k];
						}
						else
						{
							// check multiline value
							if (!is_bool(strpos($row[$v], "\n")))
							{
								$strVal = $row[$v];
								$strVal = preg_replace('/(<\/|<)(h\d|p|div|ul|ol|li)(>)(\n)/si', "\\1\\2\\3", $strVal);
								$strVal = nl2br($strVal);
								$strVal = preg_replace('/(<\/)(h\d|p|div|ul|ol|li)(>)/si', "\\1\\2\\3\n", $strVal);
								$row[$v] = $strVal;
								unset($strVal);
							}
							$args[$k] = $row[$v];
							$arrRowFormatted[$v] = $args[$k];
						}
					}

				} // foreach ($showFields as $k=>$v)

				// Shorten label it if it is too long
				$label = vsprintf((strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format']) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] : '%s'), $args);

				if (strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters']) && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < strlen($label))
				{
					$this->import('String');
					$label = trim($this->String->substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
				}

				// Remove empty brackets (), [], {}, <> and empty tags from label
				//$label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
				//$label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

				// Build sorting groups
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0)
				{
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
					$sortingMode = (count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag']) && !strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'])) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];

					if($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['eval']['multiple'])
					{
						$remoteNew = strlen($current) ? ucfirst($GLOBALS['TL_LANG']['MSC']['yes']) : ucfirst($GLOBALS['TL_LANG']['MSC']['no']);
					}
					elseif (in_array($sortingMode, array(1, 2)))
					{
						$remoteNew = strlen($current) ? ucfirst(utf8_substr($current , 0, 1)) : '-';
					}
					elseif (in_array($sortingMode, array(3, 4)))
					{
						$remoteNew = strlen($current) ? ucfirst(utf8_substr($current , 0, 2)) : '-';
					}
					elseif (in_array($sortingMode, array(5, 6)))
					{
						$remoteNew = date($GLOBALS['TL_CONFIG']['dateFormat'], $current);
					}
					elseif (in_array($sortingMode, array(7, 8)))
					{
						$remoteNew = date('Y-m', $current);
						$intMonth = (date('m', $current) - 1);

						if (strlen($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
						{
							$remoteNew = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $current);
						}
					}
					elseif (in_array($sortingMode, array(9, 10)))
					{
						$remoteNew = date('Y', $current);
					}
					else
					{
						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['eval']['multiple'])
						{
							$remoteNew = strlen($current) ? $firstOrderBy : '';
						}
						elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference']))
						{
							$remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$current];
						}
						elseif (array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options']))
						{
							$remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options'][$current];
						}
						else
						{
							$remoteNew = $current;
						}

						if (!strlen($remoteNew))
						{
							$remoteNew = '-';
						}
					}

					// Add group header
					if ($remoteNew != $remoteCur || $remoteCur === false)
					{
						if (array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options']))
						{
							$group = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options'][$remoteNew];
						}
						else
						{
							$group = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew]);
						}

						if (!strlen($group))
						{
							$group = is_array($GLOBALS['TL_LANG'][$this->strTable][$remoteNew] ? $GLOBALS['TL_LANG'][$this->strTable][$remoteNew][0] : $GLOBALS['TL_LANG'][$this->strTable][$remoteNew]);
						}

						if (!strlen($group))
						{
							$group = $remoteNew;
						}

						$remoteCur = $remoteNew;

						$return .= '
  <tr onmouseover="Theme.hoverRow(this, 1);" onmouseout="Theme.hoverRow(this, 0);">
    <td colspan="2" class="'.$groupclass.'" style="padding-left:2px;">'.$group.'</td>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr onmouseover="Theme.hoverRow(this, 1);" onmouseout="Theme.hoverRow(this, 0);">
    <td class="tl_file_list" style="padding-left:2px;">';

				// Call label callback ($row, $label, $this)
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

					$this->import($strClass);
					$return .= $this->$strClass->$strMethod($arrRowFormatted, $label, $this);
				}
				else
				{
					$return .= $label;
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
				$return .= '</td>'.(($this->Input->get('act') == 'select') ? '
    <td class="tl_file_list" style="text-align:right; padding-right:1px;"><input type="checkbox" name="IDS[]" id="ids_'.$row['id'].'" class="tl_tree_checkbox" value="'.$row['id'].'" /></td>' : '
    <td class="tl_file_list" style="text-align:right; padding-right:1px;">'.$this->generateButtons($row, $this->strTable, $this->root).'</td>') . '
  </tr>';
			} // foreach ($result as $row)

			// Close table
			$return .= '
</table>

</div>';

			// Close form
			if ($this->Input->get('act') == 'select')
			{
				$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">
  <input type="submit" name="delete" id="delete" class="tl_submit" alt="delete selected records" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\');" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="edit" id="edit" class="tl_submit" alt="edit selected records" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'" />' : '') . '
</div>

</div>
</div>
</form>';
			}
		}

		// No records found
		else
		{
			$session = $this->Session->getData();
			//$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : $this->strTable;
			$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : (strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;

			// Unset old filter entries (in case there is an old timestamp or something like that)
			if (isset($session['filter'][$filter]))
			{
				unset($session['filter'][$filter]);
				$this->Session->setData($session);
				$this->reload();
			}

			// Return "no records found" message
			$return .= '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		return $return;
	}

	/**
	 * Build the sort panel and return it as string
	 * @return string
	 */
	protected function panel()
	{

		$filter = $this->filterMenu();
		$search = $this->searchMenu();
		$limit = $this->limitMenu();
		$sort = $this->sortMenu();

		if (!strlen($filter) && !strlen($search) && !strlen($limit) && !strlen($sort))
		{
			return '';
		}

		if (!strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout']))
		{
			return '';
		}

		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			$this->reload();
		}

		$return = '';
		$panelLayout = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'];
		$arrPanels = trimsplit(';', $panelLayout);
		$intLast = count($arrPanels) - 1;

		for ($i=0; $i<count($arrPanels); $i++)
		{
			$panels = '';
			$submit = '';
			$arrSubPanels = trimsplit(',', $arrPanels[$i]);

			foreach ($arrSubPanels as $strSubPanel)
			{
				if (strlen($$strSubPanel))
				{
					$panels = $$strSubPanel . $panels;
				}
			}

			if ($i == $intLast)
			{
				$submit = '
<div class="tl_submit_panel tl_subpanel">
<input type="image" name="filter" id="filter" src="system/themes/' . $this->getTheme() . '/images/reload.gif" class="tl_img_submit" alt="apply changes" value="apply changes" />
</div>';
			}

			if (strlen($panels))
			{
				$return .= '
<div class="tl_panel">'.$submit.$panels.'
<div class="clear"></div>
</div>';
			}
		}

		$return = '
<form action="'.ampersand($this->Environment->request, true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters" />
' . $return . '
</div>
</form>
';

		return $return;
	}


	/**
	 * Return a search form that allows to search results using regular expressions
	 * @return string
	 */
	protected function searchMenu()
	{
		$searchFields = array();
		$session = $this->Session->getData();

		// Get search fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['search'])
			{
				$searchFields[] = $k;
			}
		}

		// Return if there are no search fields
		if (!count($searchFields))
		{
			return '';
		}

		$strSessionKey = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : (strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;

		// Store search value in the current session
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{

			//$session['search'][$this->strTable]['value'] = '';
			//$session['search'][$this->strTable]['field'] = $this->Input->post('tl_field', true);
			$session['search'][$strSessionKey]['value'] = '';
			$session['search'][$strSessionKey]['field'] = $this->Input->post('tl_field', true);

			// Make sure the regular expression is valid
			if ($this->Input->postRaw('tl_value') != '')
			{
				$sqlSearchField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\'' . $this->Input->post('tl_field', true) .'\' AND pid=f.id)';
				try
				{
					$this->Database->prepare("SELECT * ".(count($this->arrSqlDetails) > 0 ? ','.implode(', ', $this->arrSqlDetails) : '')." FROM " . $this->strTable . " f WHERE " . $sqlSearchField . " REGEXP ?")
								   ->limit(1)
								   ->execute($this->Input->postRaw('tl_value'));

					$session['search'][$strSessionKey]['value'] = $this->Input->postRaw('tl_value');
				}

				catch (Exception $e)
				{
					// Nothing to do here
				}
			}

			$this->Session->setData($session);
		}

		// Set search value from session
		//elseif (strlen($session['search'][$this->strTable]['value']))
		elseif (strlen($session['search'][$strSessionKey]['value']))
		{
			//$this->procedure[] = "CAST(".$session['search'][$this->strTable]['field']." AS CHAR) REGEXP ?";
			//$this->values[] = $session['search'][$this->strTable]['value'];
			$sqlSearchField = $session['search'][$strSessionKey]['field'];
			if (in_array($sqlSearchField, $this->arrDetailFields) )
			{
				$sqlSearchField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\'' . $session['search'][$strSessionKey]['field'] .'\' AND pid=f.id)';
			}
			$this->procedure[] = "CAST(".$sqlSearchField." AS CHAR) REGEXP ?";
			$this->values[] = $session['search'][$strSessionKey]['value'];
		}

		$options_sorter = array();

		foreach ($searchFields as $field)
		{
			//$option_label = strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];
			//$options_sorter[$option_label] = '  <option value="'.specialchars($field).'"'.(($field == $session['search'][$this->strTable]['field']) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
			$option_label = strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];
			$options_sorter[$option_label] = '  <option value="'.specialchars($field).'"'.(($field == $session['search'][$strSessionKey]['field']) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
		}

		// Sort by option values
		uksort($options_sorter, 'strcasecmp');
		//$active = strlen($session['search'][$this->strTable]['value']) ? true : false;
		$active = strlen($session['search'][$strSessionKey]['value']) ? true : false;

		return '
<div class="tl_search tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['search'] . ':</strong>
<select name="tl_field" class="tl_select' . ($active ? ' active' : '') . '">
'.implode("\n", $options_sorter).'
</select>
<span>=</span>
<input type="text" name="tl_value" class="tl_text' . ($active ? ' active' : '') . '" value="'.specialchars($session['search'][$strSessionKey]['value']).'" />
</div>';
	}


	/**
	 * Return a select menu that allows to sort results by a particular field
	 * @return string
	 */
	protected function sortMenu()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] != 2)
		{
			return '';
		}

		$sortingFields = array();

		// Get sorting fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['sorting'])
			{
				$sortingFields[] = $k;
			}
		}

		// Return if there are no sorting fields
		if (!count($sortingFields))
		{
			return '';
		}

		$this->bid = 'tl_buttons_a';
		$session = $this->Session->getData();
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		//$strSessionKey = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : $this->strTable;
		$strSessionKey = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : (strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;


		// Add PID to order fields
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3 && $this->Database->fieldExists('pid', $this->strTable))
		{
			array_unshift($orderBy, 'pid');
		}

		// Set sorting from user input
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			//$session['sorting'][$this->strTable] = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->Input->post('tl_sort')]['flag'], array(2, 4, 6, 8, 10, 12)) ? $this->Input->post('tl_sort').' DESC' : $this->Input->post('tl_sort');
			$session['sorting'][$strSessionKey] = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->Input->post('tl_sort')]['flag'], array(2, 4, 6, 8, 10, 12)) ? $this->Input->post('tl_sort').' DESC' : $this->Input->post('tl_sort');
			$this->Session->setData($session);
		}

		// Overwrite "orderBy" value with session value
		//elseif (strlen($session['sorting'][$this->strTable]))
		elseif (strlen($session['sorting'][$strSessionKey]))
		{
			//$overwrite = preg_quote(preg_replace('/\s+.*$/i', '', $session['sorting'][$this->strTable]), '/');
			$overwrite = preg_quote(preg_replace('/\s+.*$/i', '', $session['sorting'][$strSessionKey]), '/');
			$orderBy = array_diff($orderBy, preg_grep('/^'.$overwrite.'/i', $orderBy));

			//array_unshift($orderBy, $session['sorting'][$this->strTable]);
			array_unshift($orderBy, $session['sorting'][$strSessionKey]);

			$this->firstOrderBy = $overwrite;
			$this->orderBy = $orderBy;
		}

		$options_sorter = array();

		// Sorting fields
		foreach ($sortingFields as $field)
		{
			$options_label = strlen(($lbl = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'])) ? $lbl : $GLOBALS['TL_LANG']['MSC'][$field];
			//$options_sorter[$options_label] = '  <option value="'.specialchars($field).'"'.((!strlen($session['sorting'][$this->strTable]) && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$this->strTable])) ? ' selected="selected"' : '').'>'.$options_label.'</option>';
			$options_sorter[$options_label] = '  <option value="'.specialchars($field).'"'.((!strlen($session['sorting'][$strSessionKey]) && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$strSessionKey])) ? ' selected="selected"' : '').'>'.$options_label.'</option>';
		}

		// Sort by option values
		uksort($options_sorter, 'strcasecmp');

		return '
<div class="tl_sorting tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['sortBy'] . ':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select">
'.implode("\n", $options_sorter).'
</select>
</div>';
	}


	/**
	 * Return a select menu to limit results
	 * @return string
	 */
	protected function limitMenu($blnOptional=false)
	{
		$session = $this->Session->getData();
		//$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : $this->strTable;
		$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : (strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;


		if (is_array($this->procedure))
		{
			$this->procedure = array_unique($this->procedure);
		}
		if (is_array($this->values))
		{
			$this->values = array_unique($this->values);
		}

		// Set limit from user input
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters' || $this->Input->post('FORM_SUBMIT') == 'tl_filters_limit')
		{
			if ($this->Input->post('tl_limit') != 'tl_limit')
			{
				$session['filter'][$filter]['limit'] = $this->Input->post('tl_limit');
			}
			else
			{
				unset($session['filter'][$filter]['limit']);
			}

			$this->Session->setData($session);
			if ($this->Input->post('FORM_SUBMIT') == 'tl_filters_limit')
			{
				$this->reload();
			}
		}

		// Set limit from table configuration
		else
		{
			$this->limit = strlen($session['filter'][$filter]['limit']) ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,'.$GLOBALS['TL_CONFIG']['resultsPerPage'];

			$sqlQuery = '';
			$sqlSelect = '';
			$sqlDetailFields = '';
			$sqlWhere = '';

			if (count($this->procedure))
			{
				$arrProcedure = $this->procedure;
				foreach ($arrProcedure as $kProc => $vProc)
				{
					//$strProcField = substr($vProc, 0, strpos($vProc, '='));
					$arrParts = preg_split('/[\s=><\!]/si', $vProc);
					$strProcField = $arrParts[0];
					if (in_array($strProcField, $this->arrDetailFields) )
					{
						//$sqlDetailFields .= ", (SELECT value FROM tl_formdata_details WHERE ff_name='" . $strProcField . "' AND pid=f.id) AS `" . $strProcField . "`";
						$sqlDetailFields .= ", (SELECT value FROM tl_formdata_details WHERE ff_name='" . $strProcField . "' AND pid=f.id) AS `" . $strProcField ."`";
						$arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='" . $strProcField . "' AND pid=f.id)=?";
					}

				}
				$sqlWhere = " WHERE " . implode(' AND ', $arrProcedure);
			}
			$sqlSelect = "SELECT COUNT(*) AS total ". $sqlDetailFields ." FROM " . $this->strTable . " f";
			$sqlQuery = $sqlSelect . $sqlWhere;

			$objTotal = $this->Database->prepare($sqlQuery)
									   ->execute($this->values);
			$total = $objTotal->total;

			// Build options
			if ($total > 0)
			{
				$options = '';
				$options_total = ceil($total / $GLOBALS['TL_CONFIG']['resultsPerPage']);

				// Reset limit if other parameters have decreased the number of results
				if (!is_null($this->limit) && (!strlen($this->limit) || preg_replace('/,.*$/i', '', $this->limit) > $total))
				{
					$this->limit = '0,'.$GLOBALS['TL_CONFIG']['resultsPerPage'];
				}

				// Build options
				for ($i=0; $i<$options_total; $i++)
				{
					$this_limit = ($i*$GLOBALS['TL_CONFIG']['resultsPerPage']).','.$GLOBALS['TL_CONFIG']['resultsPerPage'];
					$upper_limit = ($i*$GLOBALS['TL_CONFIG']['resultsPerPage']+$GLOBALS['TL_CONFIG']['resultsPerPage']);

					if ($upper_limit > $total)
					{
						$upper_limit = $total;
					}

					$options .= '
  <option value="'.$this_limit.'"' . $this->optionSelected($this->limit, $this_limit) . '>'.($i*$GLOBALS['TL_CONFIG']['resultsPerPage']+1).' - '.$upper_limit.'</option>';
				}

				$options .= '
  <option value="all"' . $this->optionSelected($this->limit, null) . '>'.$GLOBALS['TL_LANG']['MSC']['filterAll'].'</option>';
			}

			// Return if there is only one page
			if ($blnOptional && ($total < 1 || $options_total < 2))
			{
				return '';
			}


			$fields .= '
<select name="tl_limit" class="tl_select' . (($session['filter'][$filter]['limit'] != 'all' && $total > $GLOBALS['TL_CONFIG']['resultsPerPage']) ? ' active' : '') . '">
  <option value="tl_limit">'.$GLOBALS['TL_LANG']['MSC']['filterRecords'].'</option>'.$options.'
</select> ';
		}

		return '

<div class="tl_limit tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['showOnly'] . ':</strong>'.$fields.'
</div>';
	}



	/**
	 * Generate the filter panel and return it as HTML string
	 * @return string
	 */
	protected function filterMenu()
	{
		$fields = '';
		$this->bid = 'tl_buttons_a';
		$sortingFields = array();
		$session = $this->Session->getData();
		//$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : $this->strTable;
		$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable.'_'.CURRENT_ID : (strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;

		// Get sorting fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['filter'] )
			{
				$sortingFields[] = $k;
			}
		}

		// Return if there are no sorting fields
		if (!count($sortingFields))
		{
			return '';
		}

		// Set filter from user input
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			foreach ($sortingFields as $field)
			{
				if ($this->Input->post($field, true) != 'tl_'.$field)
				{
					$session['filter'][$filter][$field] = $this->Input->post($field, true);
				}
				else
				{
					unset($session['filter'][$filter][$field]);
				}
			}

			// add filter if called by special form dependent BE nav item
			if ($this->strFormFilterKey != '' && $this->strFormFilterValue != '')
			{
				$session['filter'][$filter][$this->strFormFilterKey] = $this->strFormFilterValue;
			}

			$this->Session->setData($session);
			//$this->reload();
		}

		// Set filter from table configuration
		else
		{
			foreach ($sortingFields as $field)
			{
				if (isset($session['filter'][$filter][$field]))
				{
					// Sort by day
					if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
					{
						if ($session['filter'][$filter][$field] == '')
						{
							$this->procedure[] = $field . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $field . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->dayBegin;
							$this->values[] = $objDate->dayEnd;
						}
					}

					// Sort by month
					elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
					{
						if ($session['filter'][$filter][$field] == '')
						{
							$this->procedure[] = $field . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $field . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->monthBegin;
							$this->values[] = $objDate->monthEnd;
						}
					}

					// Sort by year
					elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
					{
						if ($session['filter'][$filter][$field] == '')
						{
							$this->procedure[] = $field . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $field . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->yearBegin;
							$this->values[] = $objDate->yearEnd;
						}
					}

					// Manual filter
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])
					{
						$this->procedure[] = $field . ' LIKE ?';
						$this->values[] = '%"' . $session['filter'][$filter][$field] . '"%';
					}

					// Other sort algorithm
					else
					{
						$this->procedure[] = $field . '=?';
						$this->values[] = $session['filter'][$filter][$field];
					}
				}
			}
		}

		// Add sorting options
		foreach ($sortingFields as $field)
		{
			$arrValues = array();
			$arrProcedure = array();

			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
			{
				$arrProcedure[] = 'pid=?';
				$arrValues[] = CURRENT_ID;
			}

			// Limit results if there is a keyword (search panel)
			/*
			if (strlen($session['search'][$this->strTable]['value']))
			{
				if ( in_array($session['search'][$this->strTable]['field'], $this->arrBaseFields))
				{
					$arrProcedure[] = "CAST(".$session['search'][$this->strTable]['field']." AS CHAR) REGEXP ?";
				}
				else
				{
					$arrProcedure[] = "CAST((SELECT value FROM tl_formdata_details WHERE ff_name='" . $session['search'][$this->strTable]['field'] . "' AND pid=f.id) AS CHAR) REGEXP ?";
				}
				$arrValues[] = $session['search'][$this->strTable]['value'];
			}
			*/

			// add condition if called form specific formdata
			if ($this->strFormFilterKey != '' && $this->strFormFilterValue != '')
			{
				$arrProcedure[] = $this->strFormFilterKey . '=?';
				$arrValues[] = $this->strFormFilterValue;
			}

			if (is_array($this->root))
			{
				$arrProcedure[] = "id IN(" . implode(',', $this->root) . ")";
			}


			if (in_array($field, $this->arrBaseFields) )
			{
				$sqlField = $field;
			}
			if (in_array($field, $this->arrDetailFields) )
			{
				$sqlField = "SELECT DISTINCT(value) FROM tl_formdata_details WHERE ff_name='" . $field . "' AND pid=f.id";
			}


			$objFields = $this->Database->prepare("SELECT DISTINCT(" . $sqlField . ") AS `". $field . "` FROM " . $this->strTable . " f ". ((is_array($arrProcedure) && strlen($arrProcedure[0])) ? ' WHERE ' . implode(' AND ', $arrProcedure) : ''))
										->execute($arrValues);


			// Begin select menu
			$fields .= '
<select name="'.$field.'" id="'.$field.'" class="tl_select' . (isset($session['filter'][$filter][$field]) ? ' active' : '') . '">
  <option value="tl_'.$field.'">'.(is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']).'</option>';

			if ($objFields->numRows)
			{
				$options = $objFields->fetchEach($field);

				// Sort by day
				if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 6) ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v == '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $v);
						}

						unset($options[$k]);
					}
				}

				// Sort by month
				elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 8) ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v == '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = date('Y-m', $v);
							$intMonth = (date('m', $v) - 1);

							if (strlen($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
							{
								$options[$v] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $v);
							}
						}

						unset($options[$k]);
					}
				}

				// Sort by year
				elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 10) ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v == '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = date('Y', $v);
						}

						unset($options[$k]);
					}
				}

				// Manual filter
				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])
				{
					$moptions = array();

					foreach($options as $option)
					{
						$doptions = deserialize($option);

						if (is_array($doptions))
						{
							$moptions = array_merge($moptions, $doptions);
						}
					}

					$options = $moptions;
				}

				$options = array_unique($options);
				$options_callback = array();

				// Load options callback
				if ($field!='form' && is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']) && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'])
				{
					$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

					$this->import($strClass);
					$options_callback = $this->$strClass->$strMethod($this);

					// Sort options according to the keys of the callback array
					$options = array_intersect(array_keys($options_callback), $options);
				}

				$options_sorter = array();
				$blnDate = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10));

				// Options
				foreach ($options as $kk=>$vv)
				{
					$value = $blnDate ? $kk : $vv;

					// Get name of the parent record
					if ($field == 'pid')
					{
						$showFields = $GLOBALS['TL_DCA'][$this->ptable]['list']['label']['fields'];

						if (!$showFields[0])
						{
							$showFields[0] = 'id';
						}

						$objShowFields = $this->Database->prepare("SELECT " . $showFields[0] . " FROM ". $this->ptable . " WHERE id=?")
														->limit(1)
														->execute($vv);

						if ($objShowFields->numRows)
						{
							$vv = $objShowFields->$showFields[0];
						}
					}

					// Replace ID with the foreign key
					elseif(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
					{
						$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']);

						$objParent = $this->Database->prepare("SELECT " . $key[1] . " FROM " . $key[0] . " WHERE id=?")
													->limit(1)
													->execute($vv);

						if ($objParent->numRows)
						{
							$vv = $objParent->$key[1];
						}
					}

					// Replace boolean checkbox value with "yes" and "no"
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'] || ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']))
					{
						$vv = strlen($vv) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
					}

					// Options callback
					elseif (is_array($options_callback) && count($options_callback) > 0)
					{
						$vv = $options_callback[$vv];
					}

					$option_label = '';

					// Use reference array
					if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference']))
					{
						$option_label = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv];
					}

					// Associative array
					elseif (array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options']))
					{
						$option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$vv];
					}

					// No empty options allowed
					if (!strlen($option_label))
					{
						$option_label = strlen($vv) ? $vv : '-';
					}

					$options_sorter[utf8_romanize($option_label)] = '  <option value="' . specialchars($value) . '"' . ((isset($session['filter'][$filter][$field]) && $value == $session['filter'][$filter][$field]) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
				}

				// Sort by option values
				if (!$blnDate)
				{
					$options_sorter = natcaseksort($options_sorter);

					if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(2, 4, 12)))
					{
						$options_sorter = array_reverse($options_sorter, true);
					}
				}

				$fields .= "\n" . implode("\n", $options_sorter);
			}

			// End select menu
			$fields .= '
</select> ';
		}

		return '

<div class="tl_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['filter'] . ':</strong>' . $fields . '
</div>';
	}



	public function export($sMode='csv')
	{

		$return = '';

		$blnCustomXlsExport = false;
		$arrHookData = array();
		$arrHookDataColumns = array();

		if ($sMode=='xls')
		{
			// check for HOOK efgExportXls
			if (array_key_exists('efgExportXls', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['efgExportXls']))
			{
				$blnCustomXlsExport = true;
			}
			else
			{
				include(TL_ROOT.'/system/modules/efg/plugins/xls_export/xls_export.php');
			}
		}

		// filter or search for values
		$session = $this->Session->getData();

		$showFields = array_merge($this->arrBaseFields, $this->arrDetailFields);
		$ignoreFields = array('tstamp', 'sorting');

		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$table_alias = ($table == 'tl_formdata' ? ' f' : '');

		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		if (is_array($this->orderBy) && strlen($this->orderBy[0]))
		{
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		if ($this->Input->get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Database->fieldExists('pid', $this->strTable))
		{
			$this->procedure[] = 'pid=?';
			$this->values[] = $this->Input->get('id');
		}

		$query = "SELECT * " .(count($this->arrSqlDetails) > 0 ? ', '.implode(',' , $this->arrSqlDetails) : '') ." FROM " . $this->strTable . $table_alias;

		$sqlWhere = '';

		if (isset($session['CURRENT']['IDS']) && count($session['CURRENT']['IDS'])>0 )
		{
			$sqlWhere = " WHERE id IN (" . implode(',', $session['CURRENT']['IDS']) . ") ";
		}

		if (count($this->procedure))
		{
			$arrProcedure = $this->procedure;

			foreach ($arrProcedure as $kProc => $vProc)
			{
				$strProcField = substr($vProc, 0, strpos($vProc, '='));
				if ( in_array($strProcField, $this->arrDetailFields) )
				{
					$arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='" . $strProcField . "' AND pid=f.id)=?";
				}
			}
			$sqlWhere .= ( $sqlWhere != '' ? " AND " : " WHERE ") . implode(' AND ', $arrProcedure);
		}

		if ( $sqlWhere != '')
		{
			$query .= $sqlWhere;
		}

		if (is_array($orderBy) && strlen($orderBy[0]))
		{
			foreach ( $orderBy as $o => $strVal)
			{
				$arrOrderField = explode(' ', $strVal);
				$strOrderField = $arrOrderField[0];
				unset($arrOrderField);
				if (!in_array($strOrderField, $this->arrBaseFields))
				{
					$orderBy[$o] = "(SELECT value FROM tl_formdata_details WHERE ff_name='" . $strOrderField . "' AND pid=f.id)";
				}
			}
			$query .= " ORDER BY " . implode(', ', $orderBy);
		}
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
		{
			$query .= " DESC";
		}

		$objRowStmt = $this->Database->prepare($query);

		$objRow = $objRowStmt->execute($this->values);

		$intRowCounter = -1;

		$strExpEncl = '';
		$strExpSep = ';';

		$useFormValues = $this->arrStoreForms[substr($this->strFormKey, 3)]['useFormValues'];
		$useFieldNames = $this->arrStoreForms[substr($this->strFormKey, 3)]['useFieldNames'];

		if ($sMode=='xls')
		{
			if (!$blnCustomXlsExport)
			{
				$xls = new xlsexport();
				$strXlsSheet = "Export";
				$xls->addworksheet($strXlsSheet);
			}
		}
		else // defaults to csv
		{
			header('Content-Type: appplication/csv; charset='.($this->blnExportUTF8Decode ? 'CP1252' : 'utf-8'));
			header('Content-Transfer-Encoding: binary');
			header('Content-Disposition: attachment; filename="export_' . $this->strFormKey . '_' . date("Ymd_His") .'.csv"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Expires: 0');
		}

		// List records
		if ($objRow->numRows)
		{
			$result = $objRow->fetchAllAssoc();

			// Rename each pid to its label and resort the result (sort by parent table)
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3 && $this->Database->fieldExists('pid', $this->strTable))
			{
				$firstOrderBy = 'pid';

				foreach ($result as $k=>$v)
				{
					$objField = $this->Database->prepare("SELECT " . $showFields[0] . " FROM " . $this->ptable . " WHERE id=?")
											   ->limit(1)
											   ->execute($v['pid']);
					$result[$k]['pid'] = $objField->$showFields[0];
				}

				$aux = array();
				foreach ($result as $row)
				{
					$aux[] = $row['pid'];
				}
				array_multisort($aux, SORT_ASC, $result);
			}

			// Process result and format values
			foreach ($result as $row)
			{
				$intRowCounter++;

				$args = array();
				$this->current[] = $row['id'];
				//$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				if ($intRowCounter == 0)
				{

					if ($sMode == 'xls')
					{
						if (!$blnCustomXlsExport)
						{
							$xls->totalcol = count($showFields);
						}
					}

					$strExpEncl = '"';
					$strExpSep = '';

					$intColCounter = -1;
					foreach ($showFields as $k=>$v)
					{
						if (in_array($v, $ignoreFields) )
						{
							continue;
						}

						$intColCounter++;

						if ($useFieldNames)
						{
							$strName = $v;
						}
						elseif ( strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0]) )
						{
							$strName = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0];
						}
						elseif ( strlen($GLOBALS['TL_LANG']['tl_formdata'][$v][0]) )
						{
							$strName = $GLOBALS['TL_LANG']['tl_formdata'][$v][0];
						}
						else
						{
							$strName = strtoupper($v);
						}

						if (strlen($strName))
						{
							$strName = $this->String->decodeEntities($strName);
						}

						if ($this->blnExportUTF8Decode || ($sMode == 'xls' && !$blnCustomXlsExport))
						{
							$strName = $this->convertEncoding($strName, $GLOBALS['TL_CONFIG']['characterSet'], 'CP1252');
						}

						if ($sMode=='csv')
						{
							$strName = str_replace('"', '""', $strName);
							echo $strExpSep . $strExpEncl . $strName . $strExpEncl;
							$strExpSep = ";";
						}
						if ($sMode=='xls')
						{
							if (!$blnCustomXlsExport)
							{
								$xls->setcell(array("sheetname" => $strXlsSheet,"row" => $intRowCounter, "col" => $intColCounter, "data" => $strName, "fontweight" => XLSFONT_BOLD, "vallign" => XLSXF_VALLIGN_TOP, "fontfamily" => XLSFONT_FAMILY_NORMAL));
								$xls->setcolwidth($strXlsSheet,$intColCounter,0x1aff);
							}
							else
							{
								$arrHookDataColumns[$v] = $strName;
							}
						}

					}

					$intRowCounter++;

					if ($sMode=='csv')
					{
						echo "\n";
					}

				} // intRowCounter 0

				$strExpSep = '';

				$intColCounter = -1;

				// Prepare field value
				foreach ($showFields as $k=>$v)
				{

					if (in_array($v, $ignoreFields) )
					{
						continue;
					}

					$intColCounter++;

					$strVal = '';
					$strVal = $row[$v];

					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'date' && in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
					{
						$strVal = ( $row[$v] ? date($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '' );
					}
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'datim' && in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
					{
						$strVal = ( $row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '' );
					}
					elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
					{
						$strVal = ( $row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '' );
					}
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'])
					{
						if ($useFormValues == 1)
						{
							// single value checkboxes don't have options
							if ((is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) && count($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) > 0))
							{
								$strVal = strlen($row[$v]) ? key($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) : '';
							}
							else
							{
								$strVal = $row[$v];
							}
						}
						else
						{
							$strVal = strlen($row[$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : '-';
						}
					}
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'radio'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupRadio'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'select'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'conditionalselect'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupSelect'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox'
							|| $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'efgLookupCheckbox')
					{
						// take the assigned value instead of the user readable output
						if ($useFormValues == 1)
						{
							if ((strpos($row[$v], "|") == FALSE) && (is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) && count($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) > 0))
							{
								// handle grouped options
								$arrOptions = array();
								foreach ($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'] as $o => $mxVal)
								{
									if ((!is_array($mxVal)))
									{
										$arrOptions[$o] = $mxVal;
									}
									else
									{
										foreach ($mxVal as $ov => $mxOVal)
										{
											$arrOptions[$ov] = $mxOVal;
										}
									}
								}

								//$options = array_flip($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']);
								$options = array_flip($arrOptions);
								$strVal = $options[$row[$v]];
							}
							else
							{
								if ((is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) && count($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']) > 0))
								{
									// handle grouped options
									$arrOptions = array();
									foreach ($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'] as $o => $mxVal)
									{
										if ((!is_array($mxVal)))
										{
											$arrOptions[$o] = $mxVal;
										}
										else
										{
											foreach ($mxVal as $ov => $mxOVal)
											{
												$arrOptions[$ov] = $mxOVal;
											}
										}
									}

									//$options = array_flip($GLOBALS['TL_DCA'][$table]['fields'][$v]['options']);
									$options = array_flip($arrOptions);

									$tmparr = split("\\|", $row[$v]);
									$fieldvalues = array();
									foreach ($tmparr as $valuedesc)
									{
										array_push($fieldvalues, $options[$valuedesc]);
									}
									$strVal = join(",\n", $fieldvalues);
								}
								else
								{
									$strVal = strlen($row[$v]) ? str_replace('|', ",\n", $row[$v]) : '';
								}
							}
						}
						else
						{
							$strVal = strlen($row[$v]) ? str_replace('|', ",\n", $row[$v]) : '';
						}
					}
					else
					{
						$row_v = deserialize($row[$v]);

						if (is_array($row_v))
						{
							$args_k = array();

							foreach ($row_v as $option)
							{
								$args_k[] = strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] : $option;
							}

							$args[$k] = implode(",\n", $args_k);
						}
						elseif (is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]))
						{
							$args[$k] = is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
						}
						else
						{
							$args[$k] = $row[$v];
						}
						$strVal = is_null($args[$k]) ? $args[$k] : vsprintf('%s', $args[$k]);
					}

					if (in_array($v, $this->arrBaseFields) || in_array($v, $this->arrOwnerFields))
					{
						if ($v == 'fd_member')
						{
							$strVal = $this->arrMembers[intval($row[$v])];
						}
						if ($v == 'fd_user')
						{
							$strVal = $this->arrUsers[intval($row[$v])];
						}
					}

					if (strlen($strVal))
					{
						$strVal = $this->String->decodeEntities($strVal);
						$strVal = preg_replace(array('/<br.*\/*>/si'), array("\n"), $strVal);

						if ($this->blnExportUTF8Decode || ($sMode == 'xls' && !$blnCustomXlsExport))
						{
							$strVal = $this->convertEncoding($strVal, $GLOBALS['TL_CONFIG']['characterSet'], 'CP1252');
						}
					}

					if ($sMode=='csv')
					{
						$strVal = str_replace('"', '""', $strVal);
						echo $strExpSep . $strExpEncl . $strVal . $strExpEncl;

						$strExpSep = ";";
					}
					if ($sMode=='xls')
					{
						if (!$blnCustomXlsExport)
						{
							$xls->setcell(array("sheetname" => $strXlsSheet,"row" => $intRowCounter, "col" => $intColCounter, "data" => $strVal, "vallign" => XLSXF_VALLIGN_TOP, "fontfamily" => XLSFONT_FAMILY_NORMAL));
						}
						else
						{
							$arrHookData[$intRowCounter][$v] = $strVal;
						}
					}

				}

				if ($sMode=='csv')
				{
					$strExpSep = '';
					echo "\n";
				}

			} // foreach ($result as $row)

		} // if objRow->numRows

		if ($sMode=='xls')
		{
			if (!$blnCustomXlsExport)
			{
				$xls->sendfile("export_" . $this->strFormKey . "_" . date("Ymd_His") . ".xls");
				exit;
			}
			else
			{
				foreach ($GLOBALS['TL_HOOKS']['efgExportXls'] as $key => $callback)
				{
					$this->import($callback[0]);
					$res = $this->$callback[0]->$callback[1]($arrHookDataColumns, $arrHookData);
				}
			}
		}
		exit;
	}


	public function exportxls()
	{
		$this->export('xls');
	}

	/**
	 * Convert encoding
	 * @return String
	 * @param $strString String to convert
	 * @param $from charset to convert from
	 * @param $to charset to convert to
	 */
	public function convertEncoding($strString, $from, $to)
	{
		if (USE_MBSTRING)
		{
			@mb_substitute_character('none');
			return @mb_convert_encoding($strString, $to, $from);
		}
		elseif (function_exists('iconv'))
		{
			if (strlen($iconv = @iconv($from, $to . '//IGNORE', $strString)))
			{
				return $iconv;
			}
			else
			{
				return @iconv($from, $to, $strString);
			}
		}
		return $strString;
	}


	/**
	 * get all members (FE)
	 */
	protected function getMembers()
	{
		if (!$this->arrMembers)
		{
			$members = array();
			$objMembers = $this->Database->prepare("SELECT id, CONCAT(firstname,' ',lastname) AS name,groups,login,username,locked,disable,start,stop FROM tl_member ORDER BY name ASC")
								->execute();
			$members[] = '-';
			if ($objMembers->numRows)
			{
				while ($objMembers->next())
				{
					$k = $objMembers->id;
					$v = $objMembers->name;
					$members[$k] = $v;
				}
			}
			$this->arrMembers = $members;
		}
	}

	/**
	 * get all users (BE)
	 */
	protected function getUsers()
	{
		if (!$this->arrUsers)
		{
			$users = array();

			// Get all users
			$objUsers = $this->Database->prepare("SELECT id,username,name,locked,disable,start,stop,admin,groups,modules,inherit,fop FROM tl_user ORDER BY name ASC")
								->execute();
			$users[] = '-';
			if ($objUsers->numRows)
			{
				while ($objUsers->next())
				{
					$k = $objUsers->id;
					$v = $objUsers->name;
					$users[$k] = $v;
				}
			}
			$this->arrUsers = $users;
		}
	}

}

?>