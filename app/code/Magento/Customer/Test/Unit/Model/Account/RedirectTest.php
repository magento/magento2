<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Account;

use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\FrameworkMockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends TestCase
{
    /**
     * @var Redirect
     */
    protected $model;

    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var MockObject|Session
     */
    protected $customerSession;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MockObject|Store
     */
    protected $store;

    /**
     * @var MockObject|UrlInterface
     */
    protected $url;

    /**
     * @var MockObject|DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var MockObject|\Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var MockObject|\Magento\Framework\Controller\Result\Redirect
     */
    protected $resultRedirect;

    /**
     * @var MockObject|Forward
     */
    protected $resultForward;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactory;

    /**
     * @var HostChecker|MockObject
     */
    private $hostChecker;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getLastCustomerId',
                    'setLastCustomerId',
                    'unsBeforeAuthUrl',
                    'getBeforeAuthUrl',
                    'getBeforeRequestParams',
                    'getAfterAuthUrl',
                    'getBeforeModuleName',
                    'getBeforeControllerName',
                    'getBeforeAction',
                ]
            )
            ->onlyMethods(
                [
                    'isLoggedIn',
                    'getId',
                    'setBeforeAuthUrl',
                    'setAfterAuthUrl'
                ]
            )
            ->getMock();

        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->store = $this->createMock(Store::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->url = $this->getMockForAbstractClass(UrlInterface::class);
        $this->urlDecoder = $this->getMockForAbstractClass(DecoderInterface::class);
        $this->customerUrl = $this->getMockBuilder(\Magento\Customer\Model\Url::class)
            ->addMethods(['DashboardUrl'])
            ->onlyMethods(
                [
                    'getAccountUrl',
                    'getLoginUrl',
                    'getLogoutUrl',
                    'getDashboardUrl'
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $this->resultForward = $this->createMock(Forward::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->cookieMetadataFactory = $this->createMock(CookieMetadataFactory::class);
        $this->hostChecker = $this->createMock(HostChecker::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Redirect::class,
            [
                'request' => $this->request,
                'customerSession' => $this->customerSession,
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $this->storeManager,
                'url' => $this->url,
                'urlDecoder' => $this->urlDecoder,
                'customerUrl' => $this->customerUrl,
                'resultFactory' => $this->resultFactory,
                'cookieMetadataFactory' => $this->cookieMetadataFactory,
                'hostChecker' => $this->hostChecker,
            ]
        );
    }

    /**
     * Verify get redirect method
     *
     * @param int $customerId
     * @param int $lastCustomerId
     * @param string $referer
     * @param string $baseUrl
     * @param string $beforeAuthUrl
     * @param string $afterAuthUrl
     * @param string $accountUrl
     * @param string $loginUrl
     * @param string $logoutUrl
     * @param string $dashboardUrl
     * @param bool $customerLoggedIn
     * @param bool $redirectToDashboard
     * @dataProvider getRedirectDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetRedirect(
        $customerId,
        $lastCustomerId,
        $referer,
        $baseUrl,
        $beforeAuthUrl,
        $afterAuthUrl,
        $accountUrl,
        $loginUrl,
        $logoutUrl,
        $dashboardUrl,
        $customerLoggedIn,
        $redirectToDashboard
    ) {
        // Preparations for method updateLastCustomerId()
        $this->customerSession->expects($this->once())->method('getLastCustomerId')->willReturn($customerId);
        $this->customerSession->expects($this->any())->method('isLoggedIn')->willReturn($customerLoggedIn);
        $this->customerSession->expects($this->any())->method('getId')->willReturn($lastCustomerId);
        $this->customerSession->expects($this->any())->method('unsBeforeAuthUrl')->willReturnSelf();
        $this->customerSession->expects($this->any())
            ->method('setLastCustomerId')
            ->with($lastCustomerId)
            ->willReturnSelf();

        // Preparations for method prepareRedirectUrl()
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);

        $this->customerSession->expects($this->any())->method('getBeforeAuthUrl')->willReturn($beforeAuthUrl);
        $this->customerSession->expects($this->any())->method('setBeforeAuthUrl')->willReturnSelf();
        $this->customerSession->expects($this->any())->method('getAfterAuthUrl')->willReturn($afterAuthUrl);
        $this->customerSession->expects($this->any())
            ->method('setAfterAuthUrl')
            ->with($beforeAuthUrl)
            ->willReturnSelf();
        $this->customerSession->expects($this->any())->method('getBeforeRequestParams')->willReturn(false);

        $this->customerUrl->expects($this->any())->method('getAccountUrl')->willReturn($accountUrl);
        $this->customerUrl->expects($this->any())->method('getLoginUrl')->willReturn($loginUrl);
        $this->customerUrl->expects($this->any())->method('getLogoutUrl')->willReturn($logoutUrl);
        $this->customerUrl->expects($this->any())->method('getDashboardUrl')->willReturn($dashboardUrl);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with(CustomerUrl::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD, ScopeInterface::SCOPE_STORE)
            ->willReturn($redirectToDashboard);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with(CustomerUrl::REFERER_QUERY_PARAM_NAME)
            ->willReturn($referer);

        $this->urlDecoder->expects($this->any())->method('decode')->with($referer)->willReturn($referer);

        $this->url->expects($this->any())->method('isOwnOriginUrl')->willReturn(true);

        $this->resultRedirect->expects($this->once())->method('setUrl')->with($beforeAuthUrl)->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->hostChecker->expects($this->any())->method('isOwnOrigin')->willReturn(true);

        $this->model->getRedirect();
    }

    /**
     * Redirect data provider
     *
     * @return array
     */
    public static function getRedirectDataProvider()
    {
        /**
         * Customer ID
         * Last customer ID
         * Referer
         * Base URL
         * BeforeAuth URL
         * AfterAuth URL
         * Account URL
         * Login URL
         * Logout URL
         * Dashboard URL
         * Is customer logged in flag
         * Redirect to Dashboard flag
         */
        return [
            // Logged In, Redirect by Referer
            [1, 2, 'referer', 'base', '', '', 'account', '', '', '', true, false],
            [1, 2, 'http://referer.com/', 'http://base.com/', '', '', 'account', '', '', 'dashboard', true, false],
            // Logged In, Redirect by AfterAuthUrl
            [1, 2, 'referer', 'base', '', 'defined', 'account', '', '', '', true, true],
            // Not logged In, Redirect by LoginUrl
            [1, 2, 'referer', 'base', '', '', 'account', 'login', '', '', false, true],
            // Logout, Redirect to Dashboard
            [1, 2, 'referer', 'base', 'logout', '', 'account', 'login', 'logout', 'dashboard', false, true],
            // Default redirect
            [1, 2, 'referer', 'base', 'defined', '', 'account', 'login', 'logout', 'dashboard', true, true],
            // Logout, Without Redirect to Dashboard
            [
                'customerId' => 1,
                'lastCustomerId' => 2,
                'referer' => 'http://base.com/customer/account/logoutSuccess/',
                'baseUrl' => 'http://base.com/',
                'beforeAuthUrl' => 'http://base.com/',
                'afterAuthUrl' => 'http://base.com/customer/account/',
                'accountUrl' => 'account',
                'loginUrl' => 'login',
                'logoutUrl' => 'logout',
                'dashboardUrl' => 'dashboard',
                'customerLoggedIn' => true,
                'redirectToDashboard' => false,
            ],
        ];
    }

    /**
     * Verify before request params
     *
     * @return void
     */
    public function testBeforeRequestParams(): void
    {
        $requestParams = [
            'param1' => 'value1',
        ];

        $module = 'module';
        $controller = 'controller';
        $action = 'action';

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->customerSession->expects($this->exactly(2))
            ->method('getBeforeRequestParams')
            ->willReturn($requestParams);
        $this->customerSession->expects($this->once())
            ->method('getBeforeModuleName')
            ->willReturn($module);
        $this->customerSession->expects($this->once())
            ->method('getBeforeControllerName')
            ->willReturn($controller);
        $this->customerSession->expects($this->once())
            ->method('getBeforeAction')
            ->willReturn($action);
        $this->resultForward->expects($this->once())
            ->method('setParams')
            ->with($requestParams)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('setModule')
            ->with($module)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('setController')
            ->with($controller)
            ->willReturnSelf();
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with($action)
            ->willReturnSelf();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_FORWARD)
            ->willReturn($this->resultForward);

        $result = $this->model->getRedirect();
        $this->assertSame($this->resultForward, $result);
    }

    /**
     * Verify set redirect cokkie method
     *
     * @return void
     */
    public function testSetRedirectCookie(): void
    {
        $coockieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $publicMetadataMock = $this->createMock(PublicCookieMetadata::class);
        $routeMock = 'route';

        $this->model->setCookieManager($coockieManagerMock);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getStorePath')
            ->willReturn('storePath');
        $this->cookieMetadataFactory->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($publicMetadataMock);
        $publicMetadataMock->expects($this->once())
            ->method('setHttpOnly')
            ->with(true)
            ->willReturnSelf();
        $publicMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with(3600)
            ->willReturnSelf();
        $publicMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('storePath')
            ->willReturnSelf();
        $publicMetadataMock->expects($this->once())
            ->method('setSameSite')
            ->with('Lax')
            ->willReturnSelf();
        $coockieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                Redirect::LOGIN_REDIRECT_URL,
                $routeMock,
                $publicMetadataMock
            );
        $this->model->setRedirectCookie($routeMock);
    }

    /**
     * Verify clear redirect cookie
     *
     * @return void
     */
    public function testClearRedirectCookie(): void
    {
        $coockieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $publicMetadataMock = $this->createMock(PublicCookieMetadata::class);

        $this->model->setCookieManager($coockieManagerMock);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getStorePath')
            ->willReturn('storePath');
        $this->cookieMetadataFactory->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($publicMetadataMock);
        $publicMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('storePath')
            ->willReturnSelf();
        $coockieManagerMock->expects($this->once())
            ->method('deleteCookie')
            ->with(
                Redirect::LOGIN_REDIRECT_URL,
                $publicMetadataMock
            );
        $this->model->clearRedirectCookie();
    }
}
