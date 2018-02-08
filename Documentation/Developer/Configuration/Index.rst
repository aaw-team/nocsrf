.. include:: ../../Includes.txt


.. _section-developer-manual-configuration:

Configuration
=============

.. contents:: Table of Contents
    :local:

The behaviour of NoCSRF is configured in
`$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nocsrf']`.

.. _section-developer-manual-configuration-maxTokensInSession:

maxTokensInSession
------------------

Integer greater than zero. Defines, how many tokens are allowed in
session storage. The more tokens allowed, the more space in session
storage is used. When the limit is exceeded, the superfluous tokens will
be discarded (and thus invalidated) oldest first. The default value is
`AawTeam\Nocsrf\Session\CsrfRegistry::DEFAULT_MAX_TOKENS_IN_SESSION`,
which happens to be `1000`.

.. tip::

    The default value will suit most use cases. Only change if:

    * session storage amount is very small (decrease `maxTokensInSession`)
    * lots of concurrent forms/links exist in the application (increase
      `maxTokensInSession`)

**Example:**

.. code-block:: php

    // Allow lots of tokens
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nocsrf']['maxTokensInSession'] = 65536;
