<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

/**
 * @magentoDataFixture Magento/Sales/_files/quote.php
 */
class OnepageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        parent::setUp();
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Checkout\Model\Session'
        )->setQuoteId(
            $quote->getId()
        );
    }

    /**
     * Covers onepage payment.phtml templates
     */
    public function testIndexAction()
    {
        $this->dispatch('checkout/onepage/index');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<li id="opc-payment"', $html);
        $this->assertSelectEquals('[id="checkout-shipping-method-load"]', '', 1, $html);
        $this->assertSelectEquals('[id="checkout-payment-method-load"]', '', 1, $html);
        $this->assertSelectCount('form[id="co-billing-form"][action=""]', 1, $html);
        $this->assertSelectCount('form[id="co-payment-form"] input[name="form_key"]', 1, $html);
    }

    public function testShippingMethodAction()
    {
        $this->dispatch('checkout/onepage/shippingMethod');
        $this->assertContains('no quotes are available', $this->getResponse()->getBody());
    }

    public function testReviewAction()
    {
        $this->dispatch('checkout/onepage/review');
        $this->assertContains('Place Order', $this->getResponse()->getBody());
        $this->assertContains('checkout-review', $this->getResponse()->getBody());
    }

    public function testSaveOrderActionWithoutFormKey()
    {
        $this->dispatch('checkout/onepage/saveOrder');
        $this->assertRedirect($this->stringContains('checkout/onepage'));
    }

    public function testSaveOrderActionWithFormKey()
    {
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $this->getRequest()->setParam('form_key', $formKey->getFormKey());
        $this->dispatch('checkout/onepage/saveOrder');
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            '{"success":false,"error":true,"error_messages":"Please specify a shipping method."}',
            $html,
            $html
        );
    }
}
