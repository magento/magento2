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
 * @category    Magento
 * @package     Mage_Paypal
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Paypal
 * @magentoDataFixture Mage/Sales/_files/order.php
 */
class Mage_Paypal_PayflowControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function setUp()
    {
        parent::setUp();

        $order = new Mage_Sales_Model_Order();
        $order->load('100000001', 'increment_id');
        $order->getPayment()->setMethod(Mage_Paypal_Model_Config::METHOD_PAYFLOWLINK);
        $order->save();

        $session = Mage::getSingleton('Mage_Checkout_Model_Session');
        $session->setLastRealOrderId($order->getRealOrderId())
            ->setLastQuoteId($order->getQuoteId());
    }

    public function testCancelPaymentActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/cancelpayment');
        $this->assertContains(
            'window_top.checkout.gotoSection("payment");',
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            'window_top.document.getElementById(\'checkout-review-submit\').show();',
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            'window_top.document.getElementById(\'iframe-warning\').hide();',
            $this->getResponse()->getBody()
        );
    }

    public function testReturnurlActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/returnurl');
        $this->assertContains(
            'window_top.checkout.gotoSection("payment");',
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            'window_top.document.getElementById(\'checkout-review-submit\').show();',
            $this->getResponse()->getBody()
        );
        $this->assertContains(
            'window_top.document.getElementById(\'iframe-warning\').hide();',
            $this->getResponse()->getBody()
        );
    }

    public function testFormActionIsContentGenerated()
    {
        $this->dispatch('paypal/payflow/form');
        $this->assertContains(
            '<form id="token_form" method="POST" action="https://payflowlink.paypal.com/">',
            $this->getResponse()->getBody()
        );
    }
}