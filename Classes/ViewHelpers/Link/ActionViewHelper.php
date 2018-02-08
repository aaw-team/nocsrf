<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\ViewHelpers\Link;

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
 * ActionViewHelper
 */
class ActionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper
{
    /**
     * @param string $action
     * @param array $arguments
     * @param string $controller
     * @param string $extensionName
     * @param string $pluginName
     * @param int $pageUid
     * @param int $pageType
     * @param bool $noCache
     * @param bool $noCacheHash
     * @param string $section
     * @param string $format
     * @param bool $linkAccessRestrictedPages
     * @param array $additionalParams
     * @param bool $absolute
     * @param bool $addQueryString
     * @param array $argumentsToBeExcludedFromQueryString
     * @param string $addQueryStringMethod
     * @return string
     * @see \TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper::render()
     */
    public function render($action = null, array $arguments = [], $controller = null, $extensionName = null, $pluginName = null, $pageUid = null, $pageType = 0, $noCache = false, $noCacheHash = false, $section = '', $format = '', $linkAccessRestrictedPages = false, array $additionalParams = [], $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = [], $addQueryStringMethod = null)
    {
        /** @var CsrfRegistry $csrfRegistry */
        $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
        list($identifier, $token) = $csrfRegistry->generateTokenAndIdentifier();

        // Add the token
        $arguments[CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER] = $identifier;
        $arguments[CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER] = $token;

        return parent::render($action, $arguments, $controller, $extensionName, $pluginName, $pageUid, $pageType, $noCache, $noCacheHash, $section, $format, $linkAccessRestrictedPages, $additionalParams, $absolute, $addQueryString, $argumentsToBeExcludedFromQueryString, $addQueryStringMethod);
    }
}
