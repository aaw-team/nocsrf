.. include:: ../../Includes.txt


.. _section-developer-manual-api:

API documentation
=================

.. contents:: Table of Contents
    :local:

.. _section-developer-manual-api-registry:

CsrfRegistry
------------

Public interface
^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php
    namespace AawTeam\Nocsrf\Session;

    final class CsrfRegistry
    {
        public function generateTokenAndIdentifier(): array;
        public function verifyToken(string $identifier, string $tokenFromUserInput): bool;
        public function clearAll();
    }


.. _section-developer-manual-api-utility:

NocsrfUtility
-------------

Public interface
^^^^^^^^^^^^^^^^

.. code-block:: php

    <?php
    namespace AawTeam\Nocsrf\Utility;

    final class NocsrfUtility
    {
        public static function validateExtbaseMvcRequest(\TYPO3\CMS\Extbase\Mvc\Request $request): bool;
        public static function shouldMethodRunRequestValidation(string $className, string $methodName, array $argumentNamesInRequest): bool;
    }
