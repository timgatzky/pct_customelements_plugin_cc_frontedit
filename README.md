pct_customelements_plugins_cc_frontedit alias CC FrontEdit
================

About
-----
Brings the full front end editing experience to CustomCatalog.

Features
-------
+ All attributes supported including image, files, tags and even gallery selections
+ Slick and easy integratable in any running CustomCatalog. Using the ->widget() method for fields/attributes
+ Integrates directly to list and reader module and templates
+ All familiar backend features available such as: copy, delete, toggle visibility (green eye), cut and paste etc.
+ Multiple editing "Mehrere bearbeiten", (multiple delete, copy and paste, overwriting, editing)
+ Child-table support
+ Access levels for member groups and single members
+ Deep level rights system to restrict whole tables, entries, or single attributes
+ No backend login necessary. Even use popup windows or TinyMCEs directly in the front end
+ Full contao versioning support for entries

Demo
------------
[cc.feedit.premium-contao-themes.com](http://cc.feedit.premium-contao-themes.com)

Installation
------------
Copy the module folder to /system/modules and update the database. You might need to clear the internal cache as well.
The plugin brings two new templates:
+ mod_customcatalogedit.html5
+ customcatalog_default_edit.html5

Installation Contao 4.4.x
------------
Copy the module folder to /system/modules and update the database. Manually clear the internal cache (var/cache).
Once the module is installed it will create a config.yml (or append an existing one) in the /app/config folder on first load.
+ If the file has not been created automatically copy the config.yml file coming with this extension to the /app/config folder (or append your config.yml)

+ If you already use a config.yml, append these configurations:
‘‘‘
# contao.picker.builder::customcatalog_frontedit
services:
   contao.picker.builder:
      class: PCT\Contao\Picker\PickerBuilder
      arguments:
            - '@knp_menu.factory'
            - '@router'
            - '@request_stack'

# contao.picker.page_provider::customcatalog_frontedit
services:
   contao.picker.page_provider:
      class: PCT\Contao\Picker\PagePickerProvider

# contao.picker.file_provider::customcatalog_frontedit
services:
   contao.picker.file_provider:
      class: PCT\Contao\Picker\FilePickerProvider
‘‘‘

Activate CustomElement plugin
------------
Navigate to "My Content elements" / "Meine Inhaltselemente" > Plugin Management and enable the new plugin.

Usage
------------
The plugin brings two new methods to your CustomCatalog template.
+ `$entry->editable();`
+ `$entry->field('myAttribute')->widget();`
+ `$entry->field('myFilesAttribute')->uploadWidget( $arrSettings );`

The editable() methode callable for a CustomCatalog RowTemplate object (each entry is one of those) checks if the entry can be edited by the current user
The widget() method callable for a CustomCatalog TemplateAttribute (any attribute in a cc template file is one of those) generates the attributes formular field.

See the customcatalog_default_edit.html5 Template

Requirements
------------
Requires the pct_customelements alias CustomElements module in version 1.6.0 (or higher) and CustomCatalog in version 1.4.0 (or higher)
Best experience with the lastest version!

Optional settings for the upload widget
------------

`$arrSettings['uploadFolder']`
(string) set the path a folder inside contaos files folder. The files folder itself is restricted (default: $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['defaultUploadFolder'] = files/uploads defined in config.php)

`$arrSettings['useHomeDir']`
(boolean) upload to the front end member folder (overwrites the upload folder path)

`$arrSettings['doNotOverwrite']`
(boolean) overwrite files or not (default: true)

`$arrSettings['extensions']`
(array or string) an array or a commata list of file extensions allowed to be uploaded (default: Contaos system settings extension list)

`$arrSettings['createUploadFolder']`
(boolean) if set to true the upload folder will be created if it does not exist yet (default: false)
