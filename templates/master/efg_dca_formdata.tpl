<?php echo '<?php'; ?> if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * This is the data container array for table tl_formdata.
 *
 * PHP version 5
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @license    LGPL
 * @filesource
 * @version    1.11.0
 */

<?php $this->import('String'); ?>
<?php echo '// This file is created when saving a form in form generator' . "\n"; ?>
<?php echo '// last created on ' .date("Y-m-d H:i:s") . ' by saving form "' . $this->arrForm['title'] . '"' . "\n"; ?>


<?php
// default list config
$arrListDefaults = array(
	'text' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'hidden' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'textarea' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'password' => array
	(
		'exclude'        => 'true',
		'search'         => 'false',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'select' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'true',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'conditionalselect' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'true',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'efgLookupSelect' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'radio' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'true',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'efgLookupRadio' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'checkbox' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'efgLookupCheckbox' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'upload' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'false',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	),
	'efgImageSelect' => array
	(
		'exclude'        => 'false',
		'search'         => 'true',
		'sorting'        => 'true',
		'filter'         => 'false',
		'flag'           => 'null',
		'eval'           => 'null',
	)
);
?>


/**
 * Table tl_formdata defined by form "<?php echo $this->arrForm['title']; ?>"
 */
$GLOBALS['TL_DCA']['tl_formdata'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Formdata',
		'ctable'                      => array('tl_formdata_details'),
<?php if ($this->arrForm['key'] != 'feedback'): ?>
		'closed'                      => false,
<?php else: ?>
		'closed'                      => true,
<?php endif; ?>
		'notEditable'                 => false,
		'enableVersioning'            => false,
		'doNotCopyRecords'            => true,
		'doNotDeleteRecords'          => true,
		'switchToEdit'                => true
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
			'fields'                  => array('date', 'form', 'alias', 'be_notes' <?php foreach ($this->arrFields as $varKey => $varVals): echo ', \'' . $varKey . '\''; endforeach; ?>),
<?php if ($this->arrForm['key'] != 'feedback'): ?>
			'format'                  => '<div class="fd_wrap">
	<div class="fd_head">%s<span>[%s]</span><span>%s</span></div>
	<div class="fd_notes">%s</div>
	<?php foreach ($this->arrFields as $varKey => $varVals): ?><div class="fd_row"><div class="fd_label"><?php echo (strlen($varVals['label']) ? str_replace("'", "\'", $this->String->decodeEntities($varVals['label'])) : $varKey); ?>: </div><div class="fd_value">%s </div></div>
	<?php endforeach; ?></div>',
			/*
			'label_callback'          => array('tl_fd_<?php echo str_replace('-', '_', $this->strFormKey); ?>','getRowLabel')
			*/
<?php endif; ?>
<?php if ($this->arrForm['key'] == 'feedback'): ?>
			/*
			 'format'                  => '<div class="fd_wrap">
	<div class="fd_head">%s<span>[%s]</span></div>
	<div class="fd_notes">%s</div>
	<?php foreach ($this->arrFields as $varKey => $varVals): ?><div class="fd_row"><div class="fd_label"><?php echo str_replace("'", "\'", $this->String->decodeEntities($varVals['label'])); ?>: </div><div class="fd_value">%s </div></div>
	<?php endforeach; ?></div>',
			*/
			'label_callback'          => array('tl_fd_feedback','getRowLabel')
<?php endif; ?>
		),
		'global_operations' => array
		(
<?php if ($this->arrForm['key'] != 'feedback'): ?>
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['export'],
				'href'                => 'act=export',
				'class'               => 'header_csv_export',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'exportxls' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['exportxls'],
				'href'                => 'act=exportxls',
				'class'               => 'header_xls_export',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
<?php endif; ?>
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
				//'href'                => 'table=tl_formdata_details',
				'button_callback'     => array('ModuleFormdata', 'callbackEditButton'),
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
			)
<?php if ($this->blnBackendMail): ?>
			,
			'mail' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['mail'],
				'href'                => 'act=mail',
				'icon'                => 'system/modules/efg/html/mail.gif'
			)
