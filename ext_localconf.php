<?php
/*
 * Copyright 2018 Agentur am Wasser | Maeder & Partner AG
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

defined ('TYPO3_MODE') or die ('Access denied.');

call_user_func(function($extKey){
    // Add the dependencies when extension was not installed with composer
    if (!class_exists(\ParagonIE\ConstantTime\Encoding::class)) {
        require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/PHP/vendor/autoload.php';
    }

    // Register logoff_pre_processing hook
    // @todo: is this really needed?
    //$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][$extKey] = \AawTeam\Nocsrf\Hook\UserAuthenticationHook::class . '->logoffPreProcess';
}, 'nocsrf');
