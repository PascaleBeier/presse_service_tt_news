<?php

// Make instance of Script Object Back-End (SOBE):
$GLOBALS['SOBE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\RuhrConnect\Rss2Import\Controller\ModuleController::class);
$GLOBALS['SOBE']->init();
$GLOBALS['SOBE']->checkExtObj();
$GLOBALS['SOBE']->main();
$GLOBALS['SOBE']->printContent();