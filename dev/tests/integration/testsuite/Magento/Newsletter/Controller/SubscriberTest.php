<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber
 */
class SubscriberTest extends AbstractController
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testNewAction()
    {
        $this->getRequest()->setMethod('POST');

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->isEmpty());
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testNewActionUnusedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'email' => 'not_used@example.com',
        ]);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionUsedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'email' => 'customer@example.com',
        ]);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo([
                'There was a problem with the subscription: This email address is already assigned to another user.',
            ]));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionOwnerEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'email' => 'customer@example.com',
        ]);
        $this->login(1);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Model\Session');
        $session->loginById($customerId);
    }
}
