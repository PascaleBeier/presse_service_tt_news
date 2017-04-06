<?php

$EM_CONF[$_EXTKEY] = array(
    'title'          => 'RSS2 Import Presse-Service',
    'description'    => 'Importiert RSS2 Feeds aus dem Presse-Service in die Extension tt_news',
    'module'         => 'mod1',
    'state'          => 'beta',
    'version'        => '6.0.1',
    'author'         => 'Kasper Ligaard, Morten Tranberg Hansen, Mads Kirkedal Henriksen, Andreas Wietfeld und Pascale Beier',
    'author_email'   => 'info@ruhr-connect.de',
    'author_company' => 'ruhr-connect GmbH',
    'constraints'    => array(
        'depends'   => array(
            'php'   => '5.2.11-5.6.99',
            'typo3' => '6.2.0-6.99.99',
        ),
        'conflicts' => array(),
        'suggests'  => array(
            'tt_news'   => '3.0.0-3.99.99',
            'scheduler' => '6.2.0-6.99.99',
        ),
    ),
);

?>