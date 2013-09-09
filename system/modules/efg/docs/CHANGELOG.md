EFG - Extended From Generator for Contao Open Source CMS Changelog
==================================================================

Version 2.1.0 stable (2013-09-XX)
---------------------------------

### Changed
Adapted backend file selector to Contao 3.1

### Fixed
Handle field labels containing % (see #25)

### Fixed
Do not strip tags from listing condition (see #28)

### Fixed
Details view of frontend module 'listing form data' did not work
on page with alias 'index'

### Fixed
File uploads have not been validated on last page of a multi-page form

### Fixed
Form fields of type 'Select menu (DB)', 'Checkbox menu (DB)' and 'Radio button menu (DB)'
did not work if using form data as source table (see #26)


Version 2.0.1 stable (2013-03-17)
---------------------------------

### Fixed
Editing or deleting form data in module 'Listing form data' did not work
when using foreign tables in 'condition'

### Fixed
Backend filter menu did not keep search field after performing search

### Fixed
Frontend modul 'Listing form data' did not show '0' values


Version 2.0.0 stable (2013-03-09)
------------------------------

### Added
Support extension 'cm_alternativeforms'

### Fixed
Using foreign tables in 'condition' of module 'Listing form data'
could result in invalid SQL statement


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
