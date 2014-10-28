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
namespace Magento\Sales\Model\Observer\Frontend\Quote\Address;

class CollectTotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals
     */
    protected $model;

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals'
        );
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     *
     * @covers \Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals::dispatch
     */
    public function testChangeQuoteCustomerGroupIdForCustomerWithDisabledAutomaticGroupChange()
    {
        /** @var \Magento\Framework\ObjectManager $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $customer->load(1);
        $customer->setDisableAutoGroupChange(1);
        $customer->setGroupId(2);
        $customer->save();

        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        $quote->setCustomer($customer);

        $quoteAddress = $quote->getBillingAddress();

        $eventObserver = $objectManager->create(
            'Magento\Framework\Event\Observer',
            array('data' => array('quote_address' => $quoteAddress))
        );
        $this->model->dispatch($eventObserver);

        $this->assertEquals(2, $quote->getCustomer()->getGroupId());
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     *
     * @covers \Magento\Sales\Model\Observer\Frontend\Quote\Address\CollectTotals::dispatch
     */
    public function testChangeQuoteCustomerGroupIdForCustomerWithEnabledAutomaticGroupChange()
    {
        /** @var \Magento\Framework\ObjectManager $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $customer->load(1);
        $customer->setDisableAutoGroupChange(0);
        $customer->setGroupId(2);
        $customer->save();

        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        $quote->setCustomer($customer);

        $quoteAddress = $quote->getBillingAddress();

        $eventObserver = $objectManager->create(
            'Magento\Framework\Event\Observer',
            array('data' => array('quote_address' => $quoteAddress))
        );
        $this->model->dispatch($eventObserver);

        $this->assertEquals(1, $quote->getCustomer()->getGroupId());
    }
}
