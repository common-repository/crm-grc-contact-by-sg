=== GRC Contact API for Sendinblue ===
Contributors: sgendt
Tags: GRC Contact, Sendinblue, API
Donate link: https://fr.tipeee.com/about
Requires at least: 4.9
Tested up to: 5.6
Requires PHP: 7.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The GRC Contact API for Sendinblue plugin allows you to send data from a Sendinblue form to create a contact in the CRM GRC Contact.

== Description ==
With the GRC Contact API for Sendinblue plugin, you can intercept the data sent by a form created with the official Sendinblue plugin for Wordpress in order to send them via the CRM GRC Contact API.

Any Internet user who has filled in a form on your Wordpress website will create a contact in your CRM GRC Contact without a line of code.

The plugin is compatible with all fields of a company, of a contact and their custom fields.

**Get your API account key and password, let's go!**



== Installation ==
This section describes how to install the plugin and get it working.

The steps are :

1. [Follow this guide to install the plugin](https://wordpress.org/support/article/managing-plugins/#installing-plugins)
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to GRC Contact > Configuration then set your API account key and password
1. Create your first API gateway, go to GRC Contact > New API
1. Once your first gateway has been created, go to GRC Contact> APIs, then retrieve the mapping code
1. Copy and paste the mapping code into the desired Sendinblue form

**How to create an API gateway ?**

The purpose of the API gateway is to match the fields of your Sendinblue form with those of the GRC Contact API. For this, you must have already created a form with the [Sendinblue plugin](https://fr.wordpress.org/plugins/mailin/)

**Inside the API creation page you can find two major sections, one for companies and another for contacts. Note that it is not possible to create a contact without creating a company.**

These sections are composed of three parts:
* The first is the list of fields of the API GRC Contact
* The second corresponds to the fields of your Sendinblue form that you want to match
* The third, gives you the possibility to set a default value. This can be used if the Internet user hasn't filled in an optional field or if you wish to insert a value in GRC Contact without there being a correspondence with a Sendinblue field, for example the origin of the contact "Website - documentation request"

**The last section allows you to match the fields of the Sendinblue form with your custom fields.**

For this the operation is somewhat different :
* On the first line, you must list the name of the custom fields you want to pass separated by a pipe character |
* On the second line, you must list the name of the fields of the Sendinblue form that you want to match separated by a pipe character |, in the same order as the first line

**Debugging**

The GRC Contact API for Sendinblue plugin provide two pages to follow the success and error API message.
Go to GRC *Contact> Logs API success* or to *GRC Contact> Logs API error*

**Support**

If you have any issues or if you would like help installing contact me : [https://gendt.fr](https://gendt.fr)

== Screenshots ==
1. Plugin configuration
2. API Gateway creation
3. Insertion of mapping code


== Changelog ==

= 1.0.3 =
* Fix some file names.

= 1.0.2 =
* Remove Carbon librairy and usage.

= 1.0.1 =
* Remove unused dependencies and images to optimized the plugin size.