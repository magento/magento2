<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Newsletter\Model\Resource\Subscriber|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
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
            'Magento\Newsletter\Model\Resource\Subscriber',
            [
                'loadByEmail',
                'getIdFieldName',
                'save'
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
        $this->resource->expects($this->any())->method('getIdFieldName')->willReturn('id_field');
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn(true);
        $this->customerSession->expects($this->any())->method('isLoggedIn')->willReturn(true);
        $customerDataModel = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
        $this->customerSession->expects($this->any())->method('getCustomerDataObject')->willReturn($customerDataModel);
        $this->customerSession->expects($this->any())->method('getCustomerId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getEmail')->willReturn($email);
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($customerDataModel);
        $customerDataModel->expects($this->any())->method('getStoreId')->willReturn(1);
        $customerDataModel->expects($this->any())->method('getId')->willReturn(1);
        $this->transportBuilder->expects($this->any())->method('setTemplateIdentifier')->willReturnSelf();
        $this->transportBuilder->expects($this->any())->method('setTemplateOptions')->willReturnSelf();
        $this->transportBuilder->expects($this->any())->method('setTemplateVars')->willReturnSelf();
        $this->transportBuilder->expects($this->any())->method('setFrom')->willReturnSelf();
        $this->transportBuilder->expects($this->any())->method('addTo')->willReturnSelf();
        $storeModel = $this->getMock('\Magento\Store\Model\Store', ['getId'], [], '', false);
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('owner_email@magento.com');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($storeModel);
        $storeModel->expects($this->any())->method('getId')->willReturn(1);
        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');
        $this->transportBuilder->expects($this->any())->method('getTransport')->willReturn($transport);
        $transport->expects($this->any())->method('sendMessage')->willReturnSelf();
        $inlineTranslation = $this->getMock('Magento\Framework\Translate\Inline\StateInterface');
        $inlineTranslation->expects($this->any())->method('resume')->willReturnSelf();
        $this->resource->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $this->assertEquals(1, $this->subscriber->subscribe($email));
    }
}
