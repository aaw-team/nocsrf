<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\Hook;

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

use AawTeam\Nocsrf\Session\CsrfRegistry;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * UserAuthenticationHook
 */
final class UserAuthenticationHook
{
    /**
     * Automatically clear all tokens in session.
     *
     * @param array $params
     * @param AbstractUserAuthentication $userAuthentication
     */
    public static function logoffPreProcess(array $params, AbstractUserAuthentication $userAuthentication)
    {
        GeneralUtility::makeInstance(CsrfRegistry::class)->clearAll();
    }
}
