<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreNotInGrid
 * Assert that store is absent in grid
 */
class AssertStoreNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Store can not be found in Stores grid by name
     *
     * @param StoreIndex $storeIndex
     * @param Store $store
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, Store $store)
    {
        $storeName = $store->getName();
        $storeIndex->open()->getStoreGrid()->search(['store_title' => $storeName]);
        \PHPUnit_Framework_Assert::assertFalse(
            $storeIndex->getStoreGrid()->isStoreExists($storeName),
            'Store \'' . $storeName . '\' is present in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store is absent in grid.';
    }
}
