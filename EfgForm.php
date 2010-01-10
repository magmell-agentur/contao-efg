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
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */

/**
 * Class EfgForm
 * based on class Form by Leo Feyer
 *
 * Provide methods to handle formdata frontend edit forms
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @version    1.11.0
 */
class EfgForm extends Hybrid
{

	/**
	 * Key
	 * @var string
	 */
	protected $strKey = 'form';

	/**
	 * Table
	 * @var string
	 */
	protected $strTable = 'tl_form';

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'form';

	/**
	 * Current record
	 * @var array
	 */
	protected $arrData = array();



	/**
	 * Remove name attributes in the back end so the form is not validated
	 * @return string
	 */
	public function generate()
	{

		$str = parent::generate();

		if (TL_MODE == 'BE')
		{
			$str = preg_replace('/name="[^"]+" ?/i', '', $str);
		}

		return $str;
	}


	/**
	 * Generate the form
	 * @return string
	 */
	protected function compile()
	{
	}

	/**
	 * Process form data, store it in the session and redirect to the jumpTo page
	 * @param array
	 */
	public function processFormData($arrSubmitted)
	{

		// Send form data via e-mail
		if ($this->sendViaEmail)
		{
			$this->import('String');

			$keys = array();
			$values = array();
			$fields = array();
			$message = '';

			foreach ($arrSubmitted as $k=>$v)
			{
				if ($k == 'cc')
				{
					continue;
				}

				$v = deserialize($v);

				// Skip empty fields
				if (!is_array($v) && !strlen($v) && $this->skipEmpty)
				{
					continue;
				}

				// Add field to message
				$message .= ucfirst($k) . ': ' . (is_array($v) ? implode(', ', $v) : preg_replace('/[\n\t\r]+/', ' ', $v)) . "\n";

				// Prepare XML file
				if ($this->format == 'xml')
				{
					$fields[] = array
					(
						'name' => $k,
						'values' => (is_array($v) ? $v : array(preg_replace('/[\n\t\r]+/', ' ', $v)))
					);
				}

				// Prepare CSV file
				if ($this->format == 'csv')
				{
					$keys[] = $k;
					$values[] = (is_array($v) ? implode(',', $v) : preg_replace('/[\n\t\r]+/', ' ', $v));
				}
			}

			$recipients = trimsplit(',', $this->recipient);

			// Format recipients
			foreach ($recipients as $k=>$v)
			{
				$recipients[$k] = str_replace(array('[', ']'), array('<', '>'), $v);
			}

			$email = new Email();

			// Get subject and message
			if ($this->format == 'email')
			{
				$message = $arrSubmitted['message'];
				$email->subject = $arrSubmitted['subject'];
			}

			// Set the admin e-mail as "from" address
			$email->from = $GLOBALS['TL_ADMIN_EMAIL'];

			// Get the "reply to" address
			if (strlen($this->Input->post('email', true)))
			{
				$replyTo = $this->Input->post('email', true);

				// Add name
				if (strlen($this->Input->post('name')))
				{
					$replyTo = $this->Input->post('name') . ' <' . $replyTo . '>';
				}

				$email->replyTo($replyTo);
			}

			// Fallback to default subject
			if (!strlen($email->subject))
			{
				$email->subject = $this->subject;
			}

			// Send copy to sender
			if (strlen($arrSubmitted['cc']))
			{
				$email->sendCc($this->Input->post('email', true));
				unset($_SESSION['FORM_DATA']['cc']);
			}

			// Attach XML file
			if ($this->format == 'xml')
			{
				$objTemplate = new FrontendTemplate('form_xml');

				$objTemplate->fields = $fields;
				$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];

				$email->attachFileFromString($objTemplate->parse(), 'form.xml', 'application/xml');
			}

			// Attach CSV file
			if ($this->format == 'csv')
			{
				$email->attachFileFromString($this->String->decodeEntities(implode(';', $keys) . "\n" . implode(';', $values)), 'form.csv', 'text/comma-separated-values');
			}

			$uploaded = '';

			// Attach uploaded files
			if (count($_SESSION['FILES']))
			{
				foreach ($_SESSION['FILES'] as $file)
				{
					// Add a link to the uploaded file
					if ($file['uploaded'])
					{
						$uploaded .= "\n" . $this->Environment->base . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
						continue;
					}

					$email->attachFileFromString(file_get_contents($file['tmp_name']), $file['name'], $file['type']);
				}
			}

			$uploaded = strlen(trim($uploaded)) ? "\n\n---\n" . $uploaded : '';

			// Send e-mail
			$email->text = $this->String->decodeEntities(trim($message)) . $uploaded . "\n\n";
			$email->sendTo($recipients);
		}