<?php endif; ?>			
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'form,date,ip,pid;fd_member,fd_user;alias,published,be_notes;<?php $strSep=''; foreach ($this->arrFields as $varKey => $varVals): echo $strSep . $varKey; $strSep=','; endforeach; ?>'
	),

	// Base fields in table tl_formdata
	'fields' => array
	(
		'form' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['form'],
			'inputType'               => 'select',
			'exclude'                 => <?php echo ($this->arrForm['key'] == 'feedback' ? 'false' : 'true' ); ?>,
			'search'                  => <?php echo ($this->arrForm['key'] == 'feedback' ? 'true' : 'false' ); ?>,
			'filter'                  => <?php echo ($this->arrForm['key'] == 'feedback' ? 'true' : 'false' ); ?>,
			'sorting'                 => <?php echo ($this->arrForm['key'] == 'feedback' ? 'true' : 'false' ); ?>,
			'options_callback'        => array('tl_formdata', 'getFormsSelect'),
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
			'eval'                    => array('rgxp' => 'datim'),
		),
		'ip' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['ip'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false
		),
		'fd_member' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory' => false, 'includeBlankOption' => true),
			'options_callback'        => array('tl_formdata', 'getMembersSelect'),
		),
		'fd_user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory' => false, 'includeBlankOption' => true),
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
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 5, 'style' => 'height: 80px'),
		)
	),
	'tl_formdata' => array
	(
		'baseFields'                 => array('id','sorting','tstamp','form','ip','date','fd_member','fd_user','published','alias','be_notes'),
		'detailFields'               => array(<?php $strSep = ''; foreach ($this->arrFields as $varKey => $varVals):
echo $strSep . "'" . $varKey . "'"; $strSep = ',';
endforeach; ?>),
<?php if ($this->arrForm['key'] != 'feedback'): ?>
		'formFilterKey'              => 'form',
		'formFilterValue'            => '<?php echo $this->arrForm['title']; ?>'
<?php endif; ?>
	)
);

// Detail fields in table tl_formdata_details
<?php foreach ($this->arrFields as $varKey => $varVals): ?>
// '<?php echo $varKey; ?>'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['label'] = array('<?php echo (strlen($varVals['label']) ? str_replace("'", "\'", $this->String->decodeEntities($varVals['label'])) : $varKey); ?>', '<?php echo '[' . $varKey .'] ' .str_replace("'", "\'", $this->String->decodeEntities($varVals['label'])); ?>');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['inputType'] = '<?php echo ( ($varVals['type']=='upload' && $varVals['storeFile']) || ($varVals['type']=='efgImageSelect') ? "fileTree" : $varVals['type']); ?>';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['exclude'] = <?php echo $arrListDefaults[$varVals['type']]['exclude']; ?>;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['search'] = <?php echo (($varVals['rgxp']=='date' || $varVals['rgxp']=='datim')? 'false' : $arrListDefaults[$varVals['type']]['search']); ?>;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['sorting'] = <?php echo $arrListDefaults[$varVals['type']]['sorting']; ?>;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['filter'] = <?php echo $arrListDefaults[$varVals['type']]['filter']; ?>;
<?php if (strlen($varVals['value'])): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['default'] = '<?php echo $this->String->decodeEntities($varVals['value']); ?>';
<?php endif; ?>
<?php switch($varVals['type']):
	case 'checkbox':
	case 'radio':
	case 'select':
	case 'conditionalselect':
		$arrOpts = deserialize($varVals['options']); $blnInGroup=false; $strGroupKey=''; $strGroupLabel=''; ?>
