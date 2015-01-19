<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\StoreGroup;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreGroupNotInGrid
 * Assert that store group is absent in grid
 */
class AssertStoreGroupNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Store Group can not be found in Stores grid by name
     *
     * @param StoreIndex $storeIndex
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, StoreGroup $storeGroup)
    {
        $storeGroupName = $storeGroup->getName();
        $storeIndex->open()->getStoreGrid()->search(['group_title' => $storeGroupName]);
        \PHPUnit_Framework_Assert::assertFalse(
            $storeIndex->getStoreGrid()->isStoreExists($storeGroupName),
            'Store group \'' . $storeGroupName . '\' is present in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store Group is absent in grid.';
    }
}
