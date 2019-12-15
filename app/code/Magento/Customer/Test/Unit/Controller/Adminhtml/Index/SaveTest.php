<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Testing Save Customer use case from admin page
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \Magento\Customer\Controller\Adminhtml\Index\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\Save
     */
    protected $model;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataFactoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMapperMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managementMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataFactoryMock;

    /**
     * @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotificationMock;

    /**
     * @var \Magento\Customer\Model\Address\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressMapperMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressRepositoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsCustomerFormData', 'setCustomerFormData'])
            ->getMock();
        $this->formFactoryMock = $this->getMockBuilder(\Magento\Customer\Model\Metadata\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerDataFactoryMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressRepositoryMock = $this->getMockBuilder(
            \Magento\Customer\Api\AddressRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->customerMapperMock = $this->getMockBuilder(
            \Magento\Customer\Model\Customer\Mapper::class
        )->disableOriginalConstructor()->getMock();
        $this->customerAddressMapperMock = $this->getMockBuilder(
            \Magento\Customer\Model\Address\Mapper::class
        )->disableOriginalConstructor()->getMock();
        $this->dataHelperMock = $this->getMockBuilder(
            \Magento\Framework\Api\DataObjectHelper::class
        )->disableOriginalConstructor()->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriberFactoryMock = $this->getMockBuilder(\Magento\Newsletter\Model\SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->managementMock = $this->getMockBuilder(\Magento\Customer\Model\AccountManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAccount'])
            ->getMock();
        $this->addressDataFactoryMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->emailNotificationMock = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Save::class,
            [
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'formFactory' => $this->formFactoryMock,
                'objectFactory' => $this->objectFactoryMock,
                'customerDataFactory' => $this->customerDataFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerMapper' => $this->customerMapperMock,
                'dataObjectHelper' => $this->dataHelperMock,
                'subscriberFactory' => $this->subscriberFactoryMock,
                'coreRegistry' => $this->registryMock,
                'customerAccountManagement' => $this->managementMock,
                'addressDataFactory' => $this->addressDataFactoryMock,
                'request' => $this->requestMock,
                'session' => $this->sessionMock,
                'authorization' => $this->authorizationMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'addressRepository' => $this->customerAddressRepositoryMock,
                'addressMapper' => $this->customerAddressMapperMock,
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'emailNotification',
            $this->emailNotificationMock
        );
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithExistentCustomer()
    {
        $customerId = 22;
        $subscription = 'true';
        $postValue = [
            'customer' => [
                'entity_id' => $customerId,
                'code' => 'value',
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'entity_id' => $customerId,
            'code' => 'value',
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];
        $compactedData = [
            'entity_id' => $customerId,
            'code' => 'value',
            'coolness' => false,
            'disable_auto_group_change' => 'false',
            CustomerInterface::DEFAULT_BILLING => 2,
            CustomerInterface::DEFAULT_SHIPPING => 2
        ];
        $savedData = [
            'entity_id' => $customerId,
            'darkness' => true,
            'name' => 'Name',
            CustomerInterface::DEFAULT_BILLING => false,
            CustomerInterface::DEFAULT_SHIPPING => false,
        ];
        $mergedData = [
            'entity_id' => $customerId,
            'darkness' => true,
            'name' => 'Name',
            'code' => 'value',
            'disable_auto_group_change' => 0,
            'confirmation' => false,
            'sendemail_store_id' => '1',
            'id' => $customerId,
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->atLeastOnce())
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturnMap(
                [
                    [null, null, $postValue],
                    [CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['customer']],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                    ['subscription', null, $subscription],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                ]
            );

        $this->objectFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            \Magento\Customer\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $customerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'customer')
            ->willReturn($extractedData);
        $customerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($compactedData);
        $customerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);
        $this->formFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturnMap(
                [
                    [
                        CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        'adminhtml_customer',
                        $savedData,
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $customerFormMock
                    ],
                ]
            );

        /** @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->customerRepositoryMock->expects($this->exactly(2))
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->customerMapperMock->expects($this->exactly(2))
            ->method('toFlatArray')
            ->with($customerMock)
            ->willReturn($savedData);

        $this->dataHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->willReturnMap(
                [
                    [
                        $customerMock,
                        $mergedData, \Magento\Customer\Api\Data\CustomerInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );

        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($customerMock)
            ->willReturnSelf();

        $customerEmail = 'customer@email.com';
        $customerMock->expects($this->once())->method('getEmail')->willReturn($customerEmail);

        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($customerMock, $customerEmail)
            ->willReturnSelf();

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(null)
            ->willReturn(true);

        /** @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriberFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($subscriberMock);

        $subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($customerId);
        $subscriberMock->expects($this->never())
            ->method('unsubscribeCustomerById');

        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer.'))
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back', false)
            ->willReturn(true);

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/*/edit', ['id' => $customerId, '_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewCustomer()
    {
        $customerId = 22;

        $subscription = '0';
        $postValue = [
            'customer' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
        ];
        $mergedData = [
            'disable_auto_group_change' => 0,
            CustomerInterface::DEFAULT_BILLING => null,
            CustomerInterface::DEFAULT_SHIPPING => null,
            'confirmation' => false,
        ];
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->atLeastOnce())
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap(
                [
                    [null, null, $postValue],
                    [CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['customer']],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                    ['subscription', null, $subscription],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                ]
            );

        $this->objectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            \Magento\Customer\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $customerFormMock->expects($this->once())
            ->method('extractData')
            ->with($this->requestMock, 'customer')
            ->willReturn($extractedData);
        $customerFormMock->expects($this->once())
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $customerFormMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturnMap(
                [
                    [
                        CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        'adminhtml_customer',
                        [],
                        false,
                        Form::DONT_IGNORE_INVISIBLE,
                        [],
                        $customerFormMock
                    ],
                ]
            );

        /** @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->dataHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->willReturnMap(
                [
                    [
                        $customerMock,
                        $mergedData, \Magento\Customer\Api\Data\CustomerInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with(null)
            ->willReturn(true);

        /** @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(\Magento\Newsletter\Model\Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriberFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($subscriberMock);

        $subscriberMock->expects($this->once())
            ->method('unsubscribeCustomerById')
            ->with($customerId);
        $subscriberMock->expects($this->never())
            ->method('subscribeCustomerById');

        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer.'))
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back', false)
            ->willReturn(false);

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/index', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewCustomerAndValidationException()
    {
        $subscription = '0';
        $postValue = [
            'customer' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
                'dob' => '3/12/1996',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap(
                [
                    [null, null, $postValue],
                    [CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['customer']],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->exactly(2))
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            \Magento\Customer\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $customerFormMock->expects($this->exactly(2))
            ->method('extractData')
            ->with($this->requestMock, 'customer')
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        /** @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willThrowException(new \Magento\Framework\Validator\Exception(__('Validator Exception')));

        $customerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsCustomerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->messageManagerMock->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error('Validator Exception'));

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewCustomerAndLocalizedException()
    {
        $subscription = '0';
        $postValue = [
            'customer' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
                'dob' => '3/12/1996',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap(
                [
                    [null, null, $postValue],
                    [CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['customer']],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->exactly(2))
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        /** @var Form|\PHPUnit_Framework_MockObject_MockObject $formMock */
        $customerFormMock = $this->getMockBuilder(
            \Magento\Customer\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $customerFormMock->expects($this->exactly(2))
            ->method('extractData')
            ->with($this->requestMock, 'customer')
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Localized Exception')));

        $customerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsCustomerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->messageManagerMock->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error('Localized Exception'));

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @covers \Magento\Customer\Controller\Adminhtml\Index\Index::execute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithNewCustomerAndException()
    {
        $subscription = '0';
        $postValue = [
            'customer' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
                'dob' => '3/12/1996',
            ],
            'subscription' => $subscription,
        ];
        $extractedData = [
            'coolness' => false,
            'disable_auto_group_change' => 'false',
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class
        )->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('int');
        $attributes = [$attributeMock];

        $this->requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturnMap(
                [
                    [null, null, $postValue],
                    [CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, null, $postValue['customer']],
                ]
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    ['customer', null, $postValue['customer']],
                ]
            );

        /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject $objectMock */
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->exactly(2))
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            \Magento\Customer\Model\Metadata\Form::class
        )->disableOriginalConstructor()->getMock();
        $customerFormMock->expects($this->exactly(2))
            ->method('extractData')
            ->with($this->requestMock, 'customer')
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('compactData')
            ->with($extractedData)
            ->willReturn($extractedData);
        $customerFormMock->expects($this->exactly(2))
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->formFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        /** @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $exception = new \Exception(__('Exception'));
        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willThrowException($exception);

        $customerMock->expects($this->never())
            ->method('getId');

        $this->authorizationMock->expects($this->never())
            ->method('isAllowed');

        $this->subscriberFactoryMock->expects($this->never())
            ->method('create');

        $this->sessionMock->expects($this->never())
            ->method('unsCustomerFormData');

        $this->registryMock->expects($this->never())
            ->method('register');

        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('Something went wrong while saving the customer.'));

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/*/new', ['_current' => true])
            ->willReturn(true);

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
