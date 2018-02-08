.. include:: ../Includes.txt


.. _section-developer-manual:

Developers Manual
=================

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

Why not use the TYPO3 FormProtection?
-------------------------------------

As you might know, TYPO3 ships with it's own
:ref:`FormProtection Tool <t3coreapi:csrf>` since TYPO3 4.5 and it is
widely used throughout the core.

There are two major drawbacks is in our opinion:

1. Tokens are not necessarily unique. Because a token is generated with
   a HMAC, that is mixed with a secret (which is persisted in the
   user session). With this approach the uniqueness of any generated
   token relies on the uniqueness of the arguments passed to the
   generator method. Therefore, the token will always be the same (for
   the same generator arguments), as long as the session lives and the
   secret in the session does not change (which could be triggered by an
   extra API call).

2. Tokens do not have a limited lifetime. The secret in the session does
   not either.

In our opinion, the TYPO3 FormProtection Tool is too simple and does not
meet the properties of a modern CSRF prevention.
