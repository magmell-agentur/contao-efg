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
 * Class EfgFormGallery
 * based on ContentGallery by Leo Feyer
 *
 * Renders gallery with radio buttons
 * @copyright  Thomas Kuhn 2007
 * @author     Thomas Kuhn <th_kuhn@gmx.net>
 * @package    efg
 * @version    1.12.0
 */
class EfgFormGallery extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'form_efg_imageselect';

	protected $widget = null;

	/**
	 * Initialize the object
	 * @param object
	 * @return string
	 */
	public function __construct(Widget $widget, $arrConfig)
	{

		$this->widget = $widget;
		$this->import('Input');

		$this->multiSRC = $widget->efgMultiSRC; //  $arrConfig['efgMultiSRC'];
		$this->efgImageUseHomeDir = $widget->efgImageUseHomeDir;
		$this->size = $widget->efgImageSize; // $arrConfig['efgImageSize'];
		$this->fullsize = $widget->efgImageFullsize; // $arrConfig['efgImageFullsize'];
		$this->perRow = (intval($widget->efgImagePerRow) > 0) ? $widget->efgImagePerRow : 4;  // intval($arrConfig['efgImagePerRow'] > 0) ? $arrConfig['efgImagePerRow'] : 4;
		$this->perPage = 0;
		$this->imagemargin = $widget->efgImageMargin; // $arrConfig['efgImageMargin'];

		$this->arrData = $arrConfig;

	}


	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'multiSRC':
				$this->multiSRC = $varValue;
				break;

			case 'size':
				$this->size = $varValue;
				break;

			case 'perRow':
				$this->perRow = $varValue;
				break;

			case 'perPage':
				$this->perPage = $varValue;
				break;

			case 'imagemargin':
				$this->imagemargin = $varValue;
				break;

			case 'fullsize':
				$this->fullsize = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Return if there are no files
	 * @return string
	 */
	public function generate()
	{
		$this->multiSRC = deserialize($this->multiSRC);

		// Use the home directory of the current user as file source
		if ($this->efgImageUseHomeDir && FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');

			if ($this->User->assignDir && is_dir(TL_ROOT . '/' . $this->User->homeDir))
			{
				$this->multiSRC = array($this->User->homeDir);
			}
		}

		if (!is_array($this->multiSRC) || count($this->multiSRC) < 1)
		{
			return '';
		}
		return parent::generate();
	}



	/**
	 * Generate gallery
	 */
	protected function compile()
	{
		$images = array();
		$auxName = array();
		$auxDate = array();

		// Get all images
		foreach ($this->multiSRC as $file)
		{
			if (!is_dir(TL_ROOT . '/' . $file) && !file_exists(TL_ROOT . '/' . $file) || array_key_exists($file, $images))
			{
				continue;
			}

			// Single files
			if (is_file(TL_ROOT . '/' . $file))
			{
				$objFile = new File($file);
				$this->parseMetaFile(dirname($file), true);

				if ($objFile->isGdImage)
				{
					$images[$file] = array
					(
						'name' => $objFile->basename,
						'src' => $file,
						'alt' => (strlen($this->arrMeta[$objFile->basename][0]) ? $this->arrMeta[$objFile->basename][0] : ucfirst(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)))),
						'link' => (strlen($this->arrMeta[$objFile->basename][1]) ? $this->arrMeta[$objFile->basename][1] : ''),
						'caption' => (strlen($this->arrMeta[$objFile->basename][2]) ? $this->arrMeta[$objFile->basename][2] : '')
					);

					$auxName[] = $objFile->basename;
					$auxDate[] = $objFile->mtime;
				}

				continue;
			}

			$subfiles = scan(TL_ROOT . '/' . $file);
			$this->parseMetaFile($file);

			// Folders
			foreach ($subfiles as $subfile)
			{
				if (is_dir(TL_ROOT . '/' . $file . '/' . $subfile))
				{
					continue;
				}

				$objFile = new File($file . '/' . $subfile);

				if ($objFile->isGdImage)
				{
					$images[$file . '/' . $subfile] = array
					(
						'name' => $objFile->basename,
						'src' => $file . '/' . $subfile,
						'alt' => (strlen($this->arrMeta[$subfile][0]) ? $this->arrMeta[$subfile][0] : ucfirst(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)))),
						'link' => (strlen($this->arrMeta[$subfile][1]) ? $this->arrMeta[$subfile][1] : ''),
						'caption' => (strlen($this->arrMeta[$subfile][2]) ? $this->arrMeta[$subfile][2] : '')
					);

					$auxName[] = $objFile->basename;
					$auxDate[] = $objFile->mtime;
				}
			}
		}

		// Sort array
		switch ($this->sortBy)
		{
			default:
			case 'name_asc':
				array_multisort($images, SORT_ASC, $auxName);
				break;

			case 'name_desc':
				array_multisort($images, SORT_DESC, $auxName);
				break;

			case 'date_asc':
				array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
				break;

			case 'date_desc':
				array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
				break;

			case 'meta':
				$arrImages = array();
				foreach ($this->arrAux as $k)
				{
					if (strlen($k))
					{
						$arrImages[] = $images[$k];
					}
				}
				$images = $arrImages;
				break;
		}

		// Fullsize template
		if ($this->fullsize && TL_MODE == 'FE')
		{
			$this->strTemplate = 'form_efg_imageselect_fullsize';
			$this->Template = new FrontendTemplate($this->strTemplate);
		}

		$images = array_values($images);
		$total = count($images);
		$limit = $total;
		$offset = 0;

		// Pagination
		if ($this->perPage > 0)
		{
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$offset = ($page - 1) * $this->perPage;
			$limit = min($this->perPage + $offset, $total);

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}

		$size = deserialize($this->size);
		$arrMargin = deserialize($this->imagemargin);
		$margin = $this->generateMargin($arrMargin);
		$intWidth = floor((640 / $this->perRow) - $arrMargin['left'] - $arrMargin['right']);

		$this->Template->lightboxId = 'lb' . $this->id;
		$this->Template->fullsize = (TL_MODE == 'FE') ? true : false;

		$rowcount = 0;
		$colwidth = floor(100/$this->perRow);

		$body = array();

		// Rows
		for ($i=$offset; $i<$limit; $i=($i+$this->perRow))
		{
			$class_tr = '';

			if ($rowcount == 0)
			{
				$class_tr = ' row_first';
			}

			if (($i + $this->perRow) >= count($images))
			{
				$class_tr = ' row_last';
			}

			$class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

			// Columns
			for ($j=0; $j<$this->perRow; $j++)
			{
				$class_td = '';

				if ($j == 0)
				{
					$class_td = ' col_first';
				}

				if ($j == ($this->perRow - 1))
				{
					$class_td = ' col_last';
				}

				if (!is_array($images[($i+$j)]) || ($j+$i) >= $limit)
				{
					$body['row_' . $rowcount . $class_tr . $class_eo][$j]['hasImage'] = false;
					$body['row_' . $rowcount . $class_tr . $class_eo][$j]['class'] = 'col_'.$j . $class_td;

					continue;
				}

				$objFile = new File($images[($i+$j)]['src']);

				// Adjust image size in the back end
				if (TL_MODE == 'BE' && $objFile->width > $intWidth && ($size[0] > $intWidth || !$size[0]))
				{
					$size[0] = $intWidth;
					$size[1] = floor($intWidth * $objFile->height / $objFile->width);
				}

				$src = $this->getImage($this->urlEncode($images[($i+$j)]['src']), $size[0], $size[1]);

				if (($imgSize = @getimagesize(TL_ROOT . '/' . $src)) !== false)
				{
					$imgSize = ' ' . $imgSize[3];
				}

				$body['row_' . $rowcount . $class_tr . $class_eo][$j] = array
				(
					'hasImage' => true,
					'margin' => $margin,
					'href' => $images[($i+$j)]['src'],
					'width' => $objFile->width,
					'height' => $objFile->height,
					'colWidth' => $colwidth . '%',
					'class' => 'col_'.$j . $class_td,
					'alt' => htmlspecialchars($images[($i+$j)]['alt']),
					'link' => ((TL_MODE == 'BE') ? '' : $images[($i+$j)]['link']),
					'caption' => $images[($i+$j)]['caption'],
					'imgSize' => $imgSize,
					'src' => $src,
					'optId' => 'opt_' . $this->widget->id . '_' . ($i+$j),
					'optName' => $this->widget->name,
					'srcFile' => $images[($i+$j)]['src'],
					'checked' => ($this->widget->value == $images[($i+$j)]['src']) ? 'checked="checked"' : ''
				);
			}

			++$rowcount;
		}

		$this->Template->body = $body;
	}

}

?>