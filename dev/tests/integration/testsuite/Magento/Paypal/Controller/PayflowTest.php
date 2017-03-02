<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->load('100000001', 'increment_id');
        $order->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK);

        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\Quote::class
        )->setStoreId(
            $order->getStoreId()
        )->save();

        $order->setQuoteId($quote->getId());
        $order->save();

        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Checkout\Model\Session::class
        );
        $session->setLastRealOrderId($order->getRealOrderId())->setLastQuoteId($order->getQuoteId());
    }

    public function testCancelPaymentActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/cancelpayment');
        $this->assertContains("goToSuccessPage = ''", $this->getResponse()->getBody());
    }

    public function testReturnurlActionIsContentGenerated()
    {
        $checkoutHelper = $this->_objectManager->create(\Magento\Paypal\Helper\Checkout::class);
        $checkoutHelper->cancelCurrentOrder('test');
        $this->dispatch('paypal/payflow/returnurl');
        $this->assertContains("goToSuccessPage = ''", $this->getResponse()->getBody());
    }

    public function testFormActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/form');
        $this->assertContains(
            '<form id="token_form" method="GET" action="https://payflowlink.paypal.com">',
            $this->getResponse()->getBody()
        );
        // Check P3P header
        $headerConstraints = [];
        foreach ($this->getResponse()->getHeaders() as $header) {
            $headerConstraints[] = new \PHPUnit_Framework_Constraint_IsEqual($header->getFieldName());
        }
        $constraint = new \PHPUnit_Framework_Constraint_Or();
        $constraint->setConstraints($headerConstraints);
        $this->assertThat('P3P', $constraint);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store payment/paypal_payflow/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);
        $session = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);

        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
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
