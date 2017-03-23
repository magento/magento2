<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreInGrid
 * Assert that created Store View can be found in Stores grid
 */
class AssertStoreInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Store View can be found in Stores grid by name
     *
     * @param StoreIndex $storeIndex
     * @param Store $store
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, Store $store)
    {
        $storeName = $store->getName();
        $storeIndex->open()->getStoreGrid()->search(['store_title' => $storeName]);
        \PHPUnit_Framework_Assert::assertTrue(
            $storeIndex->getStoreGrid()->isStoreExists($storeName),
            'Store \'' . $storeName . '\' is not present in grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store View is present in grid.';
    }
}
