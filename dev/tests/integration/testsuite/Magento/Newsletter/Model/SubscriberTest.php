<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model;

class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var Subscriber
     */
    protected $_model;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(
            \Magento\Newsletter\Model\Subscriber::class
        );
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     */
    public function testEmailConfirmation()
    {
        $this->_model->subscribe('customer_confirm@example.com');
        $transportBuilder = $this->objectManager
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        // confirmationCode 'ysayquyajua23iq29gxwu2eax2qb6gvy' is taken from fixture
        $this->assertContains(
            '/newsletter/subscriber/confirm/id/' . $this->_model->getSubscriberId()
            . '/code/ysayquyajua23iq29gxwu2eax2qb6gvy',
            $transportBuilder->getSentMessage()->getRawMessage()
        );
        $this->assertEquals(Subscriber::STATUS_NOT_ACTIVE, $this->_model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testLoadByCustomerId()
    {
        $this->assertSame($this->_model, $this->_model->loadByCustomerId(1));
        $this->assertEquals('customer@example.com', $this->_model->getSubscriberEmail());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribe()
    {
        // Unsubscribe and verify
        $this->assertSame($this->_model, $this->_model->loadByCustomerId(1));
        $this->assertEquals($this->_model, $this->_model->unsubscribe());
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->_model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->subscribe('customer@example.com'));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribeByCustomerId()
    {
        // Unsubscribe and verify
        $this->assertSame($this->_model, $this->_model->unsubscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->_model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertSame($this->_model, $this->_model->subscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testSubscribeGuestRegisterCustomerWithSameEmailFromDifferentStore()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $defaultStoreId = $store->load('default', 'code')->getId();
        $secondStoreId = $store->load('fixture_second_store', 'code')->getId();

        $email = 'test@example.com';

        // Subscribing guest to a newsletter from default store
        $this->_model->setStoreId($defaultStoreId)
            ->setCustomerId(0)
            ->setSubscriberEmail($email)
            ->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED)
            ->save();

        // Registering customer with the same email from second store
        $customer = $this->objectManager->create(
            \Magento\Customer\Model\Data\Customer::class,
            [
                'data' => [
                    \Magento\Customer\Model\Data\Customer::FIRSTNAME => 'John',
                    \Magento\Customer\Model\Data\Customer::LASTNAME => 'Doe',
                    \Magento\Customer\Model\Data\Customer::EMAIL => $email,
                    \Magento\Customer\Model\Data\Customer::STORE_ID => $secondStoreId,
                ]
            ]
        );

        /** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
        $accountManagement = $this->objectManager->get(\Magento\Customer\Api\AccountManagementInterface::class);
        $customer = $accountManagement->createAccount($customer);

        /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $subscribers */
        $subscribers = $this->_model->getCollection()->addFieldToFilter('subscriber_email', $email);

        $this->assertCount(1, $subscribers->getItems());
        $this->assertEquals($subscribers->getFirstItem()->getCustomerId(), $customer->getId());
    }
}
