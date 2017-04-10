<?php

$EM_CONF['rss2_import'] = [
    'title' => 'RSS2 Import Presse-Service',
    'description' => 'Importiert RSS2 Feeds aus dem Presse-Service in die Extension tt_news',
    'module' => 'mod1',
    'state' => 'beta',
    'version' => '7.0.5',
    'author' => 'Kasper Ligaard, Morten Tranberg Hansen, Mads Kirkedal Henriksen, Andreas Wietfeld und Pascale Beier',
    'author_email' => 'info@ruhr-connect.de',
    'author_company' => 'ruhr-connect GmbH',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.1.99',
            'typo3' => '7.2.0-7.99.99',
            'tt_news' => '7.6.0-7.99.99',
            'scheduler' => '7.6.0-7.99.99',
        ],
        'conflicts' => []
    ],
];
