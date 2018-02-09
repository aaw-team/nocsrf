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
     *   2. @csrfvalidation ifHasAnyArgument([arg1, arg2 [, argN]])
     *
     *      Validate every request that contains at least one argument
     *      which ist specified with @param. Optionally, the list to test
     *      against can be specified (arg1, arg2 [, argN]). Note that the
     *      list must contain either zero or more than one argument!
     *
     *   2. @csrfvalidation ifHasArguments(arg1 [, arg2 [, argN]])
     *
     *      Validate every request that contains all of the arguments
     *      (specified with @param) from the list
     *      (arg1 [, arg2 [, argN]]).
     *
     * @param string $className
     * @param string $methodName
     * @param array $argumentNamesInRequest
     * @throws \LogicException
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
                        $argumentstoCheck = [];
                        if ($specification !== 'ifHasAnyArgument' && preg_match('/^ifHasAnyArgument\\s*\\(\\s*([^\\)]+)\\s*\\)$/i', $specification, $matches)) {
                            $argumentstoCheck = self::createArgumentNamesArrayFromString($matches[1], $className, $methodName);
                            if (count($argumentstoCheck) == 1) {
                                throw new \LogicException('Invalid @csrfvalidation annotation: ifHasAnyArgument() must have either zero or more than one arguments in ' . $className . '->' . $methodName . '(): "' . htmlspecialchars($specification) . '"', 1518174237);
                            }
                        }
                        if (empty($argumentstoCheck)) {
                            $argumentstoCheck = array_keys(self::getExtbaseReflectionService()->getMethodParameters($className, $methodName));
                        }
                        foreach ($argumentstoCheck as $argumentName) {
                            if (in_array($argumentName, $argumentNamesInRequest)) {
                                $runValidation = true;
                                break 2;
                            }
                        }
                    } elseif (preg_match('/^ifHasArguments\\s*\\(([^\\)]*)\\s*\\)$/i', $specification, $matches)) {
                        $argumentNames = self::createArgumentNamesArrayFromString($matches[1], $className, $methodName);
                        if (empty($argumentNames)) {
                            throw new \LogicException('Invalid @csrfvalidation annotation: ifHasArguments() expects at least one argument in ' . $className . '->' . $methodName . '(): "' . htmlspecialchars($specification) . '"', 1516906446);
                        }
                        foreach ($argumentNames as $argumentName) {
                            if (!in_array($argumentName, $argumentNamesInRequest)) {
                                break 2;
                            }
                        }
                        $runValidation = true;
                    } else {
                        throw new \LogicException('Invalid @csrfvalidation annotation in ' . $className . '->' . $methodName . '(): "' . htmlspecialchars($specification) . '"', 1516906498);
                    }
                }
            }
        }
        return $runValidation;
    }

    /**
     * @param string $input
     * @param string $className
     * @param string $methodName
     * @throws \LogicException
     * @return array
     */
    protected static function createArgumentNamesArrayFromString(string $input, string $className, string $methodName): array
    {
        $argumentNames = GeneralUtility::trimExplode(',', $input, true);
        array_walk($argumentNames, function(&$value, $key) {
            $value = ltrim($value, '$');
        });
        $methodParameterNames = array_keys(self::getExtbaseReflectionService()->getMethodParameters($className, $methodName));
        if ($diff = array_diff($argumentNames, $methodParameterNames)) {
            throw new \LogicException('Invalid @csrfvalidation annotation: you specified arguments ("' . implode('", "', array_map('htmlspecialchars', $diff)) . '") that are not declared with @param in ' . $className . '->' . $methodName . '()', 1518173081);
        }
        return $argumentNames;
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
