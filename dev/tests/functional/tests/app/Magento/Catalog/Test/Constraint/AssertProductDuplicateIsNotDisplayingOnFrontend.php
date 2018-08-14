<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductDuplicateIsNotDisplayingOnFrontend
 */
class AssertProductDuplicateIsNotDisplayingOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that product duplicate is not displayed on front-end
     *
     * @return void
     */
    public function processAssert()
    {
        // TODO mage to MAGETWO-25523
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'The product does not appear on the frontend.';
    }
}
