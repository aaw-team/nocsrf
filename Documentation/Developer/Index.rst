.. include:: ../Includes.txt


.. _section-developer-manual:

Developers Manual
=================

.. _section-dev-include:

Include in your project
-----------------------

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

.. _section-dev-general:

General
-------

If you don't know about Cross-site request forgery (CSRF), please read
this first:

* `Cross-site request forgery (Wikipedia) <https://en.wikipedia.org/wiki/Cross-site_request_forgery>`_
* `Cross-site request forgery (OWASP) <https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)>`_

.. _section-dev-usage:

Usage
-----

Include in a fluid form
^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: html
    
    <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
          xmlns:nocsrf="http://typo3.org/ns/AawTeam/Nocsrf/ViewHelpers">
        <f:form method="post">
            <nocsrf:form.csrftoken />
            <!-- The other form-stuff goes here -->
        </f:form>
    </html>

Validate a request (extbase)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    use AawTeam\Nocsrf\Utility\NocsrfUtility;

    class MyController extends ActionController
    {
        public function createAction(MyModel $model)
        {
            if (NocsrfUtility::validateExtbaseMvcRequest($this->request) !== true) {
                $this->response->setContent('Security alert: CSRF token validation failed');
                throw new \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException();
            }
            // At this point you can be sure the request is legitimate
        }
    }

Validate a request (without extbase)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    use AawTeam\Nocsrf\Session\CsrfRegistry;
    use AawTeam\Nocsrf\ViewHelpers\Form\CsrfTokenViewHelper;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    class MyController
    {
        public function createAction()
        {
            $postdata = GeneralUtility::_POST('tx_myext');
            if (is_array($postdata) && !empty($postdata)) {
                $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
                $identifier = $postdata[CsrfTokenViewHelper::TOKEN_ID_IDENTIFIER] ?? '';
                $token = $postdata[CsrfTokenViewHelper::TOKEN_VALUE_IDENTIFIER] ?? '';
                if($csrfRegistry->verifyToken($identifier, $token) !== true) {
                    throw new \Exception('Security alert: CSRF token validation failed');
                }
                // At this point you can be sure the request is legitimate
            }
        }
    }

