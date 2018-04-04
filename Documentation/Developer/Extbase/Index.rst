.. include:: ../../Includes.txt


.. _section-developer-manual-extbase:

Extbase-based extensions
========================

.. contents:: Table of Contents
    :local:


As stated before, NoCSRF provides some handy tools for extbase-based
extensions. They consist of fluid ViewHelpers and an extension of the
default `ActionController` of extbase. The combination of the two leads
to a very easy-to-use system that protects you from CSRF attacks, both
in frontend and backend.

.. important::

    Due to the nature of CSRF attacks, a valid login session MUST be
    established. Otherwise, Exceptions will be thrown. So: do not add
    the CSRF tokens to every form. Think first.

Include CSRF token in a fluid form
----------------------------------

.. code-block:: html

    <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
          xmlns:nocsrf="http://typo3.org/ns/AawTeam/Nocsrf/ViewHelpers">
        <f:form method="post">
            <nocsrf:form.csrfToken />
            <!-- The other form-stuff goes here -->
        </f:form>
    </html>

Include CSRF token in a link/uri
--------------------------------

Normally, CSRF tokens are included in forms, as described above. But if
there is a special need to add the token to a link or an uri, the
ViewHelpers `Link/ActionViewHelper` and `Uri/ActionViewHelper` can be
used:

.. code-block:: html

    <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
          xmlns:nocsrf="http://typo3.org/ns/AawTeam/Nocsrf/ViewHelpers">

        <!-- <nocsrf:link.action> example -->
        <div class="confirmationDialog">
            <p>Are you sure you want to do this?<p>
            <nocsrf:link.action action="runsomething">YES</nocsrf:link.action>
            <f:link.action action="overview">NO</f:link.action>
        </div>

        <!-- <nocsrf:uri.action> example -->
        <div class="confirmationDialog">
            <p>Are you sure you want to do this?<p>
            <a href="{nocsrf:uri.action(action:'runsomething')}">YES</a>
            <a href="javascript:history.back();">NO</a>
        </div>

    </html>

The ViewHelpers `Link/ActionViewHelper` and `Uri/ActionViewHelper`
extend their equivalents from fluid. All arguments supported by the
fluid ViewHelpers are available too. To "equip" links/uris with the CSRF
token, just change `<f:link.action ...>` to `<nocsrf:link.action ...>`
(or `<f:uri.action ..>` to `<nocsrf:uri.action ..>`).

The Controller-side
-------------------

When you have added the csrfToken ViewHelper to your form, nothing
happens with the generated data yet. It must be verified at some point
of the controller-flow. There are two ways to achieve this.

For the easier one, your controller has to extend
`AawTeam\Nocsrf\Mvc\Controller\ActionController`. It extends
`\TYPO3\CMS\Extbase\Mvc\Controller\ActionController`, which your
controller extends most probably. If that is not possible for you, you
must go the other way, which is a little bit more of effort, but not that
much. In the following, the two possibilities are described in detail.



Way 1: Validate a request by configuration
------------------------------------------

When your controller is able to extend
`AawTeam\Nocsrf\Mvc\Controller\ActionController` (instead of
`\TYPO3\CMS\Extbase\Mvc\Controller\ActionController`), you can take
advantage of the `@csrfvalidation` annotation in phpDoc.

.. code-block:: php

    <?php
    namespace My\Vendor;

    class ProductController extends \AawTeam\Nocsrf\Mvc\Controller\ActionController
    {
        /**
         * @param Product $product
         * @csrfvalidation
         */
        public function createAction(Product $product)
        {
            // At this point you can be sure the request is legitimate
        }

        /**
         * @param Product $product
         * @param bool $confirmation
         * @csrfvalidation ifHasArguments($confirmation)
         */
        public function deleteAction(Product $product, bool $confirmation = false)
        {
            if ($confirmation === true) {
                // At this point you can be sure the request is legitimate
            }
        }
    }

.. note::

    The CSRF validation takes place in the dispatch process just
    **before** the (extbase-internal) argument mapping and validation.

For more information about `@csrfvalidation` annotation, see
:ref:`section-developer-manual-csrfvalidation`.

Way 2: Validate a request inside an action method
-------------------------------------------------

When you cannot (or do not want to) to use the `ActionController` from
NoCSRF, just use `NocsrfUtility::validateExtbaseMvcRequest()` whenever
you need it. Since you are in an extbase-style controller, the request
object is always available, just pass it to this method and implement
the "token validation failed"-case.

.. important::

    When the token validation failed, do **not** run any further logic
    or try  to "catch and handle" the case. Invalid (or not existing)
    tokens either mean that there's an attack going on or you messed
    something up in the code. Just stop every further processing and
    exit, the simpler, the better!

.. code-block:: php

    <?php
    namespace My\Vendor;

    use AawTeam\Nocsrf\Utility\NocsrfUtility;

    class ProductController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
    {
        /**
         * @param Product $product
         */
        public function createAction(Product $product)
        {
            if (NocsrfUtility::validateExtbaseMvcRequest($this->request) !== true) {
                $this->response->setContent('Security alert: CSRF token validation failed');
                throw new \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException();
            }
            // At this point you can be sure the request is legitimate
        }
    }
