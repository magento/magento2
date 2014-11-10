<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Helper\Data as CustomerData;
use Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerHelperMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\UrlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFactoryMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerExtractorMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerServiceDataMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerDetails|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDetailsMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDetailsBuilderMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

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
     * @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Newsletter\Model\Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberMock;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberFactoryMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\RegionBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionBuilderMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressBuilderMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    protected function setUp()
    {
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface');
        $this->responseMock = $this->getMock('Magento\Webapi\Controller\Response');
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $this->urlMock = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->urlFactoryMock = $this->getMock('Magento\Framework\UrlFactory', [], [], '', false);
        $this->urlFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->customerServiceDataMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->customerDetailsMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerDetails', [], [], '', false
        );
        $this->customerDetailsBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerDetailsBuilder', [], [], '', false
        );

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->customerAccountServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->addressHelperMock = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);
        $this->formFactoryMock = $this->getMock('Magento\Customer\Model\Metadata\FormFactory', [], [], '', false);

        $this->subscriberMock = $this->getMock('Magento\Newsletter\Model\Subscriber', [], [], '', false);
        $this->subscriberFactoryMock = $this->getMock(
            'Magento\Newsletter\Model\SubscriberFactory', ['create'], [], '', false
        );
        $this->subscriberFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->subscriberMock));

        $this->regionBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\RegionBuilder', [], [], '', false
        );
        $this->addressBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\AddressBuilder', [], [], '', false
        );
        $this->customerHelperMock = $this->getMock('Magento\Customer\Helper\Data', [], [], '', false);
        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->customerExtractorMock = $this->getMock('Magento\Customer\Model\CustomerExtractor', [], [], '', false);

        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManagerMock));

        $this->model = new \Magento\Customer\Controller\Account\CreatePost(
            $this->contextMock,
            $this->customerSessionMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->customerAccountServiceMock,
            $this->addressHelperMock,
            $this->urlFactoryMock,
            $this->formFactoryMock,
            $this->subscriberFactoryMock,
            $this->regionBuilderMock,
            $this->addressBuilderMock,
            $this->customerDetailsBuilderMock,
            $this->customerHelperMock,
            $this->escaperMock,
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

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue(false));

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/', array())
            ->will($this->returnValue(false));

        $this->customerAccountServiceMock->expects($this->never())
            ->method('createCustomer');

        $this->model->execute();
    }

    public function testRegenerateIdOnExecution()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
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

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue(true));
        $this->customerHelperMock->expects($this->once())
            ->method('getEmailConfirmationUrl')
            ->will($this->returnValue($customerEmail));

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerServiceDataMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerId));
        $this->customerServiceDataMock->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue($customerEmail));

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->customerServiceDataMock));

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
            ->method('setCustomer')
            ->with($this->equalTo($this->customerServiceDataMock))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDetailsMock));

        $this->customerAccountServiceMock->expects($this->once())
            ->method('createCustomer')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->customerServiceDataMock));
        $this->customerAccountServiceMock->expects($this->once())
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
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'Account confirmation is required',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                false,
                Address::TYPE_SHIPPING,
                'Thank you for registering with',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED,
                true,
                Address::TYPE_SHIPPING,
                'enter you shipping address for proper VAT calculation',
            ],
            [
                1,
                'customer@example.com',
                '123123q',
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED,
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

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue(true));

        $this->customerSessionMock->expects($this->once())
            ->method('regenerateId');

        $this->customerServiceDataMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->with($this->equalTo('customer_account_create'), $this->equalTo($this->requestMock))
            ->will($this->returnValue($this->customerServiceDataMock));

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
            ->method('setCustomer')
            ->with($this->equalTo($this->customerServiceDataMock))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('setAddresses')
            ->with($this->equalTo([]))
            ->will($this->returnSelf());
        $this->customerDetailsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customerDetailsMock));

        $this->customerAccountServiceMock->expects($this->once())
            ->method('createCustomer')
            ->with($this->equalTo($this->customerDetailsMock), $this->equalTo($password), '')
            ->will($this->returnValue($this->customerServiceDataMock));
        $this->customerAccountServiceMock->expects($this->once())
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
                $this->equalTo(CustomerData::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD),
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
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                true,
                'Thank you for registering with',
            ],
            [
                1,
                '123123q',
                CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED,
                'http://example.com/success',
                false,
                'Thank you for registering with',
            ],
        ];
    }
}
