<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Backend;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class CustomerQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensure that customer group is updated in customer quote, when it is changed for the customer.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerSaveQuoteObserver()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
        $customer->load(1);

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        $quote->setCustomerIsGuest(false)->setCustomerId(1)->setCustomerGroupId($customer->getGroupId())->save();

        $this->assertNotNull($customer->getGroupId(), "Precondition failed: Customer group is not set.");
        $this->assertEquals(
            $customer->getGroupId(),
            $quote->getCustomerGroupId(),
            "Precondition failed: Customer group in quote is invalid."
        );

        /**
         * 'customer_save_after_data_object' event is expected to be dispatched when customer save is invoked.
         * \Magento\Sales\Model\Observer\Backend\CustomerQuote::dispatch() is an observer of this event.
         */
        $newCustomerGroupId = 2;
        $customer->setGroupId($newCustomerGroupId)->save();

        $quote->load('test01', 'reserved_order_id');
        $this->assertEquals(
            $newCustomerGroupId,
            $quote->getCustomerGroupId(),
            'Customer group in quote was not updated on "customer_save_after_data_object" event ' .
            'by Magento\Sales\Model\Observer\Backend\CustomerQuote::dispatch().'
        );
    }
}
