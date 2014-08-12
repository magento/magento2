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
namespace Magento\Paypal\Controller;

/**
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class PayflowTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        parent::setUp();

        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');
        $order->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK);

        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Quote'
        )->setStoreId(
            $order->getStoreId()
        )->save();

        $order->setQuoteId($quote->getId());
        $order->save();

        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Session');
        $session->setLastRealOrderId($order->getRealOrderId())->setLastQuoteId($order->getQuoteId());
    }

    public function testCancelPaymentActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/cancelpayment');
        $this->assertContains(
            "parent.jQuery('#checkoutSteps').trigger('gotoSection', 'payment');",
            $this->getResponse()->getBody()
        );
        $this->assertContains("parent.jQuery('#checkout-review-submit').show();", $this->getResponse()->getBody());
        $this->assertContains("parent.jQuery('#iframe-warning').hide();", $this->getResponse()->getBody());
    }

    public function testReturnurlActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/returnurl');
        $this->assertContains(
            "parent.jQuery('#checkoutSteps').trigger('gotoSection', 'payment');",
            $this->getResponse()->getBody()
        );
        $this->assertContains("parent.jQuery('#checkout-review-submit').show();", $this->getResponse()->getBody());
        $this->assertContains("parent.jQuery('#iframe-warning').hide();", $this->getResponse()->getBody());
    }

    public function testFormActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/form');
        $this->assertContains(
            '<form id="token_form" method="POST" action="https://payflowlink.paypal.com/">',
            $this->getResponse()->getBody()
        );
        // Check P3P header
        $headerConstraints = [];
        foreach ($this->getResponse()->getHeaders() as $header) {
            $headerConstraints[] = new \PHPUnit_Framework_Constraint_IsEqual($header['name']);
        }
        $constraint = new \PHPUnit_Framework_Constraint_Or();
        $constraint->setConstraints($headerConstraints);
        $this->assertThat('P3p', $constraint);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store payment/paypal_payflow/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test02', 'reserved_order_id');
        $order->load('100000001', 'increment_id')->setQuoteId($quote->getId())->save();
        $session->setQuoteId($quote->getId());
        $session->setPaypalStandardQuoteId($quote->getId())->setLastRealOrderId('100000001');
        $this->dispatch('paypal/payflow/cancelpayment');
        $order->load('100000001', 'increment_id');
        $this->assertEquals('canceled', $order->getState());
        $this->assertEquals($session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }
}
