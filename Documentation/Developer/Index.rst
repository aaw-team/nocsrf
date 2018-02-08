.. include:: ../Includes.txt


.. _section-developer-manual:

Developers Manual
=================

.. contents:: Table of Contents
    :local:

.. _section-dev-general:

General
-------

If you don't know about Cross-site request forgery (CSRF), please read
this first:

* `Cross-site request forgery (Wikipedia) <https://en.wikipedia.org/wiki/Cross-site_request_forgery>`_
* `Cross-site request forgery (OWASP) <https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)>`_

.. _section-dev-include:

Include NoCSRF in your project
------------------------------

To use NoCSRF in your project, add it to the dependency constraints in
your `ext_emconf.php` to make sure it is available:

.. code-block:: php

    $EM_CONF[$_EXTKEY] = array(
        // [...]
        'constraints' => array(
            'depends' => array(
                'nocsrf' => '',
            ),
        ),
    );

If your project uses composer, add `typo3-ter/nocsrf` as required
package.

.. _section-dev-usage:

Usage
-----

NoCSRF is generally designed to work within every TYPO3 extension. While
nowadays lots of extensions use extbase/fluid, NoCSRF provides some handy
integrations for those.

Please refer to the documentation of your own flavour of TYPO3 extension
or the API documentation.

.. toctree::
    :maxdepth: 3
    :titlesonly:

    Configuration/Index
    Extbase/Index
    Other/Index
    API/Index


.. _section-dev-whynott3formprotection:

What about the TYPO3 FormProtection?
------------------------------------

TYPO3 ships with it's own :ref:`FormProtection Tool <t3coreapi:csrf>`
since TYPO3 4.5, which is widely used throughout the core. There are two
major drawbacks in our opinion:

**1. Token reuse**

   Within the FormProtection API, a token is not a random string, but a
   basically a hash (hmac) including a (randomly generated) secret (which
   is stored in the session):

   .. code-block:: php

      $token = hash_hmac('sha1', $some . $userdefined . $strings . $secretFromSession, $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);

   Say: the FormProtection API is designed to be able to *reproduce
   tokens, instead of comparing them to a random value* that is dropped
   as soon as it gets used.

   Like this it is not possible to invalidate one single token (after
   use) without invalidating every other token as well.

   Furthermore, the secret value in the session is not cleared
   internally, that must be done separately (by the consuming code).
   Which leads in fact to token reuse.

**2. No token lifetime limit**

   As described in the first point, tokens are reproducible and thus
   don't exist somewhere as data. Like this it is not possible to
   invalidate/expire a token after a defined time. Except for clearing
   the session-based secret, which will in turn invalidate every other
   token generated until this moment.

   And the session-based secret does not have a lifetime limit either.
   If it doesn't get cleared proactively, it lives as long as the
   session is valid (which applies to every token as well).

These issues are supposedly caused by the complex use cases of TYPO3 CMS,
especially in backend.

However, the design of the FormProtection API did not meet our
requirements. That's why NoCSRF came to life: a modern, secure and yet
easy to use CSRF protection for TYPO3 (extension-) developers.

Please feel free to contact us if you have further questions or comments,
we're happy to answer!
