<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   efg
 * @author    Thomas Kuhn <mail@th-kuhn.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Thomas Kuhn 2007-2012
 */


/**
 * Class FormDataComments
 *
 * @copyright  Thomas Kuhn 2007-2012
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    efg
 */
class FormDataComments extends Backend
{

	/**
	 * List a particular record
	 * @param array
	 * @return string
	 */
	public function listComments($arrRow)
	{
		$this->import('FormData');

		$strRet = '';

		$objParent = $this->Database->prepare("SELECT `id`, `form`, `alias`  FROM tl_formdata WHERE id=?")
											->execute($arrRow['parent']);

		if ($objParent->numRows)
		{
			$strRet .= ' (' . $objParent->form;

			if (strlen($objParent->alias))
			{
				$strRet .= ' - '.$objParent->alias;
			}
			$strRet .= ')';
		}

		return $strRet;
	}


}
