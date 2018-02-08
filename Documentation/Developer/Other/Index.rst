.. include:: ../../Includes.txt


.. _section-developer-manual-other:

Non-extbase extensions
======================

.. contents:: Table of Contents
    :local:

While the usage of this extension is very simple for extbase/fluid-based
extensions, others can use it anyway. The API is easy-to-use, let's take
a look at it.

In the following, there is a description of an utterly simple example.
You'll get the idea while reading the code.

.. important::

    When the token validation failed, do **not** run any further logic
    or try  to "catch and handle" the case. Invalid (or not existing)
    tokens either mean that there's an attack going on or you messed
    something up in the code. Just stop every further processing and
    exit, the simpler, the better!


NoCSRF usage example
--------------------

This example uses `CsrfRegistry::generateTokenAndIdentifier()` and
`CsrfRegistry::verifyToken()`, the basic API methods. The use of the
constants from `CsrfTokenViewHelper` is not required, but recommended,
they are part of the API.

.. code-block:: php

    <?php
    namespace My\Vendor;

    use AawTeam\Nocsrf\Session\CsrfRegistry;
    use AawTeam\Nocsrf\ViewHelpers\Form\CsrfTokenViewHelper;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    class ProductController
    {
        public function createAction()
        {
            /** @var CsrfRegistry $csrfRegistry */
            $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);

            $postdata = GeneralUtility::_POST('tx_myext');
            if (is_array($postdata) && !empty($postdata)) {
                $identifier = $postdata[CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER] ?? '';
                $token = $postdata[CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER] ?? '';
                if($csrfRegistry->verifyToken($identifier, $token) !== true) {
                    throw new \Exception('Security alert: CSRF token validation failed');
                }

                // At this point you can be sure the request is legitimate

            } else {
                list($identifier, $token) = $csrfRegistry->generateTokenAndIdentifier();
                return '
                    <form action="" method="post">
                        <input type="hidden" name="tx_myext[' . CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER . ']" value="' . htmlentities($identifier, ENT_QUOTES, 'UTF-8') . '" />
                        <input type="hidden" name="tx_myext[' . CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER . ']" value="' . htmlentities($token, ENT_QUOTES, 'UTF-8') . '" />
                        <!-- The other form-stuff goes here -->
                    </form>
                ';
            }
        }
    }
