<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreFrontend
 * Assert that created store view available on frontend (store view selector on page top)
 */
class AssertStoreFrontend extends AbstractConstraint
{
    /**
     * Assert that created store view available on frontend (store view selector on page top)
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
            ? true // if only one store view is assigned to store group
            : $cmsIndex->getStoreSwitcherBlock()->isStoreViewVisible($store);

        \PHPUnit_Framework_Assert::assertTrue(
            $isStoreViewVisible,
            "Store view is not visible in dropdown on CmsIndex page"
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store view is visible in dropdown on CmsIndex page';
    }
}
