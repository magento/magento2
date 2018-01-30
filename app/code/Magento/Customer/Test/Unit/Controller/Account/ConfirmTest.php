<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Url;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Controller\Account\Confirm;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\UrlFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\Manager;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Store;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConfirmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\Confirm
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerDataMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultMock;

    /** @var PhpCookieManager | \PHPUnit_Framework_MockObject_MockObject */
    private $cookieMetadataManager;

    /** @var CookieMetadataFactory | \PHPUnit_Framework_MockObject_MockObject */
    private $cookieMetadataFactory;

    protected function setUp()
    {
        $this->customerSessionMock = $this->createDefaultMock(Session::class);
        $this->requestMock = $this->createDefaultMock(RequestInterface::class);
        $this->responseMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', '__wakeup'])
            ->getMock();
        $viewMock = $this->createDefaultMock(ViewInterface::class);
        $this->redirectMock = $this->createDefaultMock(RedirectInterface::class);

        $this->urlMock = $this->createDefaultMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createDefaultMock(UrlFactory::class);
        $urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->urlMock));

        $this->customerAccountManagementMock = $this->getMockBuilder(AccountManagementInterface::class)
            ->getMockForAbstractClass();
        $this->customerDataMock = $this->createDefaultMock(CustomerInterface::class);

        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->createDefaultMock(Manager::class);
        $this->addressHelperMock = $this->createDefaultMock(Address::class);
        $this->storeManagerMock = $this->createDefaultMock(StoreManager::class);
        $this->storeMock = $this->createDefaultMock(Store::class);
        $this->redirectResultMock = $this->createDefaultMock(Redirect::class);

        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectResultMock);

        $this->scopeConfigMock = $this->createDefaultMock(ScopeConfigInterface::class);
        $this->contextMock = $this->createDefaultMock(Context::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $this->contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($viewMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);

        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'createCookieMetadata'])
            ->getMock();

        $cookieMetaData = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cookieMetaData->expects($this->any())
            ->method('setPath')
            ->will($this->returnSelf());

        $this->cookieMetadataFactory->expects($this->any())
            ->method('createCookieMetadata')
            ->will($this->returnValue($cookieMetaData));

        /** @var PhpCookieManager | \PHPUnit_Framework_MockObject_MockObject $cookieMetadataManager */
        $this->cookieMetadataManager = $this->getMockBuilder(PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Confirm::class,
            [
                'context' => $this->contextMock,
                'customerSession' => $this->customerSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'customerAccountManagement' => $this->customerAccountManagementMock,
                'customerRepository' => $this->customerRepositoryMock,
                'addressHelper' => $this->addressHelperMock,
                'urlFactory' => $urlFactoryMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactory,
                'cookieMetadataManager' => $this->cookieMetadataManager,
            ]
        );
    }

    public function testIsLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->redirectResultMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->model->execute());
    }

    /**
     * @dataProvider getParametersDataProvider
     */
    public function testNoCustomerIdInRequest($customerId, $key)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with($this->equalTo('id'), false)
            ->will($this->returnValue($customerId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with($this->equalTo('key'), false)
            ->will($this->returnValue($key));

        $exception = new \Exception('Bad request.');
        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($this->equalTo($exception), $this->equalTo('There was an error confirming the account'));

        $testUrl = 'http://example.com';
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($testUrl));

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($this->equalTo($testUrl))
            ->will($this->returnValue($testUrl));

        $this->redirectResultMock->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo($testUrl))
            ->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->model->execute());
    }

    /**
     * @return array
     */
    public function getParametersDataProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    /**
     * @param $customerId
     * @param $key
     * @param $vatValidationEnabled
     * @param $addressType
     * @param $successMessage
     *
     * @dataProvider getSuccessMessageDataProvider
     */
    public function testSuccessMessage($customerId, $key, $vatValidationEnabled, $addressType, $successMessage)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $customerId],
                ['key', false, $key],
            ]);

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->will($this->returnValue($this->customerDataMock));

        $email = 'test@example.com';
        $this->customerDataMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));

        $this->customerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($this->equalTo($email), $this->equalTo($key))
            ->will($this->returnValue($this->customerDataMock));

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->equalTo($this->customerDataMock))
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->addressHelperMock->expects($this->once())
            ->method('isVatValidationEnabled')
            ->will($this->returnValue($vatValidationEnabled));
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($addressType));

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider()
    {
        return [
            [1, 1, false, null, __('Thank you for registering with')],
            [1, 1, true, Address::TYPE_BILLING, __('enter your billing address for proper VAT calculation')],
            [1, 1, true, Address::TYPE_SHIPPING, __('enter your shipping address for proper VAT calculation')],
        ];
    }

    /**
     * @param $customerId
     * @param $key
     * @param $backUrl
     * @param $successUrl
     * @param $resultUrl
     * @param $isSetFlag
     * @param $successMessage
     *
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $customerId,
        $key,
        $backUrl,
        $successUrl,
        $resultUrl,
        $isSetFlag,
        $successMessage
    ) {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, $customerId],
                ['key', false, $key],
                ['back_url', false, $backUrl],
            ]);

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->will($this->returnValue($this->customerDataMock));

        $email = 'test@example.com';
        $this->customerDataMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));

        $this->customerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($this->equalTo($email), $this->equalTo($key))
            ->will($this->returnValue($this->customerDataMock));

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->equalTo($this->customerDataMock))
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess')
            ->with($this->stringContains($successMessage))
            ->willReturnSelf();

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->urlMock->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('*/*/index'), ['_secure' => true])
            ->will($this->returnValue($successUrl));

        $this->redirectMock->expects($this->never())
            ->method('success')
            ->with($this->equalTo($resultUrl))
            ->willReturn($resultUrl);

        $this->scopeConfigMock->expects($this->never())
            ->method('isSetFlag')
            ->with(
                Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);

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
                1,
                'http://example.com/back',
                null,
                'http://example.com/back',
                true,
                __('Thank you for registering with'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                true,
                __('Thank you for registering with'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                false,
                __('Thank you for registering with'),
            ],
        ];
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createDefaultMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Tests cookie cleaning
     *
     * @dataProvider dataProviderClean
     * @param mixed $cookieValue
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $deleteMatcher
     */
    public function testClean($cookieValue, \PHPUnit_Framework_MockObject_Matcher_Invocation $deleteMatcher)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', false, 'id'],
                ['key', false, 'key'],
            ]);

        $this->customerDataMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('email@exmple.com'));

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($this->returnValue($this->customerDataMock));

        $this->customerAccountManagementMock->expects($this->once())
            ->method('activate');

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->will($this->returnValue('frontend'));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->will($this->returnValue($cookieValue));

        $this->cookieMetadataManager->expects($deleteMatcher)
            ->method('deleteCookie')
            ->will($this->returnValue(null));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->model->execute();
    }

    /**
     * Provides data for testing clean method
     *
     * @return array
     */
    public function dataProviderClean()
    {
        return [
            'clean-cookie' => [
                'testValue',
                $this->once()
            ],
            'no-clean' => [
                null,
                $this->never()
            ],
        ];
    }
}
