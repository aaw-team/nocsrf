<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\Session;

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

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Binary;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * CsrfRegistry
 */
final class CsrfRegistry
{
    const SESSION_IDENTIFIER = 'NOCSRF';
    const MAX_TOKENS_IN_SESSION = 25;

    const HMAC_ALGO = 'sha256';
    const HMAC_LENGTH = 64;

    const TOKEN_BYTES = 32;
    const TOKEN_IDENTIFIER_BYTES = 18;
    const TOKEN_LIFETIME = 1800;

    /**
     * @var AbstractUserAuthentication
     */
    private $userAuthentication;

    /**
     * @throws \RuntimeException
     * @return void
     */
    public function __construct()
    {
        if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_FE) && ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) && ($GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication) && $GLOBALS['TSFE']->fe_user->user['uid'] > 0) {
            $this->userAuthentication = $GLOBALS['TSFE']->fe_user;
        } elseif ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_BE) && ($GLOBALS['BE_USER'] instanceof BackendUserAuthentication) && $GLOBALS['BE_USER']->user['uid'] > 0) {
            $this->userAuthentication = $GLOBALS['BE_USER'];
        } else {
            throw new \RuntimeException('Invalid environment');
        }
    }

    /**
     * @return void
     */
    public function clearAll()
    {
        $this->storeSessionData([]);
    }

    /**
     * Returns a new generated token and it's identifier as array:
     *
     * Array
     * (
     *     [0] => identifier
     *     [1] => token
     * )
     *
     * Use it like this:
     *
     *   list($identifier, $token) = CsrfRegistry::generateTokenAndIdentifier();
     *
     * @return string[]
     * @api
     */
    public function generateTokenAndIdentifier(): array
    {
        $identifier = Base64UrlSafe::encode(random_bytes(self::TOKEN_IDENTIFIER_BYTES));
        $token = $this->generateToken();
        $this->storeToken($identifier, $token);
        return [$identifier, $token['token']];
    }

    /**
     * Verifies a token.
     *
     * @param string $identifier
     * @param string $tokenFromUserInput
     * @return boolean
     * @api
     */
    public function verifyToken(string $identifier, string $tokenFromUserInput): bool
    {
        $sessionData = $this->getSessionData();
        if (empty($sessionData)) {
            // Session data is empty
            return false;
        } elseif (!isset($sessionData[$identifier])) {
            // No such identifier in session data
            return false;
        }

        // Retrieve token from session data
        $token = $sessionData[$identifier];

        // Remove token from session data
        unset($sessionData[$identifier]);
        $this->storeSessionData($sessionData);

        // Check token
        if (!$this->isValidToken($token)) {
            return false;
        }

        return \hash_equals($token['token'], $tokenFromUserInput);
    }

    /**
     * @return array
     */
    private function generateToken(): array
    {
        $token = [
            'token' => Base64UrlSafe::encode(random_bytes(self::TOKEN_BYTES)),
            'crdate' => time(), // Do not use $GLOBALS['EXEC_TIME'] here, it is rounded to 60 seconds and can break accurate ordering, see storeToken()
        ];
        return $token;
    }

    /**
     * @param string $identifier
     * @param array $token
     */
    private function storeToken(string $identifier, array $token)
    {
        $sessionData = $this->getSessionData();
        // Remove invalid/outdated tokens
        $sessionData = array_filter($sessionData, [$this, 'isValidToken']);
        $sessionData[$identifier] = $token;

        // Remove exceeding tokens from session
        if (count($sessionData) > self::MAX_TOKENS_IN_SESSION) {
            // Sort oldest to newst
            uasort($sessionData, function($a, $b) {
                return ($a['crdate'] <=> $b['crdate']);
            });
            // Remove superfluous tokens from beginning of the array
            $sessionData = array_slice($sessionData, count($sessionData) - self::MAX_TOKENS_IN_SESSION);
        }
        $this->storeSessionData($sessionData);
    }

    /**
     * @param array $sessionData
     */
    private function storeSessionData(array $sessionData)
    {
        if (empty($sessionData)) {
            $sessionDataString = '';
        } else {
            // Authenticate session data with a hmac
            $sessionDataString = \json_encode($sessionData);
            $sessionDataString = $this->appendHMAC($sessionDataString);
        }
        $this->userAuthentication->setAndSaveSessionData(self::SESSION_IDENTIFIER, $sessionDataString);
    }

    /**
     * @return array
     */
    public function getSessionData(): array
    {
        $sessionData = null;
        $sessionDataString = $this->userAuthentication->getSessionData(self::SESSION_IDENTIFIER);
        if (is_string($sessionDataString)) {
            try {
                $sessionDataString = $this->verifyAndStripHMAC($sessionDataString);
                $sessionData = \json_decode($sessionDataString, true);
            } catch (InvalidHmacException $e) {
                $this->clearAll();
                $sessionData = [];
            }
        }
        if (!is_array($sessionData)) {
            $sessionData = [];
        }
        return $sessionData;
    }

    /**
     * @param array $token
     * @return boolean
     */
    private function isValidToken(array $token): bool
    {
        return is_array($token) && count($token) == 2
            && array_key_exists('crdate', $token) && is_int($token['crdate'])
            && array_key_exists('token', $token) && is_string($token['token'])
            && $token['crdate'] >= (time() - self::TOKEN_LIFETIME);
    }

    /**
     * @param string $string
     * @return string
     */
    private function appendHMAC(string $string): string
    {
        return $string . $this->createHMAC($string);
    }

    /**
     * @param string $string
     * @throws InvalidHmacException
     * @return string
     */
    private function verifyAndStripHMAC(string $string): string
    {
        if (Binary::safeStrlen($string) > self::HMAC_LENGTH) {
            $hmac = Binary::safeSubstr($string, (self::HMAC_LENGTH * -1));
            $plainString = Binary::safeSubstr($string, 0, (self::HMAC_LENGTH * -1));
            if (\hash_equals($this->createHMAC($plainString), $hmac)) {
                return $plainString;
            }
        }
        throw new InvalidHmacException();
    }

    /**
     * @param string $string
     * @return string
     */
    private function createHMAC(string $string): string
    {
        return \hash_hmac(self::HMAC_ALGO, $string, $this->getTypo3EncryptionKey());
    }

    /**
     * @throws \RuntimeException
     * @return string
     */
    private function getTypo3EncryptionKey(): string
    {
        $encryptionKey = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        // Check encryption key: if it was generated automatically, it
        // should be 96 chars long. We do not accept shorter strings.
        if (!is_string($encryptionKey) || Binary::safeStrlen($encryptionKey) < 96) {
            throw new \RuntimeException('Invalid TYPO3 encryptionKey: set a new one in the Install Tool');
        }
        return $encryptionKey;
    }
}
