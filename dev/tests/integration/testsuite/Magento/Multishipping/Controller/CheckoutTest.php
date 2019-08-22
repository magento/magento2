<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller;

use \Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Test class for \Magento\Multishipping\Controller\Checkout
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Sales/_files/quote.php
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class CheckoutTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class);
        $this->checkoutSession = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);

        $this->quote->load('test01', 'reserved_order_id');
        $this->checkoutSession->setQuoteId($this->quote->getId());
        $this->checkoutSession->setCartWasUpdated(false);
    }

    /**
     * Covers \Magento\Multishipping\Block\Checkout\Payment\Info and \Magento\Multishipping\Block\Checkout\Overview
     *
     * @magentoConfigFixture current_store multishipping/options/checkout_multiple 1
     */
    public function testOverviewAction()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        /** @var \Magento\Customer\Api\AccountManagementInterface $service */
        $service = $this->_objectManager->create(\Magento\Customer\Api\AccountManagementInterface::class);
        $customer = $service->authenticate('customer@example.com', 'password');
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class, [$logger]);
        $customerSession->setCustomerDataAsLoggedIn($customer);
        $this->checkoutSession->setCheckoutState(State::STEP_BILLING);
        $this->getRequest()->setPostValue('payment', ['method' => 'checkmo']);
        $this->dispatch('multishipping/checkout/overview');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<div class="box box-billing-method">', $html);
        $this->assertContains('<div class="box box-shipping-method">', $html);
        $this->assertContains(
            '<dt class="title">' . $this->quote->getPayment()->getMethodInstance()->getTitle() . '</dt>',
            $html
        );
        $this->assertContains('<span class="price">$10.00</span>', $html);
        $this->assertContains('<input name="form_key" type="hidden" value="' . $formKey->getFormKey(), $html);
    }
}
