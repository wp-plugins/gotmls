=== Get Off Malicious Scripts (Anti-Malware) ===
Plugin Name: Get Off Malicious Scripts (Anti-Malware)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Tags: security, plugin, scan, remove, repair, malware, virus, recover, hacked, server, malicious, scripts, infection, timthumb, exploit, vulnerability
Version: 1.2.07.29
Stable tag: 1.2.07.29
Requires at least: 2.8
Tested up to: 3.4.1

This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and it helps you remove them.

== Description ==

Register this plugin and you will have access to new definitions of "known threats" and added features like automatic removal, plus patches for specific security threats and vulnerabilities. Updated definition files can be downloaded automatically within the admin once your Key is registered. Otherwise, this plugin just scans for the use of the eval function on your site (a potential threat, usually not harmful).

Updated July-29th

NOTICE: This plugin make use of a "phone home" feature to check for updates. This is not unlike what WordPress already does with all your plugins. It is an essential part of any worthwhile security plugin and it is here to let you know when there are new plugin and definition update available. If you're allergic to "phone home" scripts then don't use this plugin (or WordPress at all for that matter).

== Installation ==

1. Download and unzip the plugin into your WordPress plugins directory (usually `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' menu in your WordPress Admin.
1. Register on gotmls.net to have access to new definitions of "known threats" and added features like automatic removal and automatic security patches from your admin page.

== Frequently Asked Questions ==

= Why can't I automatically remove the "potential treats" in yellow? =

Many of these files may use eval and other powerful PHP function for perfectly legitimate reasons and removing that code from the files would likely cripple or even break your site so I have only enabled the Auto remove feature for "know threats".

= How do I know if any of the "potential treats" are dangerous? =

Click on the linked filename, then click each numbered link above the file content box to highlight the suspect code. If you cannot tell wheather or not the code is malicious just leave it alone or ask someone else to look at it for you. If you find that it is malicious please send me a copy of the file so that I can add it to the definitions file as a "known threat", then it can be automatically removed. If you want me to examine your files please consider making a donation.

= What if the scan gets stuck part way through? =

First just leave it for a while. If there are a lot of files on your server it could take quite a while and could sometimes appear to not be moving along at all even if it really is working. If, after a while, it still seems really stuck then make a note of what it was scanning when it stopped, then run it again. If it stops in the exact same place then you may want to try to figure out what file in that folder is causing it to hang or avoid scanning that folder all together. If you figure it out let me know what it was and I will try and make the program find it's own way around the problem.

= Why should I register? =

If you register on gotmls.net you will have access to new definitions of "known threats" and added features like automatic removal and patches for specific security threats and vulnerabilities. Otherwise, this plugin only scans for the use of the eval function on your site, it would still be your job to identify the good one from the bad one and remove them acoudingly. 

== Screenshots ==

1. The menu showing Anti-Malware.
2. An example scan that found some threats.

== Changelog ==

= 1.2.07.29 =
* Fixed return URL on Donate form.

= 1.2.07.28 =
* Added options to limit scan to specific folders.

= 1.2.07.20 =
* Fixed XSS vulnerability.

= 1.2.05.20 =
* Changed registration to allow for multiple sites/keys to be registered under one user/email.

= 1.2.05.04 =
* Fixed "Invalid Threat level" Error on default values for pre-registration scans.
* Changed auto-update path to update threat level array for all new definition updates.

= 1.2.04.24 =
* Fixed auto-update script to update scan level even if there is no new definitions.

= 1.2.04.09 =
* Added more info about registration to the readme file.
* Updated timthumb replacement patch to version 2.8.10 per WordPress.org plugins requirement.
* Fixed menu option placement to work just as well as a sub-menu under tools.

= 1.2.04.08 =
* Fixed option to exclude directories so that the scan would not get stuck if omitted.
* Added support for winblows servers using BACKSLASH directory structures.

= 1.2.04.04 =
* Fixed new definition updates to properly update the version number.
* Added option to exclude directories.

= 1.2.04.02 =
* Changed definition updates to write to the DB instead of a file.
* Added better messages about available updates.

= 1.2.04.01 =
* Fixed caching issue with downloading second definition file in Safari.
* Added more FAQs to the readme.
* Encoded registration url so your email address could be used as your username.

= 1.2.03.28 =
* Fixed registration form.

= 1.2.03.27 =
* Fixed some of the links on the settings page.

= 1.2.03.23 =
* First BETA versions available for WordPress.

== Upgrade Notice ==

= 1.2.07.29 =
Fixed return URL on Donate form.

= 1.2.07.28 =
Added options to limit scan to specific folders.

= 1.2.07.20 =
Fixed XSS vulnerability.

= 1.2.05.20 =
Changed registration to allow for multiple sites/keys to be registered under one user/email.

= 1.2.05.04 =
Fixed Threat Level error and changed auto-update path to update threat level array for all new definition updates.

= 1.2.04.24 =
Fixed auto-update script to update scan level even if there is no new definitions.

= 1.2.04.09 =
Added more info about registration to the readme file, Updated timthumb replacement patch to version 2.8.10, and fixed menu option placement.

= 1.2.04.08 =
Fixed option to exclude directories and added support for winblows servers using BACKSLASH directory structures.

= 1.2.04.04 =
Fixed new definition updates to properly update the version number and added option to exclude directories.

= 1.2.04.02 =
Changed definition updates to write to the DB instead of a file and added better messages about available updates.

= 1.2.04.01 =
Fixed caching issue with downloading second definition file in Safari and encoded registration url so your email address is your username.

= 1.2.03.28 =
Fixed registration form.

= 1.2.03.27 =
Fixed some of the links on the settings page.

= 1.2.03.23 =
First BETA versions available for WordPress.


