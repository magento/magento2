<?php
/**
 * Test for \Magento\Paypal\Block\Billing\Agreement\View
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Billing\Agreement;

use Magento\TestFramework\Helper\Bootstrap;

class ViewTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var \Magento\Paypal\Block\Billing\Agreement\View */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Bootstrap::getObjectManager()->create('Magento\Paypal\Block\Billing\Agreement\View');
        parent::setUp();
    }

    /**
     * Test getting orders associated with specified billing agreement.
     *
     * Create two identical orders, associate one of them with billing agreement and invoke testGetRelatedOrders()
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetRelatedOrders()
    {
        /** Customer ID declared in the fixture */
        $customerId = 1;
        /** Assign first order to the active customer */
        /** @var \Magento\Sales\Model\Order $orderA */
        $orderA = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $orderA->loadByIncrementId('100000001');
        $orderA->setCustomerIsGuest(false)->setCustomerId($customerId)->save();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($customerId);

        /** Assign second order to the active customer */
        $orderB = clone $orderA;
        $orderB->setId(
            null
        )->setIncrementId(
            '100000002'
        )->setCustomerIsGuest(
            false
        )->setCustomerId(
            $customerId
        )->save();

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($customerId);

        /** @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection'
        );
        /** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
        $billingAgreement = $billingAgreementCollection->getFirstItem();
        $billingAgreement->addOrderRelation($orderA->getId())->save();

        $registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
        $registry->register('current_billing_agreement', $billingAgreement);

        $relatedOrders = $this->_block->getRelatedOrders();
        $this->assertEquals(1, $relatedOrders->count(), "Only one order must be returned.");
        $this->assertEquals(
            $orderA->getId(),
            $relatedOrders->getFirstItem()->getId(),
            "Invalid order returned as associated with billing agreement."
        );
    }
}
