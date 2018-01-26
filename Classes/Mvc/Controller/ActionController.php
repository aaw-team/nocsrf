<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\Mvc\Controller;

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

use AawTeam\Nocsrf\Utility\NocsrfUtility;

/**
 * ActionController
 */
class ActionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Extend parent method: check the csrf token in request before
     * continuing. Use the @csrfvalidation annotations to decide whether
     * this is needed or not.
     *
     * The request handling of the extbase action controller is
     * interrupted at this point (ie. with this method) because:
     *
     *   1. Exit the process (early) before any argument gets processed
     *      (eg. argument mapping/validation) if the validation fails.
     *
     *   2. If a public API method such as initializeAction() would be
     *      used, the "automatic" request validation could be overwritten
     *      by child classes, when they do not bother to explicitly call
     *      the parent method (which is not required when extending
     *      extbase default ActionController).
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController::mapRequestArgumentsToControllerArguments()
     * @see \AawTeam\Nocsrf\Utility\NocsrfUtility::shouldMethodRunRequestValidation()
     */
    protected function mapRequestArgumentsToControllerArguments()
    {
        // Handle web requests only
        if ($this->request instanceof \TYPO3\CMS\Extbase\Mvc\Web\Request
            && NocsrfUtility::shouldMethodRunRequestValidation(get_class($this), $this->actionMethodName, array_keys($this->request->getArguments()))
            && NocsrfUtility::validateExtbaseMvcRequest($this->request) !== true
        ) {
            $this->response->setContent('Security alert: CSRF token validation failed');
            throw new \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException();
        }

        return parent::mapRequestArgumentsToControllerArguments();
    }
}
