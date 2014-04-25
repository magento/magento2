<?php
/**
 * Test for \Magento\Paypal\Block\Billing\Agreement\View
 *
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
namespace Magento\Paypal\Block\Billing\Agreement;

use Magento\TestFramework\Helper\Bootstrap;

class ViewTest extends \Magento\Backend\Utility\Controller
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

        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
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
