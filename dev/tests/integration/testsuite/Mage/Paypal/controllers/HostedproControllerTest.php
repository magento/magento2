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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Sales/_files/order.php
 */
class Mage_Paypal_HostedproControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function testCancelActionIsContentGenerated()
    {
        $order = Mage::getModel('Mage_Sales_Model_Order');
        $order->load('100000001', 'increment_id');
        $order->getPayment()->setMethod(Mage_Paypal_Model_Config::METHOD_HOSTEDPRO);

        $quote = Mage::getModel('Mage_Sales_Model_Quote')
            ->setStoreId($order->getStoreId())
            ->save();

        $order->setQuoteId($quote->getId());
        $order->save();

        $session = Mage::getSingleton('Mage_Checkout_Model_Session');
        $session->setLastRealOrderId($order->getRealOrderId())
            ->setLastQuoteId($order->getQuoteId());

        $this->dispatch('paypal/hostedpro/cancel');
        $this->assertContains(
            "parent.jQuery('#checkoutSteps').trigger('gotoSection', 'payment');",
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            "parent.jQuery('#checkout-review-submit').show();",
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            "parent.jQuery('#iframe-warning').hide();",
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDataFixture Mage/Paypal/_files/quote_payment_express.php
     * @magentoConfigFixture current_store payment/paypal_hostedpro/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $order = $this->_objectManager->create('Mage_Sales_Model_Order');
        $session = $this->_objectManager->get('Mage_Checkout_Model_Session');

        $quote = $this->_objectManager->create('Mage_Sales_Model_Quote');
        $quote->load('test02', 'reserved_order_id');
        $session->setQuoteId($quote->getId());
        $session->setPaypalStandardQuoteId($quote->getId())
            ->setLastRealOrderId('100000002');
        $this->dispatch('paypal/hostedpro/cancel');

        $order->load('100000002', 'increment_id');
        $this->assertEquals('canceled', $order->getState());
        $this->assertEquals($session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }
}
