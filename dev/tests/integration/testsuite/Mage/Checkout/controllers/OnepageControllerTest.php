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
 * @package     Mage_Checkout
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Sales/_files/quote.php
 */
class Mage_Checkout_OnepageControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    protected function setUp()
    {
        parent::setUp();
        $quote = new Mage_Sales_Model_Quote();
        $quote->load('test01', 'reserved_order_id');
        Mage::getSingleton('Mage_Checkout_Model_Session')->setQuoteId($quote->getId());
    }

    /**
     * Covers onepage payment.phtml templates
     */
    public function testIndexAction()
    {
        $this->dispatch('checkout/onepage/index');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<li id="opc-payment"', $html);
        $this->assertContains('<dl class="sp-methods" id="checkout-payment-method-load">', $html);
        $this->assertContains('<form id="co-billing-form" action="">', $html);
    }

    /**
     * Covers app/code/core/Mage/Checkout/Block/Onepage/Payment/Info.php
     */
    public function testProgressAction()
    {
        $steps = array(
            'payment' => array('is_show' => true, 'complete' => true),
            'billing' => array('is_show' => true),
            'shipping' => array('is_show' => true),
            'shipping_method' => array('is_show' => true),
        );
        Mage::getSingleton('Mage_Checkout_Model_Session')->setSteps($steps);

        $this->dispatch('checkout/onepage/progress');
        $html = $this->getResponse()->getBody();
        $this->assertContains('Checkout', $html);
        $methodTitle = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote()->getPayment()->getMethodInstance()
            ->getTitle();
        $this->assertContains('<p>' . $methodTitle . '</p>', $html);
    }

    public function testShippingMethodAction()
    {
        $this->dispatch('checkout/onepage/shippingmethod');
        $this->assertContains('no quotes are available', $this->getResponse()->getBody());
    }

    public function testReviewAction()
    {
        $this->dispatch('checkout/onepage/review');
        $this->assertContains('checkout-review', $this->getResponse()->getBody());
    }
}
