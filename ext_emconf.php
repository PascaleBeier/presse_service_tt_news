<?php

########################################################################
# Extension Manager/Repository config file for ext "rss2_import".
#
# Auto generated 17-05-2011 22:21
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RSS2 Import Presse-Service',
	'description' => 'Importiert RSS2 Feeds aus dem Presse-Service in die Extension tt_news',
	'category' => 'be',
	'shy' => 0,
	'version' => '1.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Kasper Ligaard, Morten Tranberg Hansen & Mads Kirkedal Henriksen - Modifiziert und angepasst durch Andreas Wietfeld',
	'author_email' => 'info@ruhr-connect.de',
	'author_company' => 'ruhr-connect GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.11-0.0.0',
			'typo3' => '4.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:36:{s:9:"ChangeLog";s:4:"d6cd";s:20:"class.ext_update.php";s:4:"804e";s:33:"class.tx_rss2import_rssparser.php";s:4:"3286";s:33:"class.tx_rss2import_scheduler.php";s:4:"21f4";s:57:"class.tx_rss2import_scheduler_additionalfieldprovider.php";s:4:"211d";s:37:"class.tx_rss2import_tcemainprocdm.php";s:4:"4132";s:16:"ext_autoload.php";s:4:"2a22";s:21:"ext_conf_template.txt";s:4:"e4df";s:12:"ext_icon.gif";s:4:"c872";s:17:"ext_localconf.php";s:4:"9e93";s:15:"ext_php_api.dat";s:4:"4d79";s:14:"ext_tables.php";s:4:"c045";s:14:"ext_tables.sql";s:4:"e0a4";s:28:"icon_tx_rss2import_feeds.gif";s:4:"c872";s:23:"locallang_csh_feeds.xml";s:4:"3a94";s:16:"locallang_db.xml";s:4:"a7c3";s:7:"tca.php";s:4:"f171";s:19:"converters/utf8.inc";s:4:"1bbe";s:27:"converters/vcal-to-rss2.php";s:4:"20ed";s:14:"doc/manual.sxw";s:4:"0284";s:19:"doc/wizard_form.dat";s:4:"e5a3";s:20:"doc/wizard_form.html";s:4:"0a28";s:32:"formatters/user_AU_Buildings.php";s:4:"4c94";s:42:"formatters/user_AU_FixDescriptionField.php";s:4:"39e8";s:35:"mod1/class.tx_rss2import_helper.php";s:4:"2130";s:41:"mod1/class.tx_rss2import_notification.php";s:4:"36a9";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"5fd4";s:14:"mod1/index.php";s:4:"44fd";s:18:"mod1/locallang.xml";s:4:"e3de";s:22:"mod1/locallang_mod.xml";s:4:"ecb4";s:19:"mod1/moduleicon.gif";s:4:"c872";s:23:"mod1/moduleicon_alt.gif";s:4:"12aa";s:23:"mod1/moduleicon_org.gif";s:4:"8074";s:22:"mod1/rss2import-be.css";s:4:"56dd";s:40:"scheduler/class.tx_rss2import_import.php";s:4:"3f1d";}',
	'suggests' => array(
	),
);

?>