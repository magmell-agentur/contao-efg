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

class EfgRunonce extends Controller
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();

		// Fix potential Exception on line 0 because of __destruct method (see http://dev.contao.org/issues/2236)
		$this->import((TL_MODE=='BE' ? 'BackendUser' : 'FrontendUser'), 'User');
		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		// Nothing to do if EFG has not yet been installed
		if (!$this->Database->tableExists('tl_formdata'))
		{
			return;
		}

		$this->exec('updateTables');

	}


	private function execute($method)
	{

		try
		{
			$this->$method();
		}
		catch(Exception $e)
		{

			$strReturn = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contao Open Source CMS</title>
<style media="screen">
div { width:520px; margin:64px auto 18px; padding:24px; background:#ffc; border:1px solid #fc0; font-family:Verdana,sans-serif; font-size:13px; }
h1 { font-size:18px; font-weight:normal; margin:0 0 18px; }
</style>
</head>
<body>
<div>
<h1>Updating EFG failed</h1>
<pre style="white-space:normal">' . $e->getMessage() . '</pre>
</div>
</body>
</html>';

			echo $strReturn;

			exit;
		}

	}

}
