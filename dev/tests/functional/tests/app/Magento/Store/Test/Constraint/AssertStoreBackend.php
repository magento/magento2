<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\SystemConfig;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreBackend
 * Assert that created store view displays in backend configuration (Stores > Configuration > "Scope" dropdown)
 */
class AssertStoreBackend extends AbstractConstraint
{
    /**
     * Assert that created store view displays in backend configuration (Stores > Configuration > "Scope" dropdown)
     *
     * @param Store $store
     * @param SystemConfig $systemConfig
     * @return void
     */
    public function processAssert(Store $store, SystemConfig $systemConfig)
    {
        $systemConfig->open();
        $isStoreVisible = $systemConfig->getPageActions()->isStoreVisible($store);
        \PHPUnit_Framework_Assert::assertTrue($isStoreVisible, "Store view is not visible in dropdown on config page");
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store View is available in backend configuration (Stores > Configuration > "Scope" dropdown)';
    }
}
