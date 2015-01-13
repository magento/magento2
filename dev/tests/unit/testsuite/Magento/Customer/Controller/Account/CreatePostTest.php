<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

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
     * @var \Magento\Customer\Api\CustomerRepository|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDetailsBuilderMock;

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

    protected function setUp()
    {
        /**
         * This test can be unskipped when the Unit test object manager helper is enabled to return correct DataBuilders
         * For now the \Magento\Customer\Controller\AccountTest sufficiently covers the SUT
         */
        $this->markTestSkipped('Cannot be unit tested with the auto generated builder dependencies');
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface');
        $this->responseMock = $this->getMock('Magento\Webapi\Controller\Response');
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $this->urlMock = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $urlFactoryMock = $this->getMock('Magento\Framework\UrlFactory', [], [], '', false);
        $urlFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->customerMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            [],
            '',
            false
        );
        $this->customerDetailsMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface', [], [], '', false
        );
        $this->customerDetailsBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder', [], [], '', false
        );

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->customerRepository = $this->getMock('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->accountManagement = $this->getMock('Magento\Customer\Api\AccountManagementInterface');
        $this->addressHelperMock = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);
        $formFactoryMock = $this->getMock('Magento\Customer\Model\Metadata\FormFactory', [], [], '', false);

        $this->subscriberMock = $this->getMock('Magento\Newsletter\Model\Subscriber', [], [], '', false);
        $subscriberFactoryMock = $this->getMock(
            'Magento\Newsletter\Model\SubscriberFactory', ['create'], [], '', false
        );
        $subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->subscriberMock));

        $regionBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\RegionDataBuilder', [], [], '', false
        );
        $addressBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressDataBuilder', [], [], '', false
        );
        $this->customerUrl = $this->getMock('Magento\Customer\Model\Url', [], [], '', false);
        $this->registration = $this->getMock('Magento\Customer\Model\Registration', [], [], '', false);
        $escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->customerExtractorMock = $this->getMock('Magento\Customer\Model\CustomerExtractor', [], [], '', false);

        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManagerMock));

        $this->model = new \Magento\Customer\Controller\Account\CreatePost(
            $contextMock,
            $this->customerSessionMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->accountManagement,
            $this->addressHelperMock,
            $urlFactoryMock,
            $formFactoryMock,
            $subscriberFactoryMock,
            $regionBuilderMock,
            $addressBuilderMock,
            $this->customerDetailsBuilderMock,
            $this->customerUrl,
            $this->registration,
            $escaperMock,
            $this->customerExtractorMock
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

        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('populate')
            ->with($this->equalTo($this->customerMock))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDetailsMock));

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
                'Account confirmation is required',
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

        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('populate')
            ->with($this->equalTo($this->customerMock))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDetailsMock));

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
