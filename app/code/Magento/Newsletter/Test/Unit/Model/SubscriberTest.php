<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model;

class SubscriberTest extends \PHPUnit_Framework_TestCase
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
        $this->newsletterData = $this->getMock('Magento\Newsletter\Helper\Data', [], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->transportBuilder = $this->getMock(
            'Magento\Framework\Mail\Template\TransportBuilder',
            [
                'setTemplateIdentifier',
                'setTemplateOptions',
                'setTemplateVars',
                'setFrom',
                'addTo',
                'getTransport'
            ],
            [],
            '',
            false
        );
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            [
                'isLoggedIn',
                'getCustomerDataObject',
                'getCustomerId'
            ],
            [],
            '',
            false
        );
        $this->customerRepository = $this->getMock('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerAccountManagement = $this->getMock('Magento\Customer\Api\AccountManagementInterface');
        $this->inlineTranslation = $this->getMock('Magento\Framework\Translate\Inline\StateInterface');
        $this->resource = $this->getMock(
            'Magento\Newsletter\Model\ResourceModel\Subscriber',
            [
                'loadByEmail',
                'getIdFieldName',
                'save',
                'loadByCustomerData',
                'received'
            ],
            [],
            '',
            false
        );
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subscriber = $this->objectManager->getObject(
            'Magento\Newsletter\Model\Subscriber',
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
        $customerDataModel = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
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
        $customerDataModel = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
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
        $customerDataMock = $this->getMockBuilder('\Magento\Customer\Api\Data\CustomerInterface')
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

        $this->assertEquals($this->subscriber, $this->subscriber->updateSubscription($customerId));
    }

    public function testUnsubscribeCustomerById()
    {
        $customerId = 1;
        $customerDataMock = $this->getMockBuilder('\Magento\Customer\Api\Data\CustomerInterface')
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
        $customerDataMock = $this->getMockBuilder('\Magento\Customer\Api\Data\CustomerInterface')
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
        $queue = $this->getMockBuilder('\Magento\Newsletter\Model\Queue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('received')->with($this->subscriber, $queue)->willReturnSelf();

        $this->assertEquals($this->subscriber, $this->subscriber->received($queue));
    }

    protected function sendEmailCheck()
    {
        $storeModel = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');
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
