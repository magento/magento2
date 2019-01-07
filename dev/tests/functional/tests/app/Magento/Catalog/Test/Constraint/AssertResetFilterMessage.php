<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

/**
 * Assert that filters have been reset successfully.
 */
class AssertResetFilterMessage extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Assert message that filters have been reset.
     *
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function processAssert(
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
    ) {
        \PHPUnit\Framework\Assert::assertContains(
            'restored the filter to its original state',
            $catalogProductIndex->getMessagesBlock()->getErrorMessage(),
            "Can't find proper message"
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Filters have been reset successfully.';
    }
}
