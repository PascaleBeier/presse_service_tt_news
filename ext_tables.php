<?php

defined("TYPO3_MODE") || die ("Access denied.");

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import', 'vendor/autoload.php');

if (TYPO3_MODE === "BE") {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'tools',
        'txrss2importM1',
        '',
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rss2_import', 'mod1/'),
        [
            'access'     => 'user,group',
            'script'     => '_DISPATCH',
            'name'       => 'tools_txrss2importM1',
            'vendorName' => 'RuhrConnect\Rss2Import',
            'labels'     => [
                'tabs_images' => [
                    'tab' => 'EXT:rss2_import/Resources/Public/Images/moduleicon.gif'
                ],
                'll_ref'      => 'LLL:EXT:rss2_import/Resources/Private/Language/locallang_mod.xml'
            ]
        ]
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_rss2import_feeds',
    'EXT:rss2_import/Resources/Private/Language/locallang_csh_feeds.xml'
);
