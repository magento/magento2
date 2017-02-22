<?php
/**
 * Unit test for Magento\Customer\Test\Unit\Model\Account\Redirect
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Model\Account;

use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Redirect
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Controller\Result\Redirect
     */
    protected $resultRedirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    public function setUp()
    {
        $this->request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'getLastCustomerId',
                'isLoggedIn',
                'getId',
                'setLastCustomerId',
                'unsBeforeAuthUrl',
                'getBeforeAuthUrl',
                'setBeforeAuthUrl',
                'getAfterAuthUrl',
                'setAfterAuthUrl',
            ])
            ->getMock();

        $this->scopeConfig = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->url = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $this->urlDecoder = $this->getMockForAbstractClass('Magento\Framework\Url\DecoderInterface');

        $this->customerUrl = $this->getMockBuilder('Magento\Customer\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Customer\Model\Account\Redirect',
            [
                'request'               => $this->request,
                'customerSession'       => $this->customerSession,
                'scopeConfig'           => $this->scopeConfig,
                'storeManager'          => $this->storeManager,
                'url'                   => $this->url,
                'urlDecoder'            => $this->urlDecoder,
                'customerUrl'           => $this->customerUrl,
                'resultRedirectFactory' => $this->resultRedirectFactory
            ]
        );
    }

    /**
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
        $this->customerSession->expects($this->once())
            ->method('getLastCustomerId')
            ->willReturn($customerId);
        $this->customerSession->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($customerLoggedIn);
        $this->customerSession->expects($this->any())
            ->method('getId')
            ->willReturn($lastCustomerId);
        $this->customerSession->expects($this->any())
            ->method('unsBeforeAuthUrl')
            ->willReturnSelf();
        $this->customerSession->expects($this->any())
            ->method('setLastCustomerId')
            ->with($lastCustomerId)
            ->willReturnSelf();

        // Preparations for method prepareRedirectUrl()
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->customerSession->expects($this->any())
            ->method('getBeforeAuthUrl')
            ->willReturn($beforeAuthUrl);
        $this->customerSession->expects($this->any())
            ->method('setBeforeAuthUrl')
            ->willReturnSelf();
        $this->customerSession->expects($this->any())
            ->method('getAfterAuthUrl')
            ->willReturn($afterAuthUrl);
        $this->customerSession->expects($this->any())
            ->method('setAfterAuthUrl')
            ->with($beforeAuthUrl)
            ->willReturnSelf();

        $this->customerUrl->expects($this->any())
            ->method('getAccountUrl')
            ->willReturn($accountUrl);
        $this->customerUrl->expects($this->any())
            ->method('getLoginUrl')
            ->willReturn($loginUrl);
        $this->customerUrl->expects($this->any())
            ->method('getLogoutUrl')
            ->willReturn($logoutUrl);
        $this->customerUrl->expects($this->any())
            ->method('DashboardUrl')
            ->willReturn($dashboardUrl);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with(CustomerUrl::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD, ScopeInterface::SCOPE_STORE)
            ->willReturn($redirectToDashboard);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with(CustomerUrl::REFERER_QUERY_PARAM_NAME)
            ->willReturn($referer);

        $this->urlDecoder->expects($this->any())
            ->method('decode')
            ->with($referer)
            ->willReturn($referer);

        $this->url->expects($this->any())
            ->method('isOwnOriginUrl')
            ->willReturn(true);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->willReturnSelf();

        $this->model->getRedirect();
    }

    /**
     * @return array
     */
    public function getRedirectDataProvider()
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
            // Loggend In, Redirect by Referer
            [1, 2, 'referer', 'base', '', '', 'account', '', '', '', true, false],
            // Loggend In, Redirect by AfterAuthUrl
            [1, 2, 'referer', 'base', '', 'defined', 'account', '', '', '', true, true],
            // Not logged In, Redirect by LoginUrl
            [1, 2, 'referer', 'base', '', '', 'account', 'login', '', '', false, true],
            // Logout, Redirect to Dashboard
            [1, 2, 'referer', 'base', 'logout', '', 'account', 'login', 'logout', 'dashboard', false, true],
            // Default redirect
            [1, 2, 'referer', 'base', 'defined', '', 'account', 'login', 'logout', 'dashboard', true, true],
        ];
    }
}
