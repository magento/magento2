<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAddressDeletedBackend
 * Assert that deleted customers address is not displayed on backend during order creation
 */
class AssertAddressDeletedBackend extends AbstractConstraint
{
    /**
     * Assert that deleted customers address is not displayed on backend during order creation
     *
     * @param OrderIndex $orderIndex
     * @param OrderCreateIndex $orderCreateIndex
     * @param Address $deletedAddress
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        OrderIndex $orderIndex,
        OrderCreateIndex $orderCreateIndex,
        Address $deletedAddress,
        Customer $customer
    ) {
        $filter = ['email' => $customer->getEmail()];
        $orderIndex->open()->getGridPageActions()->addNew();
        $orderCreateIndex->getCustomerBlock()->searchAndOpen($filter);
        $orderCreateIndex->getStoreBlock()->selectStoreView();
        $actualAddresses = $orderCreateIndex->getCreateBlock()->getBillingAddressBlock()->getExistingAddresses();
        $addressRenderer = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $deletedAddress]
        );
        $addressToSearch = $addressRenderer->render();
        \PHPUnit_Framework_Assert::assertFalse(
            in_array($addressToSearch, $actualAddresses),
            'Deleted address is present on backend during order creation'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Deleted address is absent on backend during order creation';
    }
}
