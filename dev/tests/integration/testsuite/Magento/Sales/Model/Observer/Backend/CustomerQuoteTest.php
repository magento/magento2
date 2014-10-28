<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
