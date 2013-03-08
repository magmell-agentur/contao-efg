EFG - Extended From Generator for Contao Open Source CMS Changelog
==================================================================

Version 2.0.0 rc3 (2013-03-09)
------------------------------

### Added
Support extension 'cm_alternativeforms'

Version 2.0.0 rc2 (2013-02-23)
------------------------------

### Added
Support extension 'conditionalforms'

### Added
Add a CSS class to multipage forms

### Added
Added option to swap order of submit and back button of form field type
'Submit field and page break'

### Fixed
Date of formdata has been displayed as timestamp in backend formdata list view
'All results' / 'Feedback'

### Fixed
File attachments of confirmation email have been attached
to information (formatted text / html) email

### Fixed
Avoid warning message in runonce.php when trying to clear not existing cache

### Fixed
Added missing fallback to parent::__get() in Formdata::__get()

### Fixed
Replace inserttags in sender and sender name of confirmation mail

### Changed
Use method splitFriendlyName to parse email addresses

### Changed
Field 'Sorting value' (tl_formdata.sorting) can be edited in backend
