<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteNotInGrid
 * Assert that deleted website is absent in grid
 */
class AssertWebsiteNotInGrid extends AbstractConstraint
{
    /**
     * Assert that created Website can't be found in grid by name
     *
     * @param StoreIndex $storeIndex
     * @param Website $website
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, Website $website)
    {
        $websiteName = $website->getName();
        $storeIndex->open()->getStoreGrid()->search(['website_title' => $websiteName]);
        \PHPUnit_Framework_Assert::assertFalse(
            $storeIndex->getStoreGrid()->isWebsiteExists($website),
            'Website \'' . $websiteName . '\' is present in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Website is absent in grid.';
    }
}
