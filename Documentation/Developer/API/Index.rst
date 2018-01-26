.. include:: ../../Includes.txt


.. _section-developer-manual-api:

API documentation
=================

.. _section-developer-manual-api-annotation:

@csrfvalidation annotation
--------------------------

With the `@csrfvalidation` phpDoc annotation, it is possible to
"configure", when a csrf token validation should take place. The logic
is implemented in `NocsrfUtility::shouldMethodRunRequestValidation()`,
see :ref:`section-developer-manual-api-utility` for more details. This
feature is inspired by the `@validate` annotations in FLOW/extbase.

The idea is, that a developer does not have to implement the validation
herself, but only decides when the validation is needed. Also take a look
at the section :ref:`section-developer-manual-extbase` to see how this
can work.

There are three use cases:

1. Validate every request `@csrfvalidation`

   .. code-block:: php

       /**
        * @param Product $product
        * @csrfvalidation
        */
       public function createAction(Product $product)
       {
           // At this point you can be sure the request is legitimate
       }

2. Validate only when any argument is passed to the action method
   `@csrfvalidation ifHasAnyArgument`

   .. code-block:: php

       /**
        * @param Product $product
        * @param Category $category
        * @csrfvalidation ifHasAnyArgument
        */
       public function createAction(Product $product = null, Category $category = null)
       {
           if ($product !== null || $category !== null) {
               // At this point you can be sure the request is legitimate
           }
       }

3. Validate only when any defined argument is passed to the action
   method `@csrfvalidation ifHasArguments(arg1 [, arg2 [, argN]])`

   .. code-block:: php

       /**
        * @param Product $product
        * @param Category $category
        * @csrfvalidation ifHasArguments($product)
        */
       public function createAction(Product $product = null, Category $category = null)
       {
           if ($product !== null) {
               // At this point you can be sure the request is legitimate
           }
       }


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