<?php foreach ($arrOpts as $kOpt => $arrOpt): ?>
<?php  if ($arrOpt['group']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['reference'] = &$GLOBALS['TL_LANG']['tl_formdata']['_optgroups_']['<?php echo $varKey; ?>'];
<?php break; endif; ?>
<?php endforeach; ?>
<?php foreach ($arrOpts as $kOpt => $arrOpt): ?>
<?php  if ($arrOpt['group']): $blnInGroup=true; $strGroupKey=$arrOpt['value']; $strGroupLabel=$arrOpt['label']; ?>
<?php if ($varVals['type'] == 'conditionalselect'): ?>
$GLOBALS['TL_LANG']['tl_formdata']['_optgroups_']['<?php echo $varKey; ?>']['<?php echo $strGroupKey; ?>'] = '<?php echo str_replace("'", "\'", $strGroupKey); ?>';
<?php else: ?>
$GLOBALS['TL_LANG']['tl_formdata']['_optgroups_']['<?php echo $varKey; ?>']['<?php echo $strGroupKey; ?>'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($strGroupLabel)); ?>';
<?php endif; ?>
<?php endif; ?>
<?php  if ($blnInGroup && strlen($strGroupKey)): ?>
<?php   if ($arrOpt['group']): continue; endif; ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['options']['<?php echo $strGroupKey; ?>']['<?php echo $arrOpt['value'];?>'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($arrOpt['label'])); ?>';
<?php  else: ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['options']['<?php echo $arrOpt['value'];?>'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($arrOpt['label'])); ?>';
<?php  endif; ?>
<?php endforeach; ?>
<?php foreach ($arrOpts as $kOpt => $arrOpt): ?>
<?php if($arrOpt['default']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['default'][] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($arrOpt['label']))?>';
<?php endif; ?>
<?php endforeach; ?>
<?php if ($varVals['type'] == 'checkbox' && count($arrOpts)>1 && !$arrVals['multiple']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['multiple'] = true;
<?php endif; unset($arrOpts); ?>
<?php if ($this->arrForm['efgStoreValues']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['efgStoreValues'] = true;
<?php endif; ?>
<?php
	break;
	case 'efgLookupCheckbox':
	case 'efgLookupRadio':
	case 'efgLookupSelect':
		$arrOpts = deserialize($varVals['efgLookupOptions']); ?>
<?php if (count($arrOpts)>0): foreach ($arrOpts as $kOpt => $valOpt): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['efgLookupOptions']['<?php echo $kOpt;?>'] = '<?php echo str_replace("'", "\'", $this->String->decodeEntities($valOpt)); ?>';
<?php endforeach; ?>
<?php if($varVals['type'] == 'efgLookupCheckbox'): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['multiple'] = true;
<?php endif; ?>
<?php endif; unset($arrOpts); ?>
<?php
	break;
	case 'upload': ?>
<?php if ($varVals['storeFile']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['files'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['fieldType'] = 'radio';
<?php endif; ?>
<?php
	break;
	case 'efgImageSelect': ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['files'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['fieldType'] = 'radio';
<?php
	break;
endswitch; ?>
<?php if ($varVals['conditionField'] && strlen($varVals['conditionField'])): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['conditionField'] = '<?php echo $this->arrFieldNamesById[$varVals['conditionField']]; ?>';
<?php endif; ?>
<?php if ($varVals['mandatory'] == "1"): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['mandatory'] = true;
<?php endif; ?>
<?php if ($varVals['minlength']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['minlength'] = <?php echo $varVals['minlength']; ?>;
<?php endif; ?>
<?php if ($varVals['maxlength']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['maxlength'] = <?php echo $varVals['maxlength']; ?>;
<?php endif; ?>
<?php if ($varVals['mSize']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['size'] = <?php echo $varVals['mSize']; ?>;
<?php endif; ?>
<?php if ($varVals['multiple'] == "1"): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['multiple'] = true;
<?php endif; ?>
<?php if ($varVals['rgxp']): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['rgxp'] = '<?php echo $varVals['rgxp']; ?>';
<?php endif; ?>
<?php if ($varVals['rgxp'] == 'date' || $varVals['rgxp'] == 'datim'): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['flag'] = 5;
<?php endif; ?>
<?php if ($varVals['size']): $arrSize = deserialize($varVals['size']); ?>
<?php if ($varVals['type'] == 'textarea'): ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['cols'] = <?php echo $arrSize[1]; ?>;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['eval']['rows'] = <?php echo $arrSize[0]; ?>;
<?php endif; ?>
<?php endif; ?>
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['ff_id'] = <?php echo $varVals['id']; ?>;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['f_id'] = <?php echo $varVals['pid']; ?>;
<?php endforeach; ?>

/**
 * Class tl_fd_<?php echo $this->strFormKey."\n"; ?>
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn
 * @package    Controller
 */
class tl_fd_<?php echo ( strlen($this->strFormKey) ? str_replace('-', '_', $this->strFormKey) : 'feedback'); ?> extends Backend
{

	/**
	 * Database result
	 * @var array
	 */
	protected $arrData = null;

	public function __construct()
	{
		parent::__construct();
	}


	/*
	* Create List Label for formdata item
	* This can be used to customize the backend list view for formdata "<?php echo $this->arrForm['title']; ?>"
	*/
	public function getRowLabel($arrRow)
	{
		$strRowLabel = '';

		$strKey = 'unpublished';

		$strRowLabel .= '<div class="fd_wrap">';
		$strRowLabel .= '<div class="fd_head">' . date($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date']) . '<span>[' . $arrRow['form'] . ']</span><span>' . $arrRow['alias'] . '</span></div>';
		$strRowLabel .= '<div class="fd_notes">' . $arrRow['be_notes'] . '</div>';
		$strRowLabel .= '<div class="mark_links">';
<?php foreach ($this->arrFields as $varKey => $varVals): ?>
		if ( strlen($arrRow['<?php echo $varKey; ?>']) )
		{
			$strRowLabel .= '<div class="fd_row">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['<?php echo $varKey; ?>']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['<?php echo $varKey; ?>'] . ' </div>';
			$strRowLabel .= '</div>';
		}
<?php endforeach; ?>
		$strRowLabel .= '</div></div>';

		return $strRowLabel;

	}

}

?>