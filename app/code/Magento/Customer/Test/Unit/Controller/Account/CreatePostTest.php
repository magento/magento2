<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Url;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\CreatePost
     */
    protected $model;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\Registration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registration;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerExtractorMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDetailsMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDetailsFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /**
         * This test can be unskipped when the Unit test object manager helper is enabled to return correct DataBuilders
         * For now the \Magento\Customer\Test\Unit\Controller\AccountTest sufficiently covers the SUT
         */
        $this->markTestSkipped('Cannot be unit tested with the auto generated builder dependencies');
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->redirectMock = $this->getMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->responseMock = $this->getMock(\Magento\Framework\Webapi\Response::class);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);

        $this->urlMock = $this->getMock(\Magento\Framework\Url::class, [], [], '', false);
        $urlFactoryMock = $this->getMock(\Magento\Framework\UrlFactory::class, [], [], '', false);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->customerMock = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->customerDetailsMock = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false
        );
        $this->customerDetailsFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class, [], [], '', false
        );

        $this->messageManagerMock = $this->getMock(\Magento\Framework\Message\Manager::class, [], [], '', false);
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);

        $this->customerRepository = $this->getMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->accountManagement = $this->getMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->addressHelperMock = $this->getMock(\Magento\Customer\Helper\Address::class, [], [], '', false);
        $formFactoryMock = $this->getMock(\Magento\Customer\Model\Metadata\FormFactory::class, [], [], '', false);

        $this->subscriberMock = $this->getMock(\Magento\Newsletter\Model\Subscriber::class, [], [], '', false);
        $subscriberFactoryMock = $this->getMock(
            \Magento\Newsletter\Model\SubscriberFactory::class, ['create'], [], '', false
        );
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->subscriberMock));

        $regionFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\RegionInterfaceFactory::class, [], [], '', false
        );
        $addressFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class, [], [], '', false
        );
        $this->customerUrl = $this->getMock(\Magento\Customer\Model\Url::class, [], [], '', false);
        $this->registration = $this->getMock(\Magento\Customer\Model\Registration::class, [], [], '', false);
        $escaperMock = $this->getMock(\Magento\Framework\Escaper::class, [], [], '', false);
        $this->customerExtractorMock = $this->getMock(
            \Magento\Customer\Model\CustomerExtractor::class,
            [],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMock(\Magento\Framework\Api\DataObjectHelper::class, [], [], '', false);

        $eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);

        $contextMock = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
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
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/', [])
            ->will($this->returnValue(false));

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
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

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
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $this->customerUrl->expects($this->once())
            ->method('getEmailConfirmationUrl')
            ->will($this->returnValue($customerEmail));

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerId));
        $this->customerMock->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue($customerEmail));

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->customerMock));

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['password', null, $password],
                ['password_confirmation', null, $password],
                ['is_subscribed', false, true],
            ]);

        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->customerMock));
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($customerId))
            ->will($this->returnValue($confirmationStatus));

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($this->equalTo($customerId));

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->addressHelperMock->expects($this->any())
            ->method('isVatValidationEnabled')
            ->will($this->returnValue($vatValidationEnabled));
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($addressType));

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
            ->will($this->returnValue(false));

        $this->registration->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->customerMock));

        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['password', null, $password],
                ['password_confirmation', null, $password],
                ['is_subscribed', false, true],
            ]);

        $this->customerMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());

        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->customerMock));
        $this->accountManagement->expects($this->once())
            ->method('getConfirmationStatus')
            ->with($this->equalTo($customerId))
            ->will($this->returnValue($confirmationStatus));

        $this->subscriberMock->expects($this->once())
            ->method('subscribeCustomerById')
            ->with($this->equalTo($customerId));

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->will($this->returnSelf());

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap([
                ['*/*/index', ['_secure' => true], $successUrl],
                ['*/*/create', ['_secure' => true], $successUrl],
            ]);
        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($this->equalTo($successUrl))
            ->will($this->returnValue($successUrl));
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD),
                $this->equalTo(ScopeInterface::SCOPE_STORE)
            )
            ->will($this->returnValue($isSetFlag));
        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->model->execute();
    }

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
