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
 * Class FormdataProcessor
 *
 * @copyright  Thomas Kuhn 2007-2013
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Efg
 */
class FormdataProcessor extends \Frontend
{

	protected $strFdDcaKey = '';

	protected $strFormdataDetailsKey = 'details';


	/**
	 * Process submitted form data
	 * Send mail, store data in backend
	 * @param array $arrSubmitted Submitted data
	 * @param array|bool $arrForm Form configuration
	 * @param array|bool $arrFiles Files uploaded
	 * @param array|bool $arrLabels Form field labels
	 * @return void
	 */
	public function processSubmittedData($arrSubmitted, $arrForm=false, $arrFiles=false, $arrLabels=false) {

		// Form config
		if (!$arrForm)
		{
			return;
		}

		$arrFormFields = array();

		$this->import('FrontendUser', 'Member');
		$this->import('Formdata');

		$this->strFdDcaKey = 'fd_' . (!empty($arrForm['alias']) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title'])) );
		$this->Formdata->FdDcaKey = $this->strFdDcaKey;

		// get params of related listing formdata
		$intListingId = intval($_SESSION['EFP']['LISTING_MOD']['id']);
		if ($intListingId > 0)
		{
			$objListing = \Database::getInstance()->prepare("SELECT * FROM tl_module WHERE id=?")
				->execute($intListingId);
			if ($objListing->numRows)
			{
				$arrListing = $objListing->fetchAssoc();

				// mail delivery defined in frontend listing module
				$arrForm['sendConfirmationMailOnFrontendEditing'] = ($arrListing['efg_fe_no_confirmation_mail']) ? false : true;
				$arrForm['sendFormattedMailOnFrontendEditing'] = ($arrListing['efg_fe_no_formatted_mail']) ? false : true;
			}
		}

		if (!empty($arrListing['efg_DetailsKey']))
		{
			$this->strFormdataDetailsKey = $arrListing['efg_DetailsKey'];
		}

		$blnFEedit = false;
		$intOldId = 0;
		$strRedirectTo = '';

		$strUrl = preg_replace('/\?.*$/', '', \Environment::get('request'));
		$strUrlParams = '';
		$blnQuery = false;
		foreach (preg_split('/&(amp;)?/', $_SERVER['QUERY_STRING']) as $fragment)
		{
			if (strlen($fragment))
			{
				if (strncasecmp($fragment, $this->strFormdataDetailsKey, strlen($this->strFormdataDetailsKey)) !== 0 && strncasecmp($fragment, 'act', 3) !== 0)
				{
					$strUrlParams .= (!$blnQuery ? '' : '&amp;') . $fragment;
					$blnQuery = true;
				}
			}
		}

		if (in_array($arrListing['efg_fe_edit_access'], array('public','groupmembers','member')))
		{
			if (\Input::get('act') == 'edit')
			{
				$blnFEedit = true;

				$objCheck = \Database::getInstance()->prepare("SELECT id FROM tl_formdata WHERE id=? OR alias=?")
					->execute(\Input::get($this->strFormdataDetailsKey), \Input::get($this->strFormdataDetailsKey));

				if ($objCheck->numRows == 1)
				{
					$intOldId = intval($objCheck->id);
				}
				else
				{
					$this->log('Could not identify record by ID "' . \Input::get($this->strFormdataDetailsKey) . '"', __METHOD__s, TL_GENERAL);
				}
			}
		}

		// Types of form fields with storable data
		$arrFFstorable = $this->Formdata->arrFFstorable;

		if (($arrForm['storeFormdata'] || $arrForm['sendConfirmationMail'] || $arrForm['sendFormattedMail']) && !empty($arrSubmitted))
		{
			$timeNow = time();

			$this->loadDataContainer($this->strFdDcaKey);
			$this->loadDataContainer('tl_formdata_details');
			$this->loadDataContainer('tl_files');

			$arrFormFields = $this->Formdata->getFormfieldsAsArray($arrForm['id']);

			$arrBaseFields = array();
			$arrDetailFields = array();
			if (!empty($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields']))
			{
				$arrBaseFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields'];
			}
			if (!empty($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields']))
			{
				$arrDetailFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'];
			}
			$arrHookFields = array_merge($arrBaseFields, $arrDetailFields);

			$arrToSave = array();
			foreach($arrSubmitted as $k => $varVal)
			{
				if (in_array($k, array('id')) )
				{
					continue;
				}
				elseif (in_array($k, $arrHookFields) || in_array($k, array_keys($arrFormFields)) || in_array($k, array('FORM_SUBMIT','MAX_FILE_SIZE')))
				{
					$arrToSave[$k] = $varVal;
				}
			}

			// HOOK: process efg form data callback
			if (array_key_exists('processEfgFormData', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['processEfgFormData']))
			{
				foreach ($GLOBALS['TL_HOOKS']['processEfgFormData'] as $key => $callback)
				{
					$this->import($callback[0]);
					$arrResult = $this->$callback[0]->$callback[1]($arrToSave, $arrFiles, $intOldId, $arrForm, $arrLabels);
					if (!empty($arrResult))
					{
						$arrSubmitted = $arrResult;
						$arrToSave = $arrSubmitted;
					}
				}
			}

		}

		// Formdata storage
		if ($arrForm['storeFormdata'] && !empty($arrSubmitted))
		{
			$blnStoreOptionsValue = ($arrForm['efgStoreValues']) ? true : false;

			// if frontend editing, get old record
			if ($intOldId > 0)
			{
				$arrOldData = $this->Formdata->getFormdataAsArray($intOldId);
				$arrOldFormdata = $arrOldData['fd_base'];
				$arrOldFormdataDetails = $arrOldData['fd_details'];
			}

			// Prepare record tl_formdata
			$arrSet = array
			(
				'form' => $arrForm['title'],
				'tstamp' => $timeNow,
				'date' => $timeNow,
				'ip' => \System::anonymizeIp(\Environment::get('ip')),
				'published' => ($GLOBALS['TL_DCA']['tl_formdata']['fields']['published']['default'] ? '1' : '' ),
				'fd_member' => intval($this->Member->id),
				'fd_member_group' => intval($this->Member->groups[0]),
				'fd_user' => intval($this->User->id),
				'fd_user_group' => intval($this->User->groups[0])
			);

			// if frontend editing keep some values from existing record
			if ($intOldId > 0)
			{
				$arrSet['form'] = $arrOldFormdata['form'];
				$arrSet['be_notes'] = $arrOldFormdata['be_notes'];
				$arrSet['fd_member'] = $arrOldFormdata['fd_member'];
				$arrSet['fd_member_group'] = $arrOldFormdata['fd_member_group'];
				if (intval($this->Member->id) > 0)
				{
					$arrSet['fd_member'] = intval($this->Member->id);
					if (count($this->Member->groups) == 1 && intval($this->Member->groups[0]) > 0)
					{
						$arrSet['fd_member_group'] = intval($this->Member->groups[0]);
					}
				}
				else
				{
					$arrSet['fd_member'] = 0;
				}
				$arrSet['fd_user'] = $arrOldFormdata['fd_user'];
				$arrSet['fd_user_group'] = $arrOldFormdata['fd_user_group'];

				// set published to value of old record, if no default value is defined
				if (!isset($GLOBALS['TL_DCA']['tl_formdata']['fields']['published']['default']))
				{
					$arrSet['published'] = $arrOldFormdata['published'];
				}
			}

			// store formdata
			// update or insert and delete
			if ($blnFEedit && strlen($arrListing['efg_fe_keep_id']))
			{
				$intNewId = $intOldId;
				\Database::getInstance()->prepare("UPDATE tl_formdata %s WHERE id=?")->set($arrSet)->execute($intOldId);
				\Database::getInstance()->prepare("DELETE FROM tl_formdata_details WHERE pid=?")->execute($intOldId);
			}
			else
			{
				$objNewFormdata = \Database::getInstance()->prepare("INSERT INTO tl_formdata %s")->set($arrSet)->execute();
				$intNewId = $objNewFormdata->insertId;

				// update related comments
				if (in_array('comments', $this->Config->getActiveModules()))
				{
					\Database::getInstance()->prepare("UPDATE tl_comments %s WHERE `source` = 'tl_formdata' AND parent=?")
						->set(array('parent' => $intNewId))
						->execute($intOldId);
				}
			}

			// store details data
			foreach ($arrFormFields as $k => $arrField)
			{

				//$strType = $arrField['type'];
				$strType = $arrField['formfieldType'];
				$strVal = '';

				if (in_array($strType, $arrFFstorable))
				{

					if ($blnStoreOptionsValue && in_array($strType, array('checkbox', 'radio', 'select')))
					{
						$arrField['eval']['efgStoreValues'] = true;
					}

					// set rgxp 'date' for field type 'calendar' if not set
					if ($strType == 'calendar')
					{
						if (!isset($arrField['rgxp']))
						{
							$arrField['rgxp'] = 'date';
						}
					}
					// set rgxp 'date' and dateFormat for field type 'xdependentcalendarfields'
					elseif ($strType == 'xdependentcalendarfields')
					{
						$arrField['rgxp'] = 'date';
						$arrField['dateFormat'] = $arrField['xdateformat'];
					}
// TODO: check/cleanup
//					// set isDatabaseAssisted for field type upload
//					elseif ($strType == 'upload')
//					{
//						$arrField['isDatabaseAssisted'] = false;
//						if (version_compare(VERSION, '3.0', '>='))
//						{
//							$this->loadDataContainer('tl_files');
//
//							if ($GLOBALS['TL_DCA']['tl_files']['config']['databaseAssisted'])
//							{
//								$arrField['isDatabaseAssisted'] = true;
//							}
//						}
//					}

					$strVal = $this->Formdata->preparePostValueForDatabase($arrSubmitted[$k], $arrField, $arrFiles[$k]);

					// special treatment for type upload
					// if frontend editing and no new upload, keep old file
					if ($strType == 'upload')
					{
						if ($intOldId)
						{
							if (!$arrFiles[$k]['name'])
							{
								if (strlen($arrOldFormdataDetails[$k]['value']))
								{
									$strVal = $arrOldFormdataDetails[$k]['value'];
								}
							}
						}
					}

					if ($arrSubmitted[$k] || ($strType == 'upload' && strlen($strVal)))
					{
						// prepare data
						$arrFieldSet = array(
							'pid' => $intNewId,
							'sorting' => $arrField['sorting'],
							'tstamp' => $timeNow,
							'ff_id' => $arrField['id'],
							'ff_name' => $arrField['name'],
							'value' => $strVal
						);

						$objNewFormdataDetails = \Database::getInstance()
							->prepare("INSERT INTO tl_formdata_details %s")
							->set($arrFieldSet)
							->execute();

					}

				}
			} // end foreach $arrFormFields

			// after frontend editing delete old record
			if ($blnFEedit)
			{
				if (!isset($arrListing['efg_fe_keep_id']) || $arrListing['efg_fe_keep_id'] != "1")
				{
					if ($intNewId > 0 && intval($intOldId)>0 && intval($intNewId) != intval($intOldId))
					{
						\Database::getInstance()->prepare("DELETE FROM tl_formdata_details WHERE pid=?")
							->execute($intOldId);
						\Database::getInstance()->prepare("DELETE FROM tl_formdata WHERE id=?")
							->execute($intOldId);
					}
				}
				$strRedirectTo = preg_replace('/\?.*$/', '', \Environment::get('request'));
			}

			// auto generate alias
			$strAlias = $this->Formdata->generateAlias($arrOldFormdata['alias'], $arrForm['title'], $intNewId);
			if (strlen($strAlias))
			{
				$arrUpd = array('alias' => $strAlias);
				\Database::getInstance()->prepare("UPDATE tl_formdata %s WHERE id=?")
					->set($arrUpd)
					->execute($intNewId);
			}
		} // end form data storage

		// store data in session to display on confirmation page
		unset($_SESSION['EFP']['FORMDATA']);
		$blnSkipEmpty = ($arrForm['confirmationMailSkipEmpty']) ? true : false;

		foreach ($arrFormFields as $k => $arrField)
		{
			//$strType = $arrField['type'];
			$strType = $arrField['formfieldType'];
			$strVal = '';
			if (in_array($strType, $arrFFstorable))
			{
				$strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$k], $arrField, $arrFiles[$k], $blnSkipEmpty);
			}

			$_SESSION['EFP']['FORMDATA'][$k] = $strVal;
		}
		$_SESSION['EFP']['FORMDATA']['_formId_'] = $arrForm['id'];
		// end store data in session

		// Confirmation Mail

		if ($blnFEedit && !$arrForm['sendConfirmationMailOnFrontendEditing'])
		{
			$arrForm['sendConfirmationMail'] = false;
		}

		if ($arrForm['sendConfirmationMail'])
		{
			$messageText = '';
			$messageHtml = '';
			$messageHtmlTmpl = '';
			$recipient  = '';
			$arrRecipient = array();
			$sender = '';
			$senderName = '';
			$replyTo = '';
			$attachments = array();

			$blnSkipEmpty = ($arrForm['confirmationMailSkipEmpty']) ? true : false;

			$sender = $arrForm['confirmationMailSender'];
			if (!empty($sender))
			{
				$sender = str_replace(array('[', ']'), array('<', '>'), $sender);
				if (strpos($sender, '<') > 0)
				{
					preg_match('/(.*)?<(\S*)>/si', $sender, $parts);
					$sender = $parts[2];
					$senderName = trim($parts[1]);
				}
			}

			$recipientFieldName = $arrForm['confirmationMailRecipientField'];
			$varRecipient = $arrSubmitted[$recipientFieldName];
			if (is_array($varRecipient))
			{
				$arrRecipient = $varRecipient;
			}
			else
			{
				$arrRecipient = trimsplit(',', $varRecipient);
			}

			if (!empty($arrForm['confirmationMailRecipient']))
			{
				$varRecipient = $arrForm['confirmationMailRecipient'];
				$arrRecipient = array_merge($arrRecipient, trimsplit(',', $varRecipient));
			}
			$arrRecipient = array_unique($arrRecipient);

			$subject = \String::decodeEntities($arrForm['confirmationMailSubject']);
			$messageText = \String::decodeEntities($arrForm['confirmationMailText']);
			$messageHtmlTmpl = $arrForm['confirmationMailTemplate'];

			if (is_numeric($messageHtmlTmpl) && $messageHtmlTmpl > 0)
			{
				$objFileModel = \FilesModel::findByPk($messageHtmlTmpl);
				if ($objFileModel !== null)
				{
					$messageHtmlTmpl = $objFileModel->path;
				}
			}
			if ($messageHtmlTmpl != '')
			{
				$fileTemplate = new \File($messageHtmlTmpl);
				if ($fileTemplate->mime == 'text/html')
				{
					$messageHtml = $fileTemplate->getContent();
				}
			}

			// prepare insert tags to handle separate from 'condition tags'
			if (strlen($messageText))
			{
				$messageText = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $messageText);
			}
			if (strlen($messageHtml))
			{
				$messageHtml = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $messageHtml);
			}
			if (strlen($subject))
			{
				$subject = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $subject);
			}
			if (strlen($sender))
			{
				$sender = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $sender);
			}

			$blnEvalSubject = $this->Formdata->replaceConditionTags($subject);
			$blnEvalMessageText = $this->Formdata->replaceConditionTags($messageText);
			$blnEvalMessageHtml = $this->Formdata->replaceConditionTags($messageHtml);

			// Replace tags in messageText, messageHtml ...
			$tags = array();
			preg_match_all('/__BRCL__.*?__BRCR__/si', $messageText . $messageHtml . $subject . $sender, $tags);

			// Replace tags of type {{form::<form field name>}}
			// .. {{form::uploadfieldname?attachment=true}}
			// .. {{form::fieldname?label=Label for this field: }}
			foreach ($tags[0] as $tag)
			{
				$elements = explode('::', preg_replace(array('/^__BRCL__/i', '/__BRCR__$/i'), array('',''), $tag));

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
							$arrTagParams = $this->Formdata->parseInsertTagParams($tag);
						}

						$arrField = $arrFormFields[$strKey];
						$arrField['efgMailSkipEmpty'] = $blnSkipEmpty;

						//$strType = $arrField['type'];
						$strType = $arrField['formfieldType'];

						if (!isset($arrFormFields[$strKey]) && in_array($strKey, $this->arrBaseFields))
						{
							$arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey];
							$strType = $arrField['inputType'];
						}

