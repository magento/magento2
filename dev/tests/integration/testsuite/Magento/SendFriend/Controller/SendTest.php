<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Controller;

use Magento\Customer\Model\Session;
use Magento\SendFriend\Model\SendFriend;
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class for send friend send action
 *
 * @magentoAppArea frontend
 *
 * @see \Magento\SendFriend\Controller\Product\Send
 */
class SendTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->customerSession->logout();
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 0
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testSendMailNotAllowed(): void
    {
        $this->dispatchWithProductIdParam(6);
        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     * @magentoConfigFixture current_store sendfriend/email/allow_guest 0
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testGuestSendMailNotAllowed(): void
    {
        $this->dispatchWithProductIdParam(6);
        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     * @magentoConfigFixture current_store sendfriend/email/allow_guest 1
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testGuestSendMailAllowed(): void
    {
        $this->dispatchWithProductIdParam(6);
        $this->assertEquals(Response::STATUS_CODE_200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testLoggedInCustomer(): void
    {
        $this->customerSession->loginById(1);
        $this->dispatchWithProductIdParam(6);
        $this->assertEquals(Response::STATUS_CODE_200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testWithoutProductId(): void
    {
        $this->customerSession->loginById(1);
        $this->dispatch('sendfriend/product/send/');
        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/enabled 1
     * @magentoConfigFixture current_store sendfriend/email/max_per_hour 1
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testToMachSendRequests(): void
    {
        $this->createSendFriendMock();
        $this->customerSession->loginById(1);
        $this->dispatchWithProductIdParam(6);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You can\'t send messages more than 5 times an hour.')])
        );
        $this->assertEquals(Response::STATUS_CODE_200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * Set product id parameter and dispatch controller
     *
     * @param int $productId
     * @return void
     */
    private function dispatchWithProductIdParam(int $productId): void
    {
        $this->getRequest()->setParam('id', $productId);
        $this->dispatch('sendfriend/product/send/');
    }

    /**
     * Create mock to imitate to mach send requests
     *
     * @return void
     */
    private function createSendFriendMock(): void
    {
        $mock = $this->createMock(SendFriend::class);
        $mock->method('isExceedLimit')->willReturn(true);
        $mock->method('getMaxSendsToFriend')->willReturn(5);
        $this->_objectManager->addSharedInstance($mock, SendFriend::class);
    }
}
