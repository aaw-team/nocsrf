<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "nocsrf".
 *
 * Auto generated 24-01-2018 18:42
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'No-CSRF',
    'description' => 'Library for simple and secure CSRF protection in TYPO3 extensions',
    'category' => 'misc',
    'author' => 'Agentur am Wasser | Maeder & Partner AG',
    'author_email' => 'development@agenturamwasser.ch',
    'state' => 'alpha',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'author_company' => 'Agentur am Wasser | Maeder & Partner AG',
    'version' => '0.1.0-dev',
    'constraints' => array(
        'depends' => array(
            'php' => '7.0.0-7.1.999',
            'typo3' => '8.7.0-8.7.999',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
