<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Controller\Adminhtml\Index\Save;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testing Save Customer use case from admin page
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \Magento\Customer\Controller\Adminhtml\Index\Save
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    protected $model;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactoryMock;

    /**
     * @var DataObjectFactory|MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    protected $customerDataFactoryMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper|MockObject
     */
    protected $customerMapperMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataHelperMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    protected $authorizationMock;

    /**
     * @var SubscriberFactory|MockObject
     */
    protected $subscriberFactoryMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var AccountManagement|MockObject
     */
    protected $managementMock;

    /**
     * @var AddressInterfaceFactory|MockObject
     */
    protected $addressDataFactoryMock;

    /**
     * @var EmailNotificationInterface|MockObject
     */
    protected $emailNotificationMock;

    /**
     * @var Mapper|MockObject
     */
    protected $customerAddressMapperMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    protected $customerAddressRepositoryMock;

    /**
     * @var SubscriptionManagerInterface|MockObject
     */
    private $subscriptionManager;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsCustomerFormData', 'setCustomerFormData'])
            ->getMock();
        $this->formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectFactoryMock = $this->getMockBuilder(DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerDataFactoryMock = $this->getMockBuilder(
            CustomerInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerAddressRepositoryMock = $this->getMockBuilder(
            AddressRepositoryInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->customerMapperMock = $this->getMockBuilder(
            \Magento\Customer\Model\Customer\Mapper::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->customerAddressMapperMock = $this->getMockBuilder(
            Mapper::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(
            DataObjectHelper::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subscriberFactoryMock = $this->getMockBuilder(SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subscriptionManager = $this->getMockForAbstractClass(SubscriptionManagerInterface::class);
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->managementMock = $this->getMockBuilder(AccountManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAccount', 'validateCustomerStoreIdByWebsiteId'])
            ->getMock();
        $this->addressDataFactoryMock = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->emailNotificationMock = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getStoreIds']);
        $website->method('getStoreIds')
            ->willReturn([1]);
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $storeManager->method('getWebsite')
            ->willReturn($website);

        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            Save::class,
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
                'subscriptionManager' => $this->subscriptionManager,
                'storeManager' => $storeManager,
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
        $customerEmail = 'customer@email.com';
        $subscriptionWebsite = 1;
        $subscriptionStatus = true;
        $subscriptionStore = 3;
        $postValue = [
            'customer' => [
                'entity_id' => $customerId,
                'code' => 'value',
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription_status' => [$subscriptionWebsite => $subscriptionStatus],
            'subscription_store' => [$subscriptionWebsite => $subscriptionStore],
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

        /** @var AttributeMetadataInterface|MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
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
            ->with('customer')
            ->willReturn($postValue['customer']);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['subscription_status', null, [$subscriptionWebsite => $subscriptionStatus]],
                    ['subscription_store', null, [$subscriptionWebsite => $subscriptionStore]],
                    ['back', false, true],
                ]
            );

        /** @var DataObject|MockObject $objectMock */
        $objectMock = $this->getMockBuilder(DataObject::class)
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
            Form::class
        )->disableOriginalConstructor()
            ->getMock();
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

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getId')->willReturn($customerId);
        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);
        $this->customerRepositoryMock->method('getById')
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
                        $mergedData, CustomerInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );

        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($customerMock)
            ->willReturnSelf();
        $customerMock->expects($this->once())->method('getEmail')->willReturn($customerEmail);
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($customerMock, $customerEmail)
            ->willReturnSelf();

        $this->subscriptionManager->expects($this->once())
            ->method($subscriptionStatus ? 'subscribeCustomer' : 'unsubscribeCustomer')
            ->with($customerId, $subscriptionStore);

        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer.'))
            ->willReturnSelf();

        /** @var Redirect|MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(Redirect::class)
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

        $this->managementMock->method('validateCustomerStoreIdByWebsiteId')
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
        $subscriptionWebsite = 1;
        $subscriptionStatus = false;
        $subscriptionStore = 3;

        $postValue = [
            'customer' => [
                'coolness' => false,
                'disable_auto_group_change' => 'false',
            ],
            'subscription_status' => [$subscriptionWebsite => $subscriptionStatus],
            'subscription_store' => [$subscriptionWebsite => $subscriptionStore],
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
        /** @var AttributeMetadataInterface|MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
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
            ->with('customer')
            ->willReturn($postValue['customer']);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['subscription_status', null, [$subscriptionWebsite => $subscriptionStatus]],
                    ['subscription_store', null, [$subscriptionWebsite => $subscriptionStore]],
                    ['back', false, false],
                ]
            );

        /** @var DataObject|MockObject $objectMock */
        $objectMock = $this->getMockBuilder(DataObject::class)
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
            Form::class
        )->disableOriginalConstructor()
            ->getMock();
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

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getId')->willReturn($customerId);
        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);
        $this->dataHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->willReturnMap(
                [
                    [
                        $customerMock,
                        $mergedData, CustomerInterface::class,
                        $this->dataHelperMock
                    ],
                ]
            );
        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willReturn($customerMock);
        $this->subscriptionManager->expects($this->once())
            ->method($subscriptionStatus ? 'subscribeCustomer' : 'unsubscribeCustomer')
            ->with($customerId, $subscriptionStore);
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer.'))
            ->willReturnSelf();

        /** @var Redirect|MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(Redirect::class)
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
            'disable_auto_group_change' => 0,
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
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

        /** @var DataObject|MockObject $objectMock */
        $objectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            Form::class
        )->disableOriginalConstructor()
            ->getMock();
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

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            CustomerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willThrowException(new \Magento\Framework\Validator\Exception(__('Validator Exception')));
        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($customerMock)
            ->willReturn($extractedData);
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
            ->with(new Error('Validator Exception'));

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(Redirect::class)
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
            'disable_auto_group_change' => 0,
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
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

        /** @var DataObject|MockObject $objectMock */
        $objectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        /** @var Form|MockObject $formMock */
        $customerFormMock = $this->getMockBuilder(
            Form::class
        )->disableOriginalConstructor()
            ->getMock();
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

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        $customerMock = $this->getMockBuilder(
            CustomerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->managementMock->expects($this->once())
            ->method('createAccount')
            ->with($customerMock, null, '')
            ->willThrowException(new LocalizedException(__('Localized Exception')));

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
            ->with(new Error('Localized Exception'));

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($customerMock)
            ->willReturn($extractedData);

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(Redirect::class)
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
            'disable_auto_group_change' => 0,
            'dob' => '1996-03-12',
        ];

        /** @var AttributeMetadataInterface|MockObject $customerFormMock */
        $attributeMock = $this->getMockBuilder(
            AttributeMetadataInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('coolness');
        $attributeMock->expects($this->once())
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

        /** @var DataObject|MockObject $objectMock */
        $objectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectMock->expects($this->once())
            ->method('getData')
            ->with('customer')
            ->willReturn($postValue['customer']);

        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $postValue])
            ->willReturn($objectMock);

        $customerFormMock = $this->getMockBuilder(
            Form::class
        )->disableOriginalConstructor()
            ->getMock();
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

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'adminhtml_customer',
                [],
                false,
                Form::DONT_IGNORE_INVISIBLE
            )->willReturn($customerFormMock);

        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockBuilder(
            CustomerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->customerDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $exception = new \Exception('Exception');
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

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($customerMock)
            ->willReturn($extractedData);

        $this->sessionMock->expects($this->once())
            ->method('setCustomerFormData')
            ->with(
                [
                    'customer' => $extractedData,
                    'subscription' => $subscription,
                ]
            );

        /** @var Redirect|MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder(Redirect::class)
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