		// Store values in the database
		if ($this->storeValues && strlen($this->targetTable))
		{
			$arrSet = array();

			// Add timestamp
			if ($this->Database->fieldExists('tstamp', $this->targetTable))
			{
				$arrSet['tstamp'] = time();
			}

			// Fields
			foreach ($arrSubmitted as $k=>$v)
			{
				if ($k != 'cc' && $k != 'id')
				{
					$arrSet[$k] = $v;
				}
			}

			// Files
			if (count($_SESSION['FILES']))
			{
				foreach ($_SESSION['FILES'] as $k=>$v)
				{
					if ($v['uploaded'])
					{
						$arrSet[$k] = str_replace(TL_ROOT . '/', '', $v['tmp_name']);
					}
				}
			}

			$this->Database->prepare("INSERT INTO " . $this->targetTable . " %s")->set($arrSet)->execute();
		}

		// Store all values in the session
		foreach (array_keys($_POST) as $key)
		{
			$_SESSION['FORM_DATA'][$key] = $this->allowTags ? $this->Input->postHtml($key, true) : $this->Input->post($key, true);
		}

		$arrFiles = $_SESSION['FILES'];
		$arrData = $_SESSION['FORM_DATA'];

		$this->import('Efp');
		$this->Efp->processSubmittedData($arrData, $this->arrData, $arrFiles);

		// Reset form data in case it has been modified in a callback function
		$_SESSION['FORM_DATA'] = $arrData;
		$_SESSION['FILES'] = array(); // DO NOT CHANGE

		$this->jumpToOrReload($this->jumpTo);
	}


	/**
	 * Get the maximum file size that is allowed for file uploads
	 */
	private function getMaxFileSize()
	{
		$this->Template->maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];

		$objMaxSize = $this->Database->prepare("SELECT MAX(maxlength) AS maxlength FROM tl_form_field WHERE pid=? AND type=? AND maxlength>?")
									 ->execute($this->id, 'upload', 0);

		if ($objMaxSize->maxlength > 0)
		{
			$this->Template->maxFileSize = $objMaxSize->maxlength;
		}
	}


	/**
	 * Initialize the form in the current session
	 * @param string
	 */
	private function initializeSession($formId)
	{
		if ($this->Input->post('FORM_SUBMIT') != $formId)
		{
			return;
		}

		$arrMessageBox = array('TL_ERROR', 'TL_CONFIRM', 'TL_INFO');
		$_SESSION['FORM_DATA'] = is_array($_SESSION['FORM_DATA']) ? $_SESSION['FORM_DATA'] : array();

		foreach ($arrMessageBox as $tl)
		{
			if (is_array($_SESSION[$formId][$tl]))
			{
				$_SESSION[$formId][$tl] = array_unique($_SESSION[$formId][$tl]);

				foreach ($_SESSION[$formId][$tl] as $message)
				{
					$objTemplate = new Template('form_message');

					$objTemplate->message = $message;
					$objTemplate->class = strtolower($tl);

					$this->Template->fields .= $objTemplate->parse() . "\n";
				}

				$_SESSION[$formId][$tl] = array();
			}
		}
	}
}

?>