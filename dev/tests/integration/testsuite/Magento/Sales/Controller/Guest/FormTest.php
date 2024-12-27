<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Guest;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * @magentoAppIsolation enabled
 */
class FormTest extends AbstractController
{
    /**
     * Test view order as guest with correct data
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testViewOrderAsGuest()
    {
        $this->prepareRequestData();
        $this->dispatch('sales/guest/view/');
        $content = $this->getResponse()->getBody();
        $this->assertStringContainsString('Order # 100000001', $content);
    }

    /**
     * View order as logged in customer
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testViewOrderAsLoggedIn()
    {
        $this->login(1);
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->dispatch('sales/guest/view/');
        $this->assertRedirect($this->stringContains('sales/order/history/'));
    }

    /**
     * Test attempting to open the Returns form as logged in customer
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAttemptToOpenTheFormAsLoggedIn()
    {
        $this->login(1);
        $this->dispatch('sales/guest/form/');
        $this->assertRedirect($this->stringContains('sales/order/history'));
    }

    /**
     * Test Return Order for guest with incorrect data
     */
    public function testViewOrderAsGuestWithIncorrectData()
    {
        $this->prepareRequestData(true);
        $this->dispatch('sales/guest/view/');
        $this->assertSessionMessages(
            $this->equalTo(['You entered incorrect data. Please try again.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $session->loginById($customerId);
    }

    /**
     * @param bool $invalidData
     * @return void
     */
    private function prepareRequestData($invalidData = false)
    {
        $orderId = 100000001;
        $email = $invalidData ? 'wrong@example.com' : 'customer@example.com';

        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'oar_order_id' => $orderId,
            'oar_billing_lastname' => 'lastname',
            'oar_type' => 'email',
            'oar_email' => $email,
            'oar_zip' => '',
            'form_key' => $formKey->getFormKey(),
        ];

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($post);
    }
}
