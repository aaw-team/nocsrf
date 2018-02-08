<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\ViewHelpers\Uri;

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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ActionViewHelper
 */
class ActionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ActionViewHelper
{
    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var CsrfRegistry $csrfRegistry */
        $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
        list($identifier, $token) = $csrfRegistry->generateTokenAndIdentifier();

        // Add the token
        $arguments['arguments'][CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER] = $identifier;
        $arguments['arguments'][CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER] = $token;

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
