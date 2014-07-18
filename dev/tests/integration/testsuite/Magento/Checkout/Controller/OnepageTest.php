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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->assertSelectCount('[id="checkout-payment-method-load"]', 1, $html);
        $this->assertSelectCount('form[id="co-billing-form"][action=""]', 1, $html);
        $this->assertSelectCount('form[id="co-payment-form"] input[name="form_key"]', 1, $html);
    }

    /**
     * Covers app/code/Magento/Checkout/Block/Onepage/Payment/Info.php
     */
    public function testProgressAction()
    {
        $steps = array(
            'payment' => array('is_show' => true, 'complete' => true),
            'billing' => array('is_show' => true),
            'shipping' => array('is_show' => true),
            'shipping_method' => array('is_show' => true)
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Checkout\Model\Session'
        )->setSteps(
            $steps
        );

        $this->dispatch('checkout/onepage/progress');
        $html = $this->getResponse()->getBody();
        $this->assertContains('Checkout', $html);
        $methodTitle = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Checkout\Model\Session'
        )->getQuote()->getPayment()->getMethodInstance()->getTitle();
        $this->assertContains('<dt class="title">' . $methodTitle . '</dt>', $html);
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
        $formKey = $this->_objectManager->get('\Magento\Framework\Data\Form\FormKey');
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
