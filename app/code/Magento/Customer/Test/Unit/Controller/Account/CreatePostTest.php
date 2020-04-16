<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Url;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\CreatePost
     */
    protected $model;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\Registration|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registration;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $accountManagement;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerExtractorMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerDetailsMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerDetailsFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscriberMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * This test can be unskipped when the Unit test object manager helper is enabled to return correct DataBuilders
         * For now the \Magento\Customer\Test\Unit\Controller\AccountTest sufficiently covers the SUT
         */
        $this->markTestSkipped('Cannot be unit tested with the auto generated builder dependencies');
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\Webapi\Response::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->urlMock);

        $this->customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerDetailsMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerDetailsFactoryMock = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->accountManagement = $this->createMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->addressHelperMock = $this->createMock(\Magento\Customer\Helper\Address::class);
        $formFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);

        $this->subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $subscriberFactoryMock = $this->createPartialMock(
            \Magento\Newsletter\Model\SubscriberFactory::class,
            ['create']
        );
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->subscriberMock);

        $regionFactoryMock = $this->createMock(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        $addressFactoryMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->registration = $this->createMock(\Magento\Customer\Model\Registration::class);
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->customerExtractorMock = $this->createMock(\Magento\Customer\Model\CustomerExtractor::class);
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
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
            \Magento\Customer\Controller\Account\CreatePost::class,
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
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
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
            ->with($this->equalTo([]))
            ->willReturnSelf();

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->willReturn($this->customerMock);
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($customerId))
            ->willReturn($confirmationStatus);

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($this->equalTo($customerId));

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
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
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
            ->with($this->equalTo([]))
            ->willReturnSelf();

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->willReturn($this->customerMock);
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($customerId))
            ->willReturn($confirmationStatus);

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($this->equalTo($customerId));

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
            ->with($this->equalTo($successUrl))
            ->willReturn($successUrl);
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD),
                $this->equalTo(ScopeInterface::SCOPE_STORE)
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
