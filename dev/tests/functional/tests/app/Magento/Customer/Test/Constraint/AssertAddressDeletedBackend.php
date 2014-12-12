<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAddressDeletedBackend
 * Assert that deleted customers address is not displayed on backend during order creation
 */
class AssertAddressDeletedBackend extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that deleted customers address is not displayed on backend during order creation
     *
     * @param OrderIndex $orderIndex
     * @param OrderCreateIndex $orderCreateIndex
     * @param AddressInjectable $deletedAddress
     * @param CustomerInjectable $customer
     * @return void
     */
    public function processAssert(
        OrderIndex $orderIndex,
        OrderCreateIndex $orderCreateIndex,
        AddressInjectable $deletedAddress,
        CustomerInjectable $customer
    ) {
        $filter = ['email' => $customer->getEmail()];
        $orderIndex->open()->getGridPageActions()->addNew();
        $orderCreateIndex->getCustomerBlock()->searchAndOpen($filter);
        $orderCreateIndex->getStoreBlock()->selectStoreView();
        $actualAddresses = $orderCreateIndex->getCreateBlock()->getBillingAddressBlock()->getExistingAddresses();
        $addressRenderer = $this->objectManager->create(
            'Magento\Customer\Test\Block\Address\Renderer',
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
