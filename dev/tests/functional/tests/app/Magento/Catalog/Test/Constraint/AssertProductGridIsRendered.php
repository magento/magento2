<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

/**
 * Assert that product grid is rendered correctly.
 */
class AssertProductGridIsRendered extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Assert that product grid is rendered correctly.
     *
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function processAssert(
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
    ) {
        $productId = $catalogProductIndex->open()->getProductGrid()->getFirstItemId();
<<<<<<< HEAD
        \PHPUnit\Framework\Assert::assertNotNull(
=======
        \PHPUnit_Framework_Assert::assertNotNull(
>>>>>>> upstream/2.2-develop
            $productId,
            'Product grid is not rendered correctly.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product grid is rendered correctly.';
    }
}
