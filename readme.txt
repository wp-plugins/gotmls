=== Get Off Malicious Scripts (Anti-Malware) ===
Plugin Name: Get Off Malicious Scripts (Anti-Malware)
Plugin URI: http://gotmls.net/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/anti-malware/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE
Tags: plugin, security, virus, malware, scan, repair, recover
Version: 1.2.04.02
Stable tag: 1.2.04.02
Requires at least: 2.6
Tested up to: 3.3.1

This Anti-Virus/Anti-Malware plugin searches for Malware and other Virus like threats and vulnerabilities on your server and it helps you remove them.

== Description ==

It is almost impossible to detect every possible Malware threats, therefor this scanner may only find specific security threats and vulnerabilities that were know by me at the time of the latest release. Updated definition files can be downloaded within this plugin.

Updated Apr-5th 

== Installation ==

1. Download and unzip the plugin into your WordPress plugins directory (usually `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' menu in your WordPress Admin.

== Frequently Asked Questions ==

= Why can't I automatically remove the "potential treats" in yellow? =

Many of these files may use eval and other powerful PHP function for perfectly legitimate reasons and removing that code from the files would likely cripple or even break your site so I have only enabled the Auto remove feature for "know threats".

= How do I know if any of the "potential treats" are dangerous? =

Click on the linked filename, then click each numbered link above the file content box to highlight the suspect code. If you cannot tell wheather or not the code is malicious just leave it alone or ask someone else to look at it for you. If you find that it is malicious please send me a copy of the file so that I can add it to the definitions file as a "known threat", then it can be automatically removed. If you want me to examine your files please consider making a donation.

= What if the scan gets stuck part way through? =

First just leave it for a while. If there are a lot of files on your server it could take quite a while and could sometimes appear to not be moving along at all even if it really is working. If, after a while, it still seems really stuck then make a note of what it was scanning when it stopped, then run it again. If it stops in the exact same place then you may want to try to figure out what file in that folder is causing it to hang or avoid scanning that folder all together. If you figure it out let me know what it was and I will try and make the program find it's own way around the problem.

== Screenshots ==

1. The menu showing Anti-Malware.
2. An example scan that found some threats.

== Changelog ==

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

= 1.2.04.02 =
Changed definition updates to write to the DB instead of a file and added better messages about available updates.

= 1.2.04.01 =
* Fixed caching issue with downloading second definition file in Safari and encoded registration url so your email address is your username.

= 1.2.03.28 =
Fixed registration form.

= 1.2.03.27 =
Fixed some of the links on the settings page.

= 1.2.03.23 =
First BETA versions available for WordPress.

