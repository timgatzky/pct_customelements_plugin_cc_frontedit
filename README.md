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
+ All familiar backend features available such as: copy, delete, toggle visibility (green eye). And also multiple editing
+ Access levels for member groups and single members
+ Deep level rights system to restrict whole tables, entries, or single attributes
+ No backend login nessessary. Even use popup windows or TinyMCEs directly in the frontend
+ Full versioning support for entries

Demo
------------
[cc.premium-contao-themes.com/feedit](http://cc.premium-contao-themes.com/feedit/)

Installation
------------
Copy the module folder to /system/modules and update the database. You might need to clear the internal cache as well.
The plugin brings two new templates:
+ mod_customcatalogedit.html5
+ customcatalog_default_edit.html5

Activate CustomElement plugin
------------
Navigate to "My Content elements" "Meine Inhaltselemente" > Plugin Management and enable the new plugin.

Usage
------------
The plugin brings two new methods to your CustomCatalog template.
+ $entry->editable(); 
+ $entry->field('myAttribute')->widget();

The editable() methode callable for a CustomCatalog RowTemplate object (each entry is one of those) checks if the entry can be edited by the current user
The widget() method callable for a CustomCatalog TemplateAttribute (any attribute in a cc template file is one of those) generates the attributes formular field.

See the customcatalog_default_edit.html5 Template

Requirements
------------
Requires the pct_customelements alias CustomElements module in version 1.6.0 (or higher) and CustomCatalog in version 1.4.0 (or higher)
Best experience with the lastest version!
