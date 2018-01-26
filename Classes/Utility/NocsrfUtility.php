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
     * This method analyzes the @csrfvalidation annotation of a method
     * and then decides, whether a request validation should be run.
     *
     * The @csrfvalidation annotation adopts the "syntax-scheme" known
     * from extbase @validation annotation. It can be used in three
     * different ways:
     *
     *   1. @csrfvalidation
     *
     *      Validate every request to the annotated method.
     *
     *   2. @csrfvalidation ifHasAnyArgument
     *
     *      Validate every request that contains at least one argument
     *      which ist specified with @param.
     *
     *   2. @csrfvalidation ifHasArguments(arg1 [, arg2 [, argN]])
     *
     *      Validate every request that contains at least one argument
     *      from the list (arg1 [, arg2 [, argN]]) which ist specified
     *      with @param.
     *
     * @param string $className
     * @param string $methodName
     * @param array $argumentNamesInRequest
     * @throws \RuntimeException
     * @return bool
     * @api
     */
    public static function shouldMethodRunRequestValidation(string $className, string $methodName, array $argumentNamesInRequest): bool
    {
        $runValidation = false;
        // Check for the @csrfvalidation annotation of the actionMethod
        $methodTagsValues = self::getExtbaseReflectionService()->getMethodTagsValues($className, $methodName);
        if (isset($methodTagsValues['csrfvalidation'])) {
            $specifications = $methodTagsValues['csrfvalidation'];
            if (empty($specifications)) {
                $runValidation = true;
            } else {
                foreach ($specifications as $specification) {
                    $matches = [];
                    if (stripos($specification, 'ifHasAnyArgument') === 0) {
                        foreach (self::getExtbaseReflectionService()->getMethodParameters($className, $methodName) as $argumentName => $unused) {
                            if (in_array($argumentName, $argumentNamesInRequest)) {
                                $runValidation = true;
                                break 2;
                            }
                        }
                    } elseif (preg_match('/^ifHasArguments\\s*\\(\\s*([^\\)]+)\\s*\\)$/i', $specification, $matches)) {
                        $argumentNames = GeneralUtility::trimExplode(',', $matches[1], true);
                        if (empty($argumentNames)) {
                            throw new \RuntimeException('Invalid @csrfvalidation annotation in ' . $className . '->' . $methodName . '(): "' . $specification . '"', 1516906446);
                        }
                        array_walk($argumentNames, function(&$value, $key) {
                            if ($value[0] === '$') {
                                $value = substr($value, 1);
                            }
                        });
                        foreach ($argumentNames as $argumentName) {
                            if (in_array($argumentName, $argumentNamesInRequest)) {
                                $runValidation = true;
                                break 2;
                            }
                        }
                    } else {
                        throw new \RuntimeException('Invalid @csrfvalidation annotation in ' . $className . '->' . $methodName . '(): "' . $specification . '"', 1516906498);
                    }
                }
            }
        }
        return $runValidation;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Reflection\ReflectionService
     */
    protected static function getExtbaseReflectionService(): \TYPO3\CMS\Extbase\Reflection\ReflectionService
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
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
