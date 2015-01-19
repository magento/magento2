<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Store\Test\Fixture\Store;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreNotOnFrontend
 * Assert that created store view is not available on frontend (store view selector on page top)
 */
class AssertStoreNotOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created store view is not available on frontend (store view selector on page top)
     *
     * @param Store $store
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(Store $store, CmsIndex $cmsIndex)
    {
        $cmsIndex->open();
        if ($cmsIndex->getFooterBlock()->isStoreGroupSwitcherVisible()
            && $cmsIndex->getFooterBlock()->isStoreGroupVisible($store)
        ) {
            $cmsIndex->getFooterBlock()->selectStoreGroup($store);
        }

        $isStoreViewVisible = !$cmsIndex->getStoreSwitcherBlock()->isStoreViewDropdownVisible()
            ? false // if only one store view is assigned to store group
            : $cmsIndex->getStoreSwitcherBlock()->isStoreViewVisible($store);

        \PHPUnit_Framework_Assert::assertFalse(
            $isStoreViewVisible,
            "Store view is visible in dropdown on CmsIndex page"
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store view is not visible in dropdown on CmsIndex page';
    }
}
