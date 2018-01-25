<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\Utility;

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
use AawTeam\Nocsrf\ViewHelpers\Form\CsrfTokenViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * NocsrfUtility
 */
final class NocsrfUtility
{
    /**
     * Returns true, when the passed request:
     *
     *   1. has the identifier and tokan as arguments and
     *   2. the arguments can be verified by CsrfRegistry
     *
     * Returns false otherwise.
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @return bool
     * @api
     */
    public static function validateExtbaseMvcRequest(\TYPO3\CMS\Extbase\Mvc\Request $request): bool
    {
        $isValid = false;
        if ($request->hasArgument(CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER)
            && $request->hasArgument(CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER)
        ) {
            /** @var CsrfRegistry $csrfRegistry */
            $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
            $identifier = $request->getArgument(CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER);
            $token = $request->getArgument(CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER);
            if($csrfRegistry->verifyToken($identifier, $token)) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * This method is not tested yet..
     *
     * @param string $postVar
     * @return boolean
     */
    private static function validateTYPO3PostVars(string $postVar = null): bool
    {
        $isValid = false;
        $post = GeneralUtility::_POST($postVar);
        if (isset($post[CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER])
            && isset($post[CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER])
        ) {
            /** @var CsrfRegistry $csrfRegistry */
            $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
            $identifier = $post[CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER];
            $token = $post[CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER];
            if($csrfRegistry->verifyToken($identifier, $token)) {
                $isValid = true;
            }
        }
        return $isValid;
    }
}