						$strLabel = '';
						$strVal = '';

						if ($arrTagParams && strlen($arrTagParams['label']))
						{
							$strLabel = $arrTagParams['label'];
						}

						if (in_array($strType, $arrFFstorable))
						{
							if ($strType == 'efgImageSelect')
							{
								$strVal = '';
								$varVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								$varText = array();
								$varHtml = array();
								if (is_string($varVal))
								{
									$varVal = array($varVal);
								}
								if (!empty($varVal))
								{
									foreach ($varVal as $strVal)
									{
										if (strlen($strVal))
										{
											$varText[] = \Environment::get('base') . $strVal;
											$varHtml[] = '<img src="' . $strVal . '"' . $this->Formdata->getEmptyTagEnd();
										}
									}
								}
								if (empty($varText) && $blnSkipEmpty)
								{
									$strLabel = '';
								}

								$messageText = str_replace($tag, $strLabel . implode(', ', $varText), $messageText);
								$messageHtml = str_replace($tag, $strLabel . implode(' ', $varHtml) , $messageHtml);
							}
							elseif ($strType == 'upload')
							{
								if ($arrTagParams && (array_key_exists('attachment', $arrTagParams) && $arrTagParams['attachment'] == true))
								{
									if (!empty($arrFiles[$strKey]['tmp_name']) && is_file($arrFiles[$strKey]['tmp_name']))
									{
										if (!isset($attachments[$arrFiles[$strKey]['tmp_name']]))
										{
											$attachments[$arrFiles[$strKey]['tmp_name']] = array('name' => $arrFiles[$strKey]['name'], 'file' => $arrFiles[$strKey]['tmp_name'], 'mime' => $arrFiles[$strKey]['type']);
										}

									}
									$strVal = '';
								}
								else
								{
									$strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								}
								if (!is_array($strVal) && !strlen($strVal) && $blnSkipEmpty)
								{
									$strLabel = '';
								}
								$messageText = str_replace($tag, $strLabel . $strVal, $messageText);
								$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);
							}
							else
							{
								$strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								if (!is_array($strVal) && !strlen($strVal) && $blnSkipEmpty)
								{
									$strLabel = '';
								}
								$messageText = str_replace($tag, $strLabel . $strVal, $messageText);

								if (is_string($strVal) && strlen($strVal) && !is_bool(strpos($strVal, "\n")))
								{
									$strVal = $this->Formdata->formatMultilineValue($strVal);
								}
								$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);
							}
						}

						// replace insert tags in subject
						if (!empty($subject))
						{
							$subject = str_replace($tag, $strVal, $subject);
						}

						// replace insert tags in sender
						if (!empty($sender))
						{
							$sender = str_replace($tag, $strVal, $sender);
						}

						break;
				}
			} // foreach tags

			// Replace standard insert tags
			if (!empty($messageText))
			{
				$messageText = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $messageText);
				$messageText = $this->replaceInsertTags($messageText, false);
				if ($blnEvalMessageText)
				{
					$messageText = $this->Formdata->evalConditionTags($messageText, $arrSubmitted, $arrFiles, $arrForm);
				}
				$messageText = strip_tags($messageText);
			}

			if (!empty($messageHtml))
			{
				$messageHtml = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $messageHtml);
				$messageHtml = $this->replaceInsertTags($messageHtml, false);
				if ($blnEvalMessageHtml)
				{
					$messageHtml = $this->Formdata->evalConditionTags($messageHtml, $arrSubmitted, $arrFiles, $arrForm);
				}
			}
			// replace insert tags in subject
			if (!empty($subject))
			{
				$subject = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $subject);
				$subject = $this->replaceInsertTags($subject, false);
				if ($blnEvalSubject)
				{
					$subject = $this->Formdata->evalConditionTags($subject, $arrSubmitted, $arrFiles, $arrForm);
				}
			}
			// replace insert tags in sender
			if (!empty($sender))
			{
				$sender = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $sender);
				$sender = $this->replaceInsertTags($sender, false);
			}

			// replace insert tags in replyto
			if (!empty($arrForm['confirmationMailReplyto']))
			{
				$replyTo = $this->replaceInsertTags($arrForm['confirmationMailReplyto'], false);
			}

			$confEmail = new \Email();
			$confEmail->from = $sender;
			if (!empty($senderName))
			{
				$confEmail->fromName = $senderName;
			}
			if (!empty($replyTo))
			{
				$confEmail->replyTo($replyTo);
			}

			$confEmail->subject = $subject;

			// Thanks to Torben Schwellnus
			// check if we want custom attachments...
			if ($arrForm['addConfirmationMailAttachments'])
			{
				if($arrForm['confirmationMailAttachments'])
				{
					$arrCustomAttachments = deserialize($arrForm['confirmationMailAttachments'], true);

					if (is_array($arrCustomAttachments))
					{
						foreach ($arrCustomAttachments as $intFileId)
						{
							$objFileModel = \FilesModel::findByPk($intFileId);

							if ($objFileModel !== null && is_file(TL_ROOT . '/' . $objFileModel->path) && is_readable(TL_ROOT . '/' . $objFileModel->path))
							{
								$objFile = new \File($objFileModel->path);
								if ($objFile->size)
								{
									$attachments[$objFile->value] = array('file' => TL_ROOT . '/' . $objFile->value, 'name' => $objFile->basename, 'mime' => $objFile->mime);
								}
							}
						}
					}
				}
			}

			if (!empty($attachments))
			{
				foreach ($attachments as $strFile => $varParams)
				{
					$strContent = file_get_contents($strFile, false);
					$confEmail->attachFileFromString($strContent, $varParams['name'], $varParams['mime']);
				}
			}

			if (!empty($dirImages))
			{
				$confEmail->imageDir = $dirImages;
			}
			if (!empty($messageText))
			{
				$confEmail->text = $messageText;
			}
			if (!empty($messageHtml))
			{
				$confEmail->html = $messageHtml;
			}

			// Send e-mail
			$blnConfirmationSent = false;
			if (!empty($arrRecipient))
			{
				foreach ($arrRecipient as $recipient)
				{
					if (!empty($recipient))
					{
						$recipient = $this->replaceInsertTags($recipient, false);
						$recipient = str_replace(array('[', ']'), array('<', '>'), $recipient);
						$recipientName = '';
						if (strpos($recipient, '<') > 0)
						{
							preg_match('/(.*)?<(\S*)>/si', $recipient, $parts);
							$recipientName = trim($parts[1]);
							$recipient = (!empty($recipientName) ? $recipientName.' <'.$parts[2].'>' : $parts[2]);
						}
					}

					$confEmail->sendTo($recipient);
					$blnConfirmationSent = true;
				}
			}

			if ($blnConfirmationSent && isset($intNewId) && intval($intNewId) > 0)
			{
				$arrUpd = array('confirmationSent' => '1', 'confirmationDate' => $timeNow);
				$res = \Database::getInstance()->prepare("UPDATE tl_formdata %s WHERE id=?")
					->set($arrUpd)
					->execute($intNewId);
			}

		} // End confirmation mail

		// Information (formatted) Mail

		if ($blnFEedit && !$arrForm['sendFormattedMailOnFrontendEditing'])
		{
			$arrForm['sendFormattedMail'] = false;
		}

		if ($arrForm['sendFormattedMail'])
		{
			$messageText = '';
			$messageHtml = '';
			$messageHtmlTmpl = '';
			$recipient  = '';
			$arrRecipient = array();
			$sender = '';
			$senderName = '';
			$attachments = array();

			$blnSkipEmpty = ($arrForm['formattedMailSkipEmpty']) ? true : false;

			// Set the admin e-mail as "from" address
			$sender = $GLOBALS['TL_ADMIN_EMAIL'];
			if (!empty($sender))
			{
				$sender = str_replace(array('[', ']'), array('<', '>'), $sender);
				if (strpos($sender, '<') > 0)
				{
					preg_match('/(.*)?<(\S*)>/si', $sender, $parts);
					$sender = $parts[2];
					$senderName = trim($parts[1]);
				}
			}

			$varRecipient = $arrForm['formattedMailRecipient'];
			if (is_array($varRecipient))
			{
				$arrRecipient = $varRecipient;
			}
			else
			{
				$arrRecipient = trimsplit(',', $varRecipient);
			}
			$arrRecipient = array_unique($arrRecipient);

			$subject = \String::decodeEntities($arrForm['formattedMailSubject']);
			$messageText = \String::decodeEntities($arrForm['formattedMailText']);
			$messageHtmlTmpl = $arrForm['formattedMailTemplate'];

			if (is_numeric($messageHtmlTmpl) && $messageHtmlTmpl > 0)
			{
				$objFileModel = \FilesModel::findByPk($messageHtmlTmpl);
				if ($objFileModel !== null)
				{
					$messageHtmlTmpl = $objFileModel->path;
				}
			}
			if ($messageHtmlTmpl != '')
			{
				$fileTemplate = new \File($messageHtmlTmpl);
				if ($fileTemplate->mime == 'text/html')
				{
					$messageHtml = $fileTemplate->getContent();
				}
			}

			// prepare insert tags to handle separate from 'condition tags'
			if (!empty($messageText))
			{
				$messageText = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $messageText);
			}
			if (!empty($messageHtml))
			{
				$messageHtml = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $messageHtml);
			}
			if (!empty($subject))
			{
				$subject = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $subject);
			}
			if (!empty($sender))
			{
				$sender = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $sender);
			}

			$blnEvalSubject = $this->Formdata->replaceConditionTags($subject);
			$blnEvalMessageText = $this->Formdata->replaceConditionTags($messageText);
			$blnEvalMessageHtml = $this->Formdata->replaceConditionTags($messageHtml);

			// Replace tags in messageText, messageHtml ...
			$tags = array();
			preg_match_all('/__BRCL__.*?__BRCR__/si', $messageText . $messageHtml . $subject . $sender, $tags);

			// Replace tags of type {{form::<form field name>}}
			// .. {{form::uploadfieldname?attachment=true}}
			// .. {{form::fieldname?label=Label for this field: }}
			foreach ($tags[0] as $tag)
			{
				$elements = explode('::', trim(str_replace(array('__BRCL__', '__BRCR__'), array('', ''), $tag)));
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
							$arrTagParams = $this->Formdata->parseInsertTagParams($tag);
						}

						$arrField = $arrFormFields[$strKey];
						$arrField['efgMailSkipEmpty'] = $blnSkipEmpty;

						//$strType = $arrField['type'];
						$strType = $arrField['formfieldType'];

						$strLabel = '';
						$strVal = '';

						if ($arrTagParams && !empty($arrTagParams['label']))
						{
							$strLabel = $arrTagParams['label'];
						}

						if (in_array($strType, $arrFFstorable))
						{
							if ($strType == 'efgImageSelect')
							{
								$varText = array();
								$varHtml = array();

								$varVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);

								if (is_string($varVal))
								{
									$varVal = array($varVal);
								}
								if (!empty($varVal))
								{
									foreach ($varVal as $strVal)
									{
										if (strlen($strVal))
										{
											$varText[] = \Environment::get('base') . $strVal;
											$varHtml[] = '<img src="' . $strVal . '"' . $this->Formdata->getEmptyTagEnd();
										}
									}
								}
								if (empty($varText) && $blnSkipEmpty)
								{
									$strLabel = '';
								}

								$messageText = str_replace($tag, $strLabel . implode(', ', $varText), $messageText);
								$messageHtml = str_replace($tag, $strLabel . implode(' ', $varHtml) , $messageHtml);
							}
							elseif ($strType == 'upload')
							{
								if ($arrTagParams && (array_key_exists('attachment', $arrTagParams) && $arrTagParams['attachment'] == true))
								{
									if (!empty($arrFiles[$strKey]['tmp_name']) && is_file($arrFiles[$strKey]['tmp_name']))
									{
										if (!isset($attachments[$arrFiles[$strKey]['tmp_name']]))
										{
											$attachments[$arrFiles[$strKey]['tmp_name']] = array('name' => $arrFiles[$strKey]['name'], 'file' => $arrFiles[$strKey]['tmp_name'], 'mime' => $arrFiles[$strKey]['type']);
										}
									}
									$strVal = '';
								}
								else
								{
									$strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								}
								if (!is_array($strVal) && !strlen($strVal) && $blnSkipEmpty)
								{
									$strLabel = '';
								}
								$messageText = str_replace($tag, $strLabel . $strVal, $messageText);
								$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);
							}
							else
							{
								$strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
								if (!is_array($strVal) && !strlen($strVal) && $blnSkipEmpty)
								{
									$strLabel = '';
								}
								$messageText = str_replace($tag, $strLabel . $strVal, $messageText);

								if (is_string($strVal) && !empty($strVal) && !is_bool(strpos($strVal, "\n")))
								{
									$strVal = $this->Formdata->formatMultilineValue($strVal);
								}
								$messageHtml = str_replace($tag, $strLabel . $strVal, $messageHtml);
							}
						}

						// replace insert tags in subject
						if (!empty($subject))
						{
							$subject = str_replace($tag, $strVal, $subject);
						}

						// replace insert tags in sender
						if (!empty($sender))
						{
							$sender = str_replace($tag, $strVal, $sender);
						}

						break;
				}
			}

			// Replace standard insert tags
			if (!empty($messageText))
			{
				$messageText = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $messageText);
				$messageText = $this->replaceInsertTags($messageText, false);
				if ($blnEvalMessageText)
				{
					$messageText = $this->Formdata->evalConditionTags($messageText, $arrSubmitted, $arrFiles, $arrForm);
				}
				$messageText = strip_tags($messageText);
			}
			if (!empty($messageHtml))
			{
				$messageHtml =  preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $messageHtml);
				$messageHtml = $this->replaceInsertTags($messageHtml, false);
				if ($blnEvalMessageHtml)
				{
					$messageHtml = $this->Formdata->evalConditionTags($messageHtml, $arrSubmitted, $arrFiles, $arrForm);
				}
			}
			// replace insert tags in subject
			if (!empty($subject))
			{
				$subject = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $subject);
				$subject = $this->replaceInsertTags($subject, false);
				if ($blnEvalSubject)
				{
					$subject = $this->Formdata->evalConditionTags($subject, $arrSubmitted, $arrFiles, $arrForm);
				}
			}
			// replace insert tags in sender
			if (!empty($sender))
			{
				$sender = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $sender);
				$sender = trim($this->replaceInsertTags($sender, false));
			}

			$infoEmail = new \Email();
			$infoEmail->from = $sender;
			if (!empty($senderName))
			{
				$infoEmail->fromName = $senderName;
			}
			$infoEmail->subject = $subject;

			// Get "reply to" address, if form contains field named 'email'
			if (isset($arrSubmitted['email']) && !empty($arrSubmitted['email']) && !is_bool(strpos($arrSubmitted['email'], '@')))
			{
				$replyTo = $arrSubmitted['email'];
				// add name
				if (isset($arrSubmitted['name']) && !empty($arrSubmitted['name']))
				{
					$replyTo = '"'. $arrSubmitted['name'] .'" <' . $arrSubmitted['email'] . '>';
				}
				$infoEmail->replyTo($replyTo);
			}

			// check if we want custom attachments...
			if ($arrForm['addFormattedMailAttachments'])
			{
				if ($arrForm['formattedMailAttachments'])
				{
					$arrCustomAttachments = deserialize($arrForm['formattedMailAttachments'], true);

					if (is_array($arrCustomAttachments))
					{
						foreach ($arrCustomAttachments as $intFileId)
						{
							$objFileModel = \FilesModel::findByPk($intFileId);

							if ($objFileModel !== null && is_file(TL_ROOT . '/' . $objFileModel->path) && is_readable(TL_ROOT . '/' . $objFileModel->path))
							{
								$objFile = new \File($objFileModel->path);
								if ($objFile->size)
								{
									$attachments[$objFile->value] = array('file' => TL_ROOT . '/' . $objFile->value, 'name' => $objFile->basename, 'mime' => $objFile->mime);
								}
							}
						}
					}
				}
			}

			if (!empty($attachments))
			{
				foreach ($attachments as $strFile => $varParams)
				{
					$strContent = file_get_contents($strFile, false);
					$infoEmail->attachFileFromString($strContent, $varParams['name'], $varParams['mime']);
				}
			}

			if (!empty($dirImages))
			{
				$infoEmail->imageDir = $dirImages;
			}
			if (!empty($messageText))
			{
				$infoEmail->text = $messageText;
			}
			if (!empty($messageHtml))
			{
				$infoEmail->html = $messageHtml;
			}

			// Send e-mail
			if (!empty($arrRecipient))
			{
				foreach ($arrRecipient as $recipient)
				{
					if (!empty($recipient))
					{
						$recipient = $this->replaceInsertTags($recipient, false);
						$recipient = str_replace(array('[', ']'), array('<', '>'), $recipient);
						$recipientName = '';
						if (strpos($recipient, '<') > 0)
						{
							preg_match('/(.*)?<(\S*)>/si', $recipient, $parts);
							$recipientName = trim($parts[1]);
							$recipient = (!empty($recipientName) ? $recipientName.' <'.$parts[2].'>' : $parts[2]);
						}
					}

					$infoEmail->sendTo($recipient);
				}
			}

		} // End information mail

		// redirect after frontend editing
		if ($blnFEedit)
		{
			if (!empty($strRedirectTo))
			{
				$strRed = preg_replace(array('/\/' . $this->strFormdataDetailsKey . '\/' . \Input::get($this->strFormdataDetailsKey) . '/i', '/' . $this->strFormdataDetailsKey . '=' . \Input::get($this->strFormdataDetailsKey) . '/i', '/act=edit/i'), array('','',''), $strUrl) . (!empty($strUrlParams) ? '?'.$strUrlParams : '');
				$this->redirect($strRed);
			}
		}

	}

	/*
	 * Callback function to display submitted data on confirmation page
	 */
	public function processConfirmationContent($strContent)
	{
		$arrSubmitted = $_SESSION['EFP']['FORMDATA'];

		// fix: after submission of normal single page form array $_SESSION['EFP']['FORMDATA'] is empty
		if (null === $arrSubmitted || (count($arrSubmitted) == 1 && array_keys($arrSubmitted) === array('_formId_')))
		{
			$arrSubmitted = $_SESSION['FORM_DATA'];
			$arrSubmitted['_formId_'] = $_SESSION['EFP']['FORMDATA'];
		}

		$blnProcess = false;
		if (preg_match('/\{\{form::/si', $strContent))
		{
			$blnProcess = true;
		}

		if (!empty($arrSubmitted) && isset($arrSubmitted['_formId_']) && $blnProcess)
		{
			$blnSkipEmpty = false;

			$objSkip = \Database::getInstance()->prepare("SELECT confirmationMailSkipEmpty FROM tl_form WHERE id=?")->execute($arrSubmitted['_formId_']);
			if ($objSkip->confirmationMailSkipEmpty == 1)
			{
				$blnSkipEmpty = true;
			}

			$this->import('Formdata');
			$arrFormFields = $this->Formdata->getFormfieldsAsArray(intval($arrSubmitted['_formId_']));

			preg_match('/<body[^>]*?>.*?<\/body>/si', $strContent, $arrMatch);

			if (!empty($arrMatch))
			{

				for ($m=0; $m < count($arrMatch); $m++)
				{
					$strTemp = $arrMatch[$m];
					$strTemp = preg_replace(array('/\{\{/', '/\}\}/'), array('__BRCL__', '__BRCR__'), $strTemp);
					$blnEval = $this->Formdata->replaceConditionTags($strTemp);

					// Replace tags
					$tags = array();
					preg_match_all('/__BRCL__.*?__BRCR__/si', $strTemp, $tags);

					// Replace tags of type {{form::<form field name>}}
					// .. {{form::fieldname?label=Label for this field: }}
					foreach ($tags[0] as $tag)
					{
						$elements = explode('::', preg_replace(array('/^__BRCL__/i', '/__BRCR__$/i'), array('',''), $tag));
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
									$arrTagParams = $this->Formdata->parseInsertTagParams($tag);
								}

								$arrField = $arrFormFields[$strKey];

								$strLabel = '';
								$strVal = '';
								if ($arrTagParams && strlen($arrTagParams['label']))
								{
									$strLabel = $arrTagParams['label'];
								}

								$strVal = $arrSubmitted[$strKey];
								if (is_array($strVal))
								{
									$strVal = implode(', ', $strVal);
								}

								if (strlen($strVal))
								{
									$strVal = nl2br($strVal);
								}

								if (!strlen($strVal) && $blnSkipEmpty)
								{
									$strLabel = '';
								}

								$strTemp = str_replace($tag, $strLabel . $strVal, $strTemp);
								break;
						}
					}

					$strTemp = preg_replace(array('/__BRCL__/', '/__BRCR__/'), array('{{', '}}'), $strTemp);

					// Eval the code
					if ($blnEval)
					{
						$strTemp = $this->Formdata->evalConditionTags($strTemp);
					}

					$strContent = str_replace($arrMatch[$m], $strTemp, $strContent);

				}
			}

		}

		return $strContent;

	}

}
