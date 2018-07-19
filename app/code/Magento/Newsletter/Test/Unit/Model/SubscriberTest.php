<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newsletterData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;

    protected function setUp()
    {
        $this->newsletterData = $this->createMock(\Magento\Newsletter\Helper\Data::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->transportBuilder = $this->createPartialMock(\Magento\Framework\Mail\Template\TransportBuilder::class, [
                'setTemplateIdentifier',
                'setTemplateOptions',
                'setTemplateVars',
                'setFrom',
                'addTo',
                'getTransport'
            ]);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, [
                'isLoggedIn',
                'getCustomerDataObject',
                'getCustomerId'
            ]);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->customerAccountManagement = $this->createMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->inlineTranslation = $this->createMock(\Magento\Framework\Translate\Inline\StateInterface::class);
        $this->resource = $this->createPartialMock(\Magento\Newsletter\Model\ResourceModel\Subscriber::class, [
                'loadByEmail',
                'getIdFieldName',
                'save',
                'loadByCustomerData',
                'received'
            ]);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subscriber = $this->objectManager->getObject(
            \Magento\Newsletter\Model\Subscriber::class,
            [
                'newsletterData' => $this->newsletterData,
                'scopeConfig' => $this->scopeConfig,
                'transportBuilder' => $this->transportBuilder,
                'storeManager' => $this->storeManager,
                'customerSession' => $this->customerSession,
                'customerRepository' => $this->customerRepository,
                'customerAccountManagement' => $this->customerAccountManagement,
                'inlineTranslation' => $this->inlineTranslation,
                'resource' => $this->resource
            ]
        );
    }

    public function testSubscribe()
    {
        $email = 'subscriber_email@magento.com';
        $this->resource->expects($this->any())->method('loadByEmail')->willReturn(
            [
                'subscriber_status' => 3,
                'subscriber_email' => $email,
                'name' => 'subscriber_name'
            ]
        );
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn(true);
        $this->customerSession->expects($this->any())->method('isLoggedIn')->willReturn(true);
        $customerDataModel = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerSession->expects($this->any())->method('getCustomerDataObject')->willReturn($customerDataModel);
        $this->customerSession->expects($this->any())->method('getCustomerId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($customerDataModel);
        $customerDataModel->expects($this->any())->method('getStoreId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getId')->willReturn(1);
        $this->sendEmailCheck();
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->assertEquals(1, $this->subscriber->subscribe($email));
    }

    public function testSubscribeNotLoggedIn()
    {
        $email = 'subscriber_email@magento.com';
        $this->resource->expects($this->any())->method('loadByEmail')->willReturn(
            [
                'subscriber_status' => 3,
                'subscriber_email' => $email,
                'name' => 'subscriber_name'
            ]
        );
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn(true);
        $this->customerSession->expects($this->any())->method('isLoggedIn')->willReturn(false);
        $customerDataModel = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerSession->expects($this->any())->method('getCustomerDataObject')->willReturn($customerDataModel);
        $this->customerSession->expects($this->any())->method('getCustomerId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($customerDataModel);
        $customerDataModel->expects($this->any())->method('getStoreId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getId')->willReturn(1);
        $this->sendEmailCheck();
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();

        $this->assertEquals(2, $this->subscriber->subscribe($email));
    }

    public function testUpdateSubscription()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)->willReturn($customerDataMock);
        $this->resource->expects($this->atLeastOnce())
            ->method('loadByCustomerData')
            ->with($customerDataMock)
            ->willReturn(
                [
                    'subscriber_id' => 1,
                    'subscriber_status' => 1
                ]
            );
        $customerDataMock->expects($this->atLeastOnce())->method('getId')->willReturn('id');
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $this->customerAccountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($customerId)
            ->willReturn('account_confirmation_required');
        $customerDataMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $customerDataMock->expects($this->once())->method('getEmail')->willReturn('email');

        $storeModel = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($storeModel);

        $this->assertEquals($this->subscriber, $this->subscriber->updateSubscription($customerId));
    }

    public function testUnsubscribeCustomerById()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)->willReturn($customerDataMock);
        $this->resource->expects($this->atLeastOnce())
            ->method('loadByCustomerData')
            ->with($customerDataMock)
            ->willReturn(
                [
                    'subscriber_id' => 1,
                    'subscriber_status' => 1
                ]
            );
        $customerDataMock->expects($this->atLeastOnce())->method('getId')->willReturn('id');
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $customerDataMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $customerDataMock->expects($this->once())->method('getEmail')->willReturn('email');
        $this->sendEmailCheck();

        $this->subscriber->unsubscribeCustomerById($customerId);
    }

    public function testSubscribeCustomerById()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)->willReturn($customerDataMock);
        $this->resource->expects($this->atLeastOnce())
            ->method('loadByCustomerData')
            ->with($customerDataMock)
            ->willReturn(
                [
                    'subscriber_id' => 1,
                    'subscriber_status' => 3
                ]
            );
        $customerDataMock->expects($this->atLeastOnce())->method('getId')->willReturn('id');
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $customerDataMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $customerDataMock->expects($this->once())->method('getEmail')->willReturn('email');
        $this->sendEmailCheck();

        $this->subscriber->subscribeCustomerById($customerId);
    }

    public function testSubscribeCustomerById1()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)->willReturn($customerDataMock);
        $this->resource->expects($this->atLeastOnce())
            ->method('loadByCustomerData')
            ->with($customerDataMock)
            ->willReturn(
                [
                    'subscriber_id' => 1,
                    'subscriber_status' => 3
                ]
            );
        $customerDataMock->expects($this->atLeastOnce())->method('getId')->willReturn('id');
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $customerDataMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $customerDataMock->expects($this->once())->method('getEmail')->willReturn('email');
        $this->sendEmailCheck();
        $this->customerAccountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->willReturn(\Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED);
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->with()->willReturn(true);

        $this->subscriber->subscribeCustomerById($customerId);
        $this->assertEquals(\Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE, $this->subscriber->getStatus());
    }

    public function testSubscribeCustomerByIdAfterConfirmation()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)->willReturn($customerDataMock);
        $this->resource->expects($this->atLeastOnce())
            ->method('loadByCustomerData')
            ->with($customerDataMock)
            ->willReturn(
                [
                    'subscriber_id' => 1,
                    'subscriber_status' => 4
                ]
            );
        $customerDataMock->expects($this->atLeastOnce())->method('getId')->willReturn('id');
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $customerDataMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $customerDataMock->expects($this->once())->method('getEmail')->willReturn('email');
        $this->sendEmailCheck();
        $this->customerAccountManagement->expects($this->never())->method('getConfirmationStatus');
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->with()->willReturn(true);

        $this->subscriber->updateSubscription($customerId);
        $this->assertEquals(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED, $this->subscriber->getStatus());
    }

    public function testUnsubscribe()
    {
        $this->resource->expects($this->once())->method('save')->willReturnSelf();
        $this->sendEmailCheck();

        $this->assertEquals($this->subscriber, $this->subscriber->unsubscribe());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This is an invalid subscription confirmation code.
     */
    public function testUnsubscribeException()
    {
        $this->subscriber->setCode(111);
        $this->subscriber->setCheckCode(222);

        $this->subscriber->unsubscribe();
    }

    public function testGetSubscriberFullName()
    {
        $this->subscriber->setFirstname('John');
        $this->subscriber->setLastname('Doe');

        $this->assertEquals('John Doe', $this->subscriber->getSubscriberFullName());
    }

    public function testConfirm()
    {
        $code = 111;
        $this->subscriber->setCode($code);
        $this->resource->expects($this->once())->method('save')->willReturnSelf();

        $this->assertTrue($this->subscriber->confirm($code));
    }

    public function testConfirmWrongCode()
    {
        $code = 111;
        $this->subscriber->setCode(222);

        $this->assertFalse($this->subscriber->confirm($code));
    }

    public function testReceived()
    {
        $queue = $this->getMockBuilder(\Magento\Newsletter\Model\Queue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('received')->with($this->subscriber, $queue)->willReturnSelf();

        $this->assertEquals($this->subscriber, $this->subscriber->received($queue));
    }

    /**
     * @return $this
     */
    protected function sendEmailCheck()
    {
        $storeModel = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn(true);
        $this->transportBuilder->expects($this->once())->method('setTemplateIdentifier')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setTemplateOptions')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setTemplateVars')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('setFrom')->willReturnSelf();
        $this->transportBuilder->expects($this->once())->method('addTo')->willReturnSelf();
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($storeModel);
        $storeModel->expects($this->any())->method('getId')->willReturn(1);
        $this->transportBuilder->expects($this->once())->method('getTransport')->willReturn($transport);
        $transport->expects($this->once())->method('sendMessage')->willReturnSelf();
        $this->inlineTranslation->expects($this->once())->method('suspend')->willReturnSelf();
        $this->inlineTranslation->expects($this->once())->method('resume')->willReturnSelf();

        return $this;
    }
}
