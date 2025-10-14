<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test to update newsletter subscription status
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionManagerTest extends TestCase
{
    /**
     * @var SubscriberFactory|MockObject
     */
    private $subscriberFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subscriberFactory = $this->createMock(SubscriberFactory::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->customerAccountManagement = $this->getMockForAbstractClass(AccountManagementInterface::class);
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);

        $objectManager = new ObjectManager($this);
        $this->subscriptionManager = $objectManager->getObject(
            SubscriptionManager::class,
            [
                'subscriberFactory' => $this->subscriberFactory,
                'logger' => $this->logger,
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'customerAccountManagement' => $this->customerAccountManagement,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }

    /**
     * Test to Subscribe to newsletters by email
     *
     * @param array $subscriberData
     * @param string $email
     * @param int $storeId
     * @param bool $isConfirmNeed
     * @param array $expectedData
     * @dataProvider subscribeDataProvider
     */
    public function testSubscribe(
        array $subscriberData,
        string $email,
        int $storeId,
        bool $isConfirmNeed,
        array $expectedData
    ): void {
        $websiteId = 1;
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        /** @var Subscriber|MockObject $subscriber */
        $subscriber = $this->createPartialMock(
            Subscriber::class,
            [
                'loadBySubscriberEmail',
                'randomSequence',
                'save',
                'sendConfirmationRequestEmail',
                'sendConfirmationSuccessEmail',
                'sendUnsubscriptionEmail'
            ]
        );
        $subscriber->expects($this->once())
            ->method('loadBySubscriberEmail')
            ->with($email, $websiteId)
            ->willReturnSelf();
        $subscriber->setData($subscriberData);
        if (empty($subscriberData['id'])) {
            $subscriber->method('randomSequence')->willReturn($expectedData['subscriber_confirm_code']);
        }
        $this->subscriberFactory->method('create')->willReturn($subscriber);
        $this->scopeConfig->method('isSetFlag')
            ->with(Subscriber::XML_PATH_CONFIRMATION_FLAG, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($isConfirmNeed);

        $this->assertEquals($subscriber, $this->subscriptionManager->subscribe($email, $storeId));
        $this->assertEquals($expectedData, $subscriber->getData());
    }

    /**
     * Subscribe customer data provider
     *
     * @return array
     */
    public static function subscribeDataProvider(): array
    {
        return [
            'Subscribe new' => [
                'subscriberData' => [],
                'email' => 'email@example.com',
                'storeId' => 1,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
            ],
            'Subscribe new: confirm required' => [
                'subscriberData' => [],
                'email' => 'email@example.com',
                'storeId' => 1,
                'isConfirmNeed' => true,
                'expectedData' => [
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
            ],
            'Subscribe existing' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                    'customer_id' => 0,
                ],
                'email' => 'email@example.com',
                'storeId' => 1,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                    'customer_id' => 0,
                ],
            ],
        ];
    }

    /**
     * Test to Unsubscribe from newsletters by email
     */
    public function testUnsubscribe(): void
    {
        $email = 'email@example.com';
        $storeId = 2;
        $websiteId = 1;
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        $confirmCode = 'confirm code';
        /** @var Subscriber|MockObject $subscriber */
        $subscriber = $this->getMockBuilder(Subscriber::class)
            ->addMethods(['setCheckCode'])
            ->onlyMethods(['loadBySubscriberEmail', 'getId', 'unsubscribe'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber->expects($this->once())
            ->method('loadBySubscriberEmail')
            ->with($email, $websiteId)
            ->willReturnSelf();
        $subscriber->method('getId')->willReturn(1);
        $subscriber->expects($this->once())->method('setCheckCode')->with($confirmCode)->willReturnSelf();
        $subscriber->expects($this->once())->method('unsubscribe')->willReturnSelf();
        $this->subscriberFactory->method('create')->willReturn($subscriber);

        $this->assertEquals(
            $subscriber,
            $this->subscriptionManager->unsubscribe($email, $storeId, $confirmCode)
        );
    }

    /**
     * Test to Subscribe customer to newsletter
     *
     * @param array $subscriberData
     * @param array $customerData
     * @param int $storeId
     * @param bool $isConfirmNeed
     * @param array $expectedData
     * @param bool $needToSendEmail
     * @dataProvider subscribeCustomerDataProvider
     */
    public function testSubscribeCustomer(
        array $subscriberData,
        array $customerData,
        int $storeId,
        bool $isConfirmNeed,
        array $expectedData,
        bool $needToSendEmail
    ): void {
        $websiteId = 1;
        $customerId = $customerData['id'];
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($customerData['email']);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        /** @var Subscriber|MockObject $subscriber */
        $subscriber = $this->createPartialMock(
            Subscriber::class,
            [
                'loadByCustomer',
                'loadBySubscriberEmail',
                'randomSequence',
                'save',
                'sendConfirmationRequestEmail',
                'sendConfirmationSuccessEmail',
                'sendUnsubscriptionEmail'
            ]
        );
        $subscriber->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId, $websiteId)
            ->willReturnSelf();
        if (empty($subscriberData['subscriber_id'])) {
            $subscriber->expects($this->once())
                ->method('loadBySubscriberEmail')
                ->with($customerData['email'], $websiteId)
                ->willReturnSelf();
        }
        $subscriber->setData($subscriberData);
        if (empty($subscriberData['subscriber_id'])) {
            $subscriber->method('randomSequence')->willReturn($expectedData['subscriber_confirm_code']);
        }
        $sendEmailMethod = $this->getSendEmailMethod($expectedData['subscriber_status'] ?? 0);
        if ($needToSendEmail) {
            $subscriber->expects($this->once())->method($sendEmailMethod);
        } else {
            $subscriber->expects($this->never())->method('sendConfirmationRequestEmail');
            $subscriber->expects($this->never())->method('sendConfirmationSuccessEmail');
            $subscriber->expects($this->never())->method('sendUnsubscriptionEmail');
        }
        $this->subscriberFactory->method('create')->willReturn($subscriber);
        $this->scopeConfig->method('isSetFlag')
            ->with(Subscriber::XML_PATH_CONFIRMATION_FLAG, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($isConfirmNeed);
        $this->customerAccountManagement
            ->method('getConfirmationStatus')
            ->willReturn($customerData['confirmation_status']);

        $this->assertEquals(
            $subscriber,
            $this->subscriptionManager->subscribeCustomer($customerId, $storeId)
        );
        $this->assertEquals($expectedData, $subscriber->getData());
    }

    /**
     * Get expected send email method
     *
     * @param int $status
     * @return string
     */
    private function getSendEmailMethod(int $status): string
    {
        switch ($status) {
            case Subscriber::STATUS_SUBSCRIBED:
                $sendEmailMethod = 'sendConfirmationSuccessEmail';
                break;
            case Subscriber::STATUS_NOT_ACTIVE:
                $sendEmailMethod = 'sendConfirmationRequestEmail';
                break;
            case Subscriber::STATUS_UNSUBSCRIBED:
                $sendEmailMethod = 'sendUnsubscriptionEmail';
                break;
            default:
                $sendEmailMethod = '';
        }

        return $sendEmailMethod;
    }

    /**
     * Subscribe customer data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function subscribeCustomerDataProvider(): array
    {
        return [
            'Subscribe new' => [
                'subscriberData' => [],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMED,
                ],
                'storeId' => 1,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Subscribe new: customer confirm required' => [
                'subscriberData' => [],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                ],
                'storeId' => 1,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNCONFIRMED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => false,
            ],
            'Subscribe existing' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMED,
                ],
                'storeId' => 1,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Subscribe existing: subscription confirm required' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMED,
                ],
                'storeId' => 1,
                'isConfirmNeed' => true,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Update subscription data' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email2@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMED,
                ],
                'storeId' => 2,
                'isConfirmNeed' => false,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email2@example.com',
                    'store_id' => 2,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Update subscription data: subscription confirm required ' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email2@example.com',
                    'confirmation_status' => AccountManagementInterface::ACCOUNT_CONFIRMED,
                ],
                'storeId' => 2,
                'isConfirmNeed' => true,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email2@example.com',
                    'store_id' => 2,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
        ];
    }

    /**
     * Test to Unsubscribe customer from newsletter
     *
     * @param array $subscriberData
     * @param array $customerData
     * @param int $storeId
     * @param array $expectedData
     * @param bool $needToSendEmail
     * @dataProvider unsubscribeCustomerDataProvider
     */
    public function testUnsubscribeCustomer(
        array $subscriberData,
        array $customerData,
        int $storeId,
        array $expectedData,
        bool $needToSendEmail
    ): void {
        $websiteId = 1;
        $customerId = $customerData['id'];
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);
        $customer->method('getEmail')->willReturn($customerData['email']);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        /** @var Subscriber|MockObject $subscriber */
        $subscriber = $this->createPartialMock(
            Subscriber::class,
            [
                'loadByCustomer',
                'loadBySubscriberEmail',
                'randomSequence',
                'save',
                'sendConfirmationRequestEmail',
                'sendConfirmationSuccessEmail',
                'sendUnsubscriptionEmail'
            ]
        );
        $subscriber->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId, $websiteId)
            ->willReturnSelf();
        if (empty($subscriberData['subscriber_id'])) {
            $subscriber->expects($this->once())
                ->method('loadBySubscriberEmail')
                ->with($customerData['email'], $websiteId)
                ->willReturnSelf();
        }
        $subscriber->setData($subscriberData);
        $sendEmailMethod = $this->getSendEmailMethod($expectedData['subscriber_status'] ?? 0);
        if ($needToSendEmail) {
            $subscriber->expects($this->once())->method($sendEmailMethod);
        } else {
            $subscriber->expects($this->never())->method('sendConfirmationRequestEmail');
            $subscriber->expects($this->never())->method('sendConfirmationSuccessEmail');
            $subscriber->expects($this->never())->method('sendUnsubscriptionEmail');
        }
        $this->subscriberFactory->method('create')->willReturn($subscriber);

        $this->assertEquals(
            $subscriber,
            $this->subscriptionManager->unsubscribeCustomer($customerId, $storeId)
        );
        $this->assertEquals($expectedData, $subscriber->getData());
    }

    /**
     * Unsubscribe customer data provider
     *
     * @return array
     */
    public static function unsubscribeCustomerDataProvider(): array
    {
        return [
            'Unsubscribe new' => [
                'subscriberData' => [],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                ],
                'storeId' => 1,
                'expectedData' => [
                ],
                'needToSendEmail' => false,
            ],
            'Unsubscribe existing' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_SUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                ],
                'storeId' => 1,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Unsubscribe existing: subscription confirm required' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email@example.com',
                ],
                'storeId' => 1,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_NOT_ACTIVE,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
            'Update subscription data' => [
                'subscriberData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email@example.com',
                    'store_id' => 1,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'customerData' => [
                    'id' => 1,
                    'email' => 'email2@example.com',
                ],
                'storeId' => 2,
                'expectedData' => [
                    'subscriber_id' => 1,
                    'customer_id' => 1,
                    'subscriber_email' => 'email2@example.com',
                    'store_id' => 2,
                    'subscriber_status' => Subscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_confirm_code' => '',
                ],
                'needToSendEmail' => true,
            ],
        ];
    }
}
