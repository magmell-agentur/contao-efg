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
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Backend
 * @license    GPL
 * @filesource
 */

/**
 * Class EfgFormLookupCheckbox
 *
 * Form field "checkbox (DB)".
 * based on FormCheckbox by Leo Feyer
 * @copyright  Thomas Kuhn 2007 - 2010
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Controller
 */
class EfgFormLookupCheckbox extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'form_widget';

	/**
	 * Options
	 * @var array
	 */
	protected $arrOptions = array();


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'efgLookupOptions':
				$this->import('Database');
				$this->import('String');
				$this->import('FormData');
				$this->arrConfiguration['efgLookupOptions'] = $varValue;
				$arrOptions = $this->FormData->prepareDcaOptions($this->arrConfiguration);
				$this->arrOptions = $arrOptions;
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'rgxp':
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Return a parameter
	 * @return string
	 * @throws Exception
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'options':
				return $this->arrOptions;
				break;
			default:
				return parent::__get($strKey);
				break;
		}
	}


	/**
	 * Check options if the field is mandatory
	 */
	public function validate()
	{
		$mandatory = $this->mandatory;
		$options = deserialize($this->getPost($this->strName));

		// Check if there is at least one value
		if ($mandatory && is_array($options))
		{
			foreach ($options as $option)
			{
				if (strlen($option))
				{
					$this->mandatory = false;
					break;
				}
			}
		}

		$varInput = $this->validator($options);

		if (!$this->hasErrors())
		{
			$this->varValue = $varInput;
		}

		// Reset the property
		if ($mandatory)
		{
			$this->mandatory = true;
		}

		// Clear result if nothing has been submitted
		if (!array_key_exists($this->strName, $_POST))
		{
			$this->varValue = '';
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$strOptions = '';
		$strReferer = $this->getReferer();
		$arrLookupOptions = deserialize($this->arrConfiguration['efgLookupOptions']);
		$strLookupTable = substr($arrLookupOptions['lookup_field'], 0, strpos($arrLookupOptions['lookup_field'], '.'));
		$blnSingleEvent = false;

		// if used as lookup on table tl_calendar_events and placed on events detail page
		if ($strLookupTable=='tl_calendar_events' && strlen($this->Input->get('events')) )
		{
			if (count($this->arrOptions)==1)
			{
				$this->varValue = array($this->arrOptions[0]['value']);
				$blnSingleEvent = true;
			}
		}
		// .. equivalent,  if linked from event reader page
		if ($strLookupTable=='tl_calendar_events' && (strpos($strReferer, '/event-reader/events/') || strpos($strReferer, '&events=') ) )
		{
			if (count($this->arrOptions)==1)
			{
				$this->varValue = array($this->arrOptions[0]['value']);
				$blnSingleEvent = true;
			}
		}

		foreach ($this->arrOptions as $i=>$arrOption)
		{
			$checked = '';
			if ( (is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue) || $this->varValue == $arrOption['value']) )
			{
			  $checked = ' checked="checked"';
			}
			
			$strOptions .= sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="checkbox" value="%s"%s /> <label for="opt_%s">%s</label></span>',
									$this->strName . ((count($this->arrOptions) > 1) ? '[]' : ''),
									$this->strId.'_'.$i,
									$arrOption['value'],
									$checked,
									$this->strId.'_'.$i,
									$arrOption['label']);
			
			// render as checked radio if used as lookup on tl_calendar_events and only one event available						
			if ($strLookupTable=='tl_calendar_events' && $blnSingleEvent)
			{
				$strOptions =  sprintf('<span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s /> <label for="opt_%s">%s</label></span>',
									$this->strName . ((count($this->arrOptions) > 1) ? '[]' : ''),
									$this->strId.'_'.$i,
									$arrOption['value'],
									$checked,
									$this->strId.'_'.$i,
									$arrOption['label']);
			}									
		}

        return sprintf('<div id="ctrl_%s" class="checkbox_container%s">%s</div>',
						$this->strId,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						$strOptions) . $this->addSubmit();
	}
}

?>