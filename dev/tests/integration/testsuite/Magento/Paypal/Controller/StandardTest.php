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

class StandardTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    protected function setUp()
    {
        parent::setUp();
        $this->_order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $this->_session = $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testRedirectActionIsContentGenerated()
    {
        $this->_order->load('100000001', 'increment_id');
        $this->_order->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_WPS);
        $this->_order->save();
        $this->_order->load('100000001', 'increment_id');

        $this->_session->setLastRealOrderId(
            $this->_order->getRealOrderId()
        )->setLastQuoteId(
            $this->_order->getQuoteId()
        );

        $this->dispatch('paypal/standard/redirect');
        $this->assertContains(
            '<form action="https://www.paypal.com/cgi-bin/webscr" id="paypal_standard_checkout"' .
            ' name="paypal_standard_checkout" method="POST">',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDataFixture Magento/Paypal/_files/quote_payment_standard.php
     * @magentoConfigFixture current_store payment/paypal_standard/active 1
     * @magentoConfigFixture current_store paypal/general/business_account merchant_2012050718_biz@example.com
     */
    public function testCancelAction()
    {
        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        $this->_session->setQuoteId($quote->getId());
        $this->_session->setPaypalStandardQuoteId($quote->getId())->setLastRealOrderId('100000002');
        $this->dispatch('paypal/standard/cancel');

        $this->_order->load('100000002', 'increment_id');
        $this->assertEquals('canceled', $this->_order->getState());
        $this->assertEquals($this->_session->getQuote()->getGrandTotal(), $quote->getGrandTotal());
        $this->assertEquals($this->_session->getQuote()->getItemsCount(), $quote->getItemsCount());
    }
}
