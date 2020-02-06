<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller;

use Magento\Customer\Model\Session;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for \Magento\Newsletter\Controller\Subscriber.
 */
class SubscriberTest extends AbstractController
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Subscribe test
     *
     * @return void
     */
    public function testNewAction(): void
    {
        $this->getRequest()->setMethod('POST');

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->isEmpty());
        $this->assertRedirect($this->anything());
    }

    /**
     * Subscribe unused email
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testNewActionUnusedEmail(): void
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
     * Subscribe used email
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testNewActionUsedEmail(): void
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'email' => 'customer@example.com',
        ]);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo([
                'Thank you for your subscription.',
            ]));
        $this->assertRedirect($this->anything());
    }

    /**
     * Subscribe owner email
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testNewActionOwnerEmail(): void
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
     * Subscribe with disabled newsletter config
     *
     * @magentoConfigFixture current_store newsletter/general/active 0
     *
     * @return void
     */
    public function testWithDisabledNewsletterOption(): void
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'email' => 'customer@example.com',
        ]);
        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->isEmpty());
        $this->assertRedirect($this->stringContains('cms/noroute/index'));
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId): void
    {
        /** @var Session $session */
        $session = Bootstrap::getObjectManager()->get(Session::class);
        $session->loginById($customerId);
    }
}
