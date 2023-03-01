<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Account\Confirm;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Logger as CustomerLogger;
use Magento\Customer\Model\Log;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConfirmTest extends TestCase
{
    /**
     * @var Confirm
     */
    protected $model;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Url|MockObject
     */
    protected $urlMock;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerDataMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Address|MockObject
     */
    protected $addressHelperMock;

    /**
     * @var StoreManager|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Redirect|MockObject
     */
    protected $redirectResultMock;

    /**
     * @var CustomerLogger|MockObject
     */
    private $customerLoggerMock;

    /**
     * @var Log|MockObject
     */
    private $logMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->createPartialMock(
            Http::class,
            ['setRedirect', '__wakeup']
        );
        $viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);

        $this->urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlFactoryMock = $this->createMock(UrlFactory::class);
        $urlFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->urlMock);

        $this->customerLoggerMock = $this->createMock(CustomerLogger::class);
        $this->logMock = $this->createMock(Log::class);

        $this->customerAccountManagementMock =
            $this->getMockForAbstractClass(AccountManagementInterface::class);
        $this->customerDataMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->customerRepositoryMock =
            $this->getMockForAbstractClass(CustomerRepositoryInterface::class);

        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->addressHelperMock = $this->createMock(Address::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->redirectResultMock = $this->createMock(Redirect::class);

        $resultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectResultMock);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->contextMock = $this->createMock(Context::class);
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
                'customerLogger' => $this->customerLoggerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testIsLoggedIn(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->redirectResultMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->model->execute());
    }

    /**
     * @param $customerId
     * @param $key
     * @return void
     * @dataProvider getParametersDataProvider
     */
    public function testNoCustomerIdInRequest($customerId, $key): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['id', false], ['key', false])
            ->willReturnOnConsecutiveCalls($customerId, $key);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Bad request.'));

        $testUrl = 'http://example.com';
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/index', ['_secure' => true])
            ->willReturn($testUrl);

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($testUrl)
            ->willReturn($testUrl);

        $this->redirectResultMock->expects($this->once())
            ->method('setUrl')
            ->with($testUrl)
            ->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->model->execute());
    }

    /**
     * @return array
     */
    public function getParametersDataProvider(): array
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
     * @param $lastLoginAt
     * @param $successMessage
     *
     * @return void
     * @dataProvider getSuccessMessageDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSuccessMessage(
        $customerId,
        $key,
        $vatValidationEnabled,
        $addressType,
        $lastLoginAt,
        $successMessage
    ): void {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', 0, $customerId],
                    ['key', false, $key]
                ]
            );

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerDataMock);

        $email = 'test@example.com';
        $this->customerDataMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->customerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($email, $key)
            ->willReturn($this->customerDataMock);

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->customerDataMock)
            ->willReturnSelf();

        $this->messageManagerMock
            ->method('addSuccess')
            ->with($successMessage)
            ->willReturnSelf();

        $this->messageManagerMock
            ->expects($this->never())
            ->method('addException');

        $this->urlMock
            ->method('getUrl')
            ->willReturnMap([
                ['customer/address/edit', null, 'http://store.web/customer/address/edit'],
                ['*/*/admin', ['_secure' => true], 'http://store.web/back']
            ]);

        $this->logMock->expects($vatValidationEnabled ? $this->never() : $this->once())
            ->method('getLastLoginAt')
            ->willReturn($lastLoginAt);
        $this->customerLoggerMock->expects($vatValidationEnabled ? $this->never() : $this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->logMock);

        $this->addressHelperMock->expects($this->once())
            ->method('isVatValidationEnabled')
            ->willReturn($vatValidationEnabled);
        $this->addressHelperMock->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->willReturn($addressType);

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontend');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $cookieMetadataManager = $this->getMockBuilder(PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadata = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataFactory->expects($this->once())
            ->method('createCookieMetadata')
            ->willReturn($cookieMetadata);
        $cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with('/');
        $cookieMetadataManager->expects($this->once())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $cookieMetadata);

        $refClass = new \ReflectionClass(Confirm::class);
        $cookieMetadataManagerProperty = $refClass->getProperty('cookieMetadataManager');
        $cookieMetadataManagerProperty->setAccessible(true);
        $cookieMetadataManagerProperty->setValue($this->model, $cookieMetadataManager);

        $cookieMetadataFactoryProperty = $refClass->getProperty('cookieMetadataFactory');
        $cookieMetadataFactoryProperty->setAccessible(true);
        $cookieMetadataFactoryProperty->setValue($this->model, $cookieMetadataFactory);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessMessageDataProvider(): array
    {
        return [
            [1, 1, false, null, 'some-datetime', null],
            [1, 1, false, null, null, __('Thank you for registering with %1.', 'frontend')],
            [
                1,
                1,
                true,
                Address::TYPE_BILLING,
                null,
                __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a>'
                    . ' to enter your billing address for proper VAT calculation.',
                    'http://store.web/customer/address/edit'
                )
            ],
            [
                1,
                1,
                true,
                Address::TYPE_SHIPPING,
                null,
                __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a>'
                    . ' to enter your shipping address for proper VAT calculation.',
                    'http://store.web/customer/address/edit'
                )
            ],
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
     * @param $lastLoginAt
     *
     * @return void
     * @dataProvider getSuccessRedirectDataProvider
     */
    public function testSuccessRedirect(
        $customerId,
        $key,
        $backUrl,
        $successUrl,
        $resultUrl,
        $isSetFlag,
        $lastLoginAt,
        $successMessage
    ): void {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', 0, $customerId],
                    ['key', false, $key],
                    ['back_url', false, $backUrl]
                ]
            );

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerDataMock);

        $email = 'test@example.com';
        $this->customerDataMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->customerAccountManagementMock->expects($this->once())
            ->method('activate')
            ->with($email, $key)
            ->willReturn($this->customerDataMock);

        $this->customerSessionMock->expects($this->any())
            ->method('setCustomerDataAsLoggedIn')
            ->with($this->customerDataMock)
            ->willReturnSelf();

        $this->messageManagerMock->method('addSuccess')
            ->with($successMessage)
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->never())
            ->method('addException');

        $this->urlMock->method('getUrl')
            ->willReturnMap([
                ['customer/address/edit', null, 'http://store.web/customer/address/edit'],
                ['*/*/admin', ['_secure' => true], 'http://store.web/back'],
                ['*/*/index', ['_secure' => true], $successUrl]
            ]);

        $this->logMock->expects($this->once())
            ->method('getLastLoginAt')
            ->willReturn($lastLoginAt);
        $this->customerLoggerMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->logMock);

        $this->storeMock->expects($this->any())
            ->method('getFrontendName')
            ->willReturn('frontend');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($resultUrl)
            ->willReturn($resultUrl);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD, ScopeInterface::SCOPE_STORE)
            ->willReturn($isSetFlag);

        $cookieMetadataManager = $this->getMockBuilder(PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(false);

        $refClass = new \ReflectionClass(Confirm::class);
        $refProperty = $refClass->getProperty('cookieMetadataManager');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $cookieMetadataManager);

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function getSuccessRedirectDataProvider(): array
    {
        return [
            [
                1,
                1,
                'http://example.com/back',
                null,
                'http://example.com/back',
                true,
                null,
                __('Thank you for registering with %1.', 'frontend'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                true,
                null,
                __('Thank you for registering with %1.', 'frontend'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                false,
                null,
                __('Thank you for registering with %1.', 'frontend'),
            ],
            [
                1,
                1,
                null,
                'http://example.com/success',
                'http://example.com/success',
                false,
                'some data',
                null,
            ]
        ];
    }
}
