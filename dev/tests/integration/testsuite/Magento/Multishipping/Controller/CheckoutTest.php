<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Multishipping\Controller\Checkout
 *
 * @magentoAppArea frontend
 */
class CheckoutTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Covers app/code/Magento/Checkout/Block/Multishipping/Payment/Info.php
     * and app/code/Magento/Checkout/Block/Multishipping/Overview.php
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture current_store multishipping/options/checkout_multiple 1
     */
    public function testOverviewAction()
    {
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Session')
            ->setQuoteId($quote->getId());

        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);

        /** @var $session \Magento\Customer\Model\Session */
        $session = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session', [$logger]);

        /** @var \Magento\Customer\Api\AccountManagementInterface  $service */
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\AccountManagementInterface');
        $customer = $service->authenticate('customer@example.com', 'password');

        $session->setCustomerDataAsLoggedIn($customer);
        $this->getRequest()->setPost('payment', ['method' => 'checkmo']);
        $this->dispatch('multishipping/checkout/overview');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<div class="box box-billing-method">', $html);
        $this->assertContains('<div class="box box-shipping-method">', $html);
        $this->assertContains(
            '<dt class="title">' . $quote->getPayment()->getMethodInstance()->getTitle() . '</dt>',
            $html
        );
        $this->assertContains('<span class="price">$10.00</span>', $html);
        $this->assertContains('<input name="form_key" type="hidden" value="' . $formKey->getFormKey(), $html);
    }
}
