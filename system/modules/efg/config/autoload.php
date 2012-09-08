<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package Efg
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Efg',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'Efg\Formdata'                     => 'system/modules/efg/classes/Formdata.php',
	'Efg\FormdataComments'             => 'system/modules/efg/classes/FormdataComments.php',

	// Drivers
	'Efg\DC_Formdata'                  => 'system/modules/efg/drivers/DC_Formdata.php',

	// Elements

	// Forms
	'Efg\EfgFormGallery'               => 'system/modules/efg/forms/EfgFormGallery.php',
	'Efg\EfgFormImageSelect'           => 'system/modules/efg/forms/EfgFormImageSelect.php',
	'Efg\EfgFormLookupCheckbox'        => 'system/modules/efg/forms/EfgFormLookupCheckbox.php',
	'Efg\EfgFormLookupRadio'           => 'system/modules/efg/forms/EfgFormLookupRadio.php',
	'Efg\EfgFormLookupSelectMenu'      => 'system/modules/efg/forms/EfgFormLookupSelectMenu.php',
	'Efg\EfgFormPaginator'             => 'system/modules/efg/forms/EfgFormPaginator.php',
	'Efg\ExtendedForm'                 => 'system/modules/efg/forms/ExtendedForm.php',

	// Models

	// Modules
	'Efg\ModuleFormdataListing'        => 'system/modules/efg/modules/ModuleFormdataListing.php',

	// Pages

	// Widgets
	'Efg\EfgLookupOptionWizard'        => 'system/modules/efg/widgets/EfgLookupOptionWizard.php',

//TODO
	// Others
	'Efg\Efp'                          => 'system/modules/efg/Efp.php',
	'Efg\ModuleFormdata'               => 'system/modules/efg/ModuleFormdata.php',


));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'analytics_google'    => 'system/modules/core/templates',
	'analytics_piwik'     => 'system/modules/core/templates',
	'be_changelog'        => 'system/modules/core/templates',
	'be_confirm'          => 'system/modules/core/templates',
	'be_diff'             => 'system/modules/core/templates',
	'be_error'            => 'system/modules/core/templates',
	'be_files'            => 'system/modules/core/templates',
	'be_help'             => 'system/modules/core/templates',
	'be_install'          => 'system/modules/core/templates',
	'be_live_update'      => 'system/modules/core/templates',
	'be_login'            => 'system/modules/core/templates',
	'be_main'             => 'system/modules/core/templates',
	'be_maintenance'      => 'system/modules/core/templates',
	'be_navigation'       => 'system/modules/core/templates',
	'be_password'         => 'system/modules/core/templates',
	'be_picker'           => 'system/modules/core/templates',
	'be_popup'            => 'system/modules/core/templates',
	'be_preview'          => 'system/modules/core/templates',
	'be_purge_data'       => 'system/modules/core/templates',
	'be_rebuild_index'    => 'system/modules/core/templates',
	'be_referer'          => 'system/modules/core/templates',
	'be_switch'           => 'system/modules/core/templates',
	'be_welcome'          => 'system/modules/core/templates',
	'be_widget'           => 'system/modules/core/templates',
	'be_widget_chk'       => 'system/modules/core/templates',
	'be_widget_pw'        => 'system/modules/core/templates',
	'be_widget_rdo'       => 'system/modules/core/templates',
	'be_wildcard'         => 'system/modules/core/templates',
	'ce_accordion'        => 'system/modules/core/templates',
	'ce_accordion_start'  => 'system/modules/core/templates',
	'ce_accordion_stop'   => 'system/modules/core/templates',
	'ce_code'             => 'system/modules/core/templates',
	'ce_download'         => 'system/modules/core/templates',
	'ce_downloads'        => 'system/modules/core/templates',
	'ce_gallery'          => 'system/modules/core/templates',
	'ce_headline'         => 'system/modules/core/templates',
	'ce_html'             => 'system/modules/core/templates',
	'ce_hyperlink'        => 'system/modules/core/templates',
	'ce_hyperlink_image'  => 'system/modules/core/templates',
	'ce_image'            => 'system/modules/core/templates',
	'ce_list'             => 'system/modules/core/templates',
	'ce_player'           => 'system/modules/core/templates',
	'ce_table'            => 'system/modules/core/templates',
	'ce_teaser'           => 'system/modules/core/templates',
	'ce_text'             => 'system/modules/core/templates',
	'ce_toplink'          => 'system/modules/core/templates',
	'fe_page'             => 'system/modules/core/templates',
	'form'                => 'system/modules/core/templates',
	'form_captcha'        => 'system/modules/core/templates',
	'form_checkbox'       => 'system/modules/core/templates',
	'form_explanation'    => 'system/modules/core/templates',
	'form_headline'       => 'system/modules/core/templates',
	'form_hidden'         => 'system/modules/core/templates',
	'form_html'           => 'system/modules/core/templates',
	'form_message'        => 'system/modules/core/templates',
	'form_password'       => 'system/modules/core/templates',
	'form_radio'          => 'system/modules/core/templates',
	'form_submit'         => 'system/modules/core/templates',
	'form_widget'         => 'system/modules/core/templates',
	'form_xml'            => 'system/modules/core/templates',
	'gallery_default'     => 'system/modules/core/templates',
	'j_accordion'         => 'system/modules/core/templates',
	'j_colorbox'          => 'system/modules/core/templates',
	'j_mediaelement'      => 'system/modules/core/templates',
	'mail_default'        => 'system/modules/core/templates',
	'member_default'      => 'system/modules/core/templates',
	'member_grouped'      => 'system/modules/core/templates',
	'mod_article'         => 'system/modules/core/templates',
	'mod_article_list'    => 'system/modules/core/templates',
	'mod_article_nav'     => 'system/modules/core/templates',
	'mod_article_plain'   => 'system/modules/core/templates',
	'mod_article_teaser'  => 'system/modules/core/templates',
	'mod_booknav'         => 'system/modules/core/templates',
	'mod_breadcrumb'      => 'system/modules/core/templates',
	'mod_flash'           => 'system/modules/core/templates',
	'mod_html'            => 'system/modules/core/templates',
	'mod_login_1cl'       => 'system/modules/core/templates',
	'mod_login_2cl'       => 'system/modules/core/templates',
	'mod_logout_1cl'      => 'system/modules/core/templates',
	'mod_logout_2cl'      => 'system/modules/core/templates',
	'mod_message'         => 'system/modules/core/templates',
	'mod_navigation'      => 'system/modules/core/templates',
	'mod_password'        => 'system/modules/core/templates',
	'mod_quicklink'       => 'system/modules/core/templates',
	'mod_quicknav'        => 'system/modules/core/templates',
	'mod_random_image'    => 'system/modules/core/templates',
	'mod_search'          => 'system/modules/core/templates',
	'mod_search_advanced' => 'system/modules/core/templates',
	'mod_search_simple'   => 'system/modules/core/templates',
	'mod_sitemap'         => 'system/modules/core/templates',
	'moo_accordion'       => 'system/modules/core/templates',
	'moo_chosen'          => 'system/modules/core/templates',
	'moo_mediabox'        => 'system/modules/core/templates',
	'moo_mediaelement'    => 'system/modules/core/templates',
	'moo_slimbox'         => 'system/modules/core/templates',
	'nav_default'         => 'system/modules/core/templates',
	'pagination'          => 'system/modules/core/templates',
	'rss_default'         => 'system/modules/core/templates',
	'rss_items_only'      => 'system/modules/core/templates',
	'search_default'      => 'system/modules/core/templates',
));
