<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlFactory;
use Magento\Framework\Webapi\Response;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePostTest extends TestCase
{
    /**
     * @var CreatePost
     */
    protected $model;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var Url|MockObject
     */
    protected $customerUrl;

    /**
     * @var Registration|MockObject
     */
    protected $registration;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $accountManagement;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url|MockObject
     */
    protected $urlMock;

    /**
     * @var CustomerExtractor|MockObject
     */
    protected $customerExtractorMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerDetailsMock;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    protected $customerDetailsFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StoreManager|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var Address|MockObject
     */
    protected $addressHelperMock;

    /**
     * @var Subscriber|MockObject
     */
    protected $subscriberMock;

    /**
     * @var Manager|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        /**
         * This test can be unskipped when the Unit test object manager helper is enabled to return correct DataBuilders
         * For now the \Magento\Customer\Test\Unit\Controller\AccountTest sufficiently covers the SUT
         */
        $this->markTestSkipped('Cannot be unit tested with the auto generated builder dependencies');
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->responseMock = $this->createMock(Response::class);
        $this->requestMock = $this->createMock(Http::class);

        $this->urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createMock(UrlFactory::class);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->urlMock);

        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerDetailsMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerDetailsFactoryMock = $this->createMock(
            CustomerInterfaceFactory::class
        );

        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->storeMock = $this->createMock(Store::class);

        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->accountManagement = $this->getMockForAbstractClass(AccountManagementInterface::class);
        $this->addressHelperMock = $this->createMock(Address::class);
        $formFactoryMock = $this->createMock(FormFactory::class);

        $this->subscriberMock = $this->createMock(Subscriber::class);
        $subscriberFactoryMock = $this->createPartialMock(
            SubscriberFactory::class,
            ['create']
        );
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->subscriberMock);

        $regionFactoryMock = $this->createMock(RegionInterfaceFactory::class);
        $addressFactoryMock = $this->createMock(AddressInterfaceFactory::class);
        $this->customerUrl = $this->createMock(Url::class);
        $this->registration = $this->createMock(Registration::class);
        $escaperMock = $this->createMock(Escaper::class);
        $this->customerExtractorMock = $this->createMock(CustomerExtractor::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->model = $objectManager->getObject(
            CreatePost::class,
            [
                'context' => $contextMock,
                'customerSession' => $this->customerSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'accountManagement' => $this->accountManagement,
                'addressHelper' => $this->addressHelperMock,
                'urlFactory' => $urlFactoryMock,
                'formFactory' => $formFactoryMock,
                'subscriberFactory' => $subscriberFactoryMock,
                'regionDataFactory' => $regionFactoryMock,
                'addressDataFactory' => $addressFactoryMock,
                'customerDetailsFactory' => $this->customerDetailsFactoryMock,
                'customerUrl' => $this->customerUrl,
                'registration' => $this->registration,
                'escape' => $escaperMock,
                'customerExtractor' => $this->customerExtractorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreatePostActionRegistrationDisabled()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->willReturn(false);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/', [])
            ->willReturn(false);

        $this->customerRepository->expects($this->never())
            ->method('save');

        $this->model->execute();
    }

    public function testRegenerateIdOnExecution()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);
        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->willReturn($this->customerMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->model->execute();
    }

    /**
     * @param $customerId
     * @param $customerEmail
     * @param $password
     * @param $confirmationStatus
     * @param $vatValidationEnabled
     * @param $addressType
     * @param $successMessage
     *
     * @dataProvider getSuccessMessageDataProvider
     */
    public function testSuccessMessage(
        $customerId,
        $customerEmail,
        $password,
        $confirmationStatus,
        $vatValidationEnabled,
        $addressType,
        $successMessage
    ) {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->customerUrl->expects($this->once())
            ->method('getEmailConfirmationUrl')
            ->willReturn($customerEmail);

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with('customer_account_create', $this->requestMock)
            ->willReturn($this->customerMock);

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->willReturn(false);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['password', null, $password],
                    ['password_confirmation', null, $password],
                    ['is_subscribed', false, true],
                ]
            );

        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with([])
            ->willReturnSelf();

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->customerDetailsMock, $password, '')
            ->willReturn($this->customerMock);
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($customerId)
            ->willReturn($confirmationStatus);

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($customerId);

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccessMessage')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->addressHelperMock->expects($this->any())
            ->method('isVatValidationEnabled')
            ->willReturn($vatValidationEnabled);
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->willReturn($addressType);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider()
    {
        return [
            [
                1,
                'customer@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'An account confirmation is required',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'Thank you for registering with',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                true,
                Address::TYPE_SHIPPING,
                'enter you shipping address for proper VAT calculation',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                true,
                Address::TYPE_BILLING,
                'enter you billing address for proper VAT calculation',
            ],
        ];
    }

    /**
     * @param $customerId
     * @param $password
     * @param $confirmationStatus
     * @param $successUrl
     * @param $isSetFlag
     * @param $successMessage
     *
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $customerId,
        $password,
        $confirmationStatus,
        $successUrl,
        $isSetFlag,
        $successMessage
    ) {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with('customer_account_create', $this->requestMock)
            ->willReturn($this->customerMock);

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->willReturn(false);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['password', null, $password],
                    ['password_confirmation', null, $password],
                    ['is_subscribed', false, true],
                ]
            );

        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with([])
            ->willReturnSelf();

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->customerDetailsMock, $password, '')
            ->willReturn($this->customerMock);
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($customerId)
            ->willReturn($confirmationStatus);

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($customerId);

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccessMessage')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['*/*/index', ['_secure' => true], $successUrl],
                    ['*/*/create', ['_secure' => true], $successUrl],
                ]
            );
        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($successUrl)
            ->willReturn($successUrl);
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);
        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontend');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessRedirectDataProvider()
    {
        return [
            [
                1,
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                true,
                'Thank you for registering with',
            ],
            [
                1,
                '123123q',
                AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                false,
                'Thank you for registering with',
            ],
        ];
    }
}
