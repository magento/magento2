<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller;

/**
 * @magentoDataFixture Magento/Sales/_files/order.php
 */
class HostedproTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testCancelActionIsContentGenerated()
    {
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->load('100000001', 'increment_id');
        $order->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_HOSTEDPRO);

        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Quote\Model\Quote'
        )->setStoreId(
            $order->getStoreId()
        )->save();

        $order->setQuoteId($quote->getId());
        $order->save();

        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Session');
        $session->setLastRealOrderId($order->getRealOrderId())->setLastQuoteId($order->getQuoteId());

        $this->dispatch('paypal/hostedpro/cancel');
        $this->assertContains("goToSuccessPage = ''", $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_express.php
     * @magentoConfigFixture current_store payment/paypal_hostedpro/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('100000002', 'reserved_order_id');
        $session->setQuoteId($quote->getId());
        $session->setPaypalStandardQuoteId($quote->getId())->setLastRealOrderId('100000002');
        $this->dispatch('paypal/hostedpro/cancel');

        $order->load('100000002', 'increment_id');
        $this->assertEquals('canceled', $order->getState());
        $this->assertEquals($session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }
}
