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
        \PHPUnit_Framework_Assert::assertNotNull(
=======
        \PHPUnit\Framework\Assert::assertNotNull(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
