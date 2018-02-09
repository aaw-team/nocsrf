.. include:: ../../Includes.txt


.. _section-developer-manual-csrfvalidation:

@csrfvalidation
===============

.. contents:: Table of Contents
    :local:

.. _section-developer-manual-csrfvalidation-overview:

Overview
--------

With the `@csrfvalidation` phpDoc annotation, it is possible to
"configure", when a csrf token validation should take place. The logic
is implemented in `NocsrfUtility::shouldMethodRunRequestValidation()`
using Reflection API, see :ref:`section-developer-manual-api-utility`
for more details. This feature is inspired by the `@validate` annotations
in FLOW/extbase.

The idea is, that a developer does not have to implement the validation
herself, but only decides when the validation is needed. Also take a look
at the section :ref:`section-developer-manual-extbase` to see how this
can work.

There are three use cases:

1. :ref:`section-developer-manual-csrfvalidation-all`
2. :ref:`section-developer-manual-csrfvalidation-or` to the action
   method. The argument list can be specified optionally.
3. :ref:`section-developer-manual-csrfvalidation-and` to the action
   method.

.. _section-developer-manual-csrfvalidation-all:

Validate every request
----------------------

Abstract: `@csrfvalidation`

When `@csrfvalidation` tag is specified in the action method's phpDoc
comment, every request to this method will be validated:

**Example:**

.. code-block:: php

    /**
     * @param Product $product
     * @csrfvalidation
     */
    public function createAction(Product $product)
    {
        // At this point you can be sure the request is legitimate
    }

.. _section-developer-manual-csrfvalidation-or:

Validate only when any argument is passed
-----------------------------------------

Abstract: `@csrfvalidation ifHasAnyArgument([arg1, arg2 [, argN]])`

This runs the CSRF validation only when any argument exists in the
request.

**Example 1:**

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

It is possible to narrow down, when the CSRF validation should be
invoked. Namely, when any of the specified arguments exist in the
request. That means that the following works exactly the same as
Example 1 (above):

`@csrfvalidation ifHasAnyArgument($product,$category)`

.. important::

   It is not possible to specify one single argument. In such a case
   rather use `@csrfvalidation ifHasArguments()`, see
   :ref:`section-developer-manual-csrfvalidation-and`.

To give you a better example of how this "OR-chaining" can work, see
the following code.

**Example 2:**

.. code-block:: php

    /**
     * @param Product $product
     * @param bool $confirmation
     * @param bool $adminOveride
     * @csrfvalidation ifHasAnyArgument($confirmation,$adminOveride)
     */
    public function deleteAction(Product $product, bool $confirmation = false, bool $adminOveride = false)
    {

        if ($confirmation !== true) {
            if ($adminOverride !== true) {
                return 'You must confirm this action!';
            } else {
                // At this point you can be sure the request is legitimate
            }
        } else {
            // At this point you can be sure the request is legitimate
        }
    }


.. _section-developer-manual-csrfvalidation-and:

Validate only when all specified arguments are passed
-----------------------------------------------------

Abstract: `@csrfvalidation ifHasArguments(arg1 [, arg2 [, argN]])`

Works like `@csrfvalidation ifHasAnyArgument`, but the specified
arguments are "AND-chained". Furthermore it is not possible to use this
tag variant without at least one argument definition.

The following two examples show the most common use cases for
`@csrfvalidation ifHasArguments()`.

**Example 1:**

.. code-block:: php

    /**
     * @param Product $product
     * @csrfvalidation ifHasArguments($product)
     */
    public function createAction(Product $product = null)
    {
        if ($product !== null) {
            // At this point you can be sure the request is legitimate
        }
    }

**Example 2:**

.. code-block:: php

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

**Example 3:**

This is more an edge case to explain how `@csrfvalidation
ifHasArguments()` works with more than one argument.

.. code-block:: php

    /**
     * @param Product $product
     * @param Category $category
     * @csrfvalidation ifHasArguments($product,$category)
     */
    public function createAction(Product $product = null, Category $category = null)
    {
        if ($product !== null) {
            // At this point you can NOT be sure the request is legitimate
        }
        if ($category !== null) {
            // At this point you can NOT be sure the request is legitimate
        }
        if ($product !== null && $category !== null) {
            // At this point you can be sure the request is legitimate
        }
    }
