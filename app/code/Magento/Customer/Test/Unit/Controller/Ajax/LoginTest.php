<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Controller\Ajax;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Ajax\Login;
use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManager\ObjectManager as FakeObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Login
     */
    private $controller;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var FakeObjectManager|MockObject
     */
    private $objectManager;

    /**
     * @var AccountManagement|MockObject
     */
    private $accountManagement;

    /**
     * @var Data|MockObject
     */
    private $jsonHelper;

    /**
     * @var Json|MockObject
     */
    private $resultJson;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var Raw|MockObject
     */
    private $resultRaw;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->createPartialMock(ResponseInterface::class, ['setRedirect', 'sendResponse', 'representJson', 'setHttpResponseCode']);
        $this->customerSession = $this->createPartialMock(Session::class, [
                'isLoggedIn',
                'getLastCustomerId',
                'getBeforeAuthUrl',
                'setBeforeAuthUrl',
                'setCustomerDataAsLoggedIn',
                'regenerateId',
                'getData'
            ]);
        $this->objectManager = $this->createPartialMock(FakeObjectManager::class, ['get']);
        $this->accountManagement = $this->createPartialMock(AccountManagement::class, ['authenticate']);

        $this->jsonHelper = $this->createPartialMock(Data::class, ['jsonDecode']);

        $this->resultJson = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->cookieManager = $this->getMockBuilder(CookieManagerInterface::class)
            ->setMethods(['getCookie', 'deleteCookie'])
            ->getMockForAbstractClass();
        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadata = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRaw = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->method('create')
            ->willReturn($this->resultRaw);

        /** @var Context|MockObject $context */
        $context = $this->createMock(Context::class);
        $this->redirect = $this->createMock(RedirectInterface::class);
        $context->method('getRedirect')
            ->willReturn($this->redirect);
        $context->method('getRequest')
            ->willReturn($this->request);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            Login::class,
            [
                'context' => $context,
                'customerSession' => $this->customerSession,
                'helper' => $this->jsonHelper,
                'response' => $this->response,
                'resultRawFactory' => $resultRawFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
                'objectManager' => $this->objectManager,
                'customerAccountManagement' => $this->accountManagement,
                'cookieManager' => $this->cookieManager,
                'cookieMetadataFactory' => $this->cookieMetadataFactory
            ]
        );
    }

    /**
     * Checks successful login.
     */
    public function testLogin()
    {
        $jsonRequest = '{"username":"customer@example.com", "password":"password"}';
        $loginSuccessResponse = '{"errors": false, "message":"Login successful."}';
        $this->withRequest($jsonRequest);

        $this->resultJsonFactory->method('create')
            ->willReturn($this->resultJson);

        $this->jsonHelper->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'customer@example.com', 'password' => 'password']);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->accountManagement->method('authenticate')
            ->with('customer@example.com', 'password')
            ->willReturn($customer);

        $this->customerSession->method('setCustomerDataAsLoggedIn')
            ->with($customer);
        $this->customerSession->method('regenerateId');

        /** @var Redirect|MockObject $redirect */
        $redirect = $this->createMock(Redirect::class);
        $this->controller->setAccountRedirect($redirect);
        $redirect->method('getRedirectCookie')
            ->willReturn('some_url1');

        $this->withCookieManager();

        $this->withScopeConfig();

        $this->redirect->method('success')
            ->willReturn('some_url2');
        $this->resultRaw->expects(self::never())
            ->method('setHttpResponseCode');

        $result = [
            'errors' => false,
            'message' => __('Login successful.'),
            'redirectUrl' => 'some_url2',
        ];

        $this->resultJson->method('setData')
            ->with($result)
            ->willReturn($loginSuccessResponse);
        self::assertEquals($loginSuccessResponse, $this->controller->execute());
    }

    /**
     * Checks unsuccessful login.
     */
    public function testLoginFailure()
    {
        $jsonRequest = '{"username":"invalid@example.com", "password":"invalid"}';
        $loginFailureResponse = '{"message":"Invalid login or password."}';
        $this->withRequest($jsonRequest);

        $this->resultJsonFactory->method('create')
            ->willReturn($this->resultJson);

        $this->jsonHelper->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'invalid@example.com', 'password' => 'invalid']);

        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->accountManagement->method('authenticate')
            ->with('invalid@example.com', 'invalid')
            ->willThrowException(new InvalidEmailOrPasswordException(__('Invalid login or password.')));

        $this->customerSession->expects(self::never())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customer);
        $this->customerSession->expects(self::never())
            ->method('regenerateId');
        $this->customerSession->method('getData')
            ->with('user_login_show_captcha')
            ->willReturn(false);

        $result = [
            'errors' => true,
            'message' => __('Invalid login or password.'),
            'captcha' => false
        ];
        $this->resultJson->method('setData')
            ->with($result)
            ->willReturn($loginFailureResponse);

        self::assertEquals($loginFailureResponse, $this->controller->execute());
    }

    /**
     * Emulates request behavior.
     *
     * @param string $jsonRequest
     */
    private function withRequest(string $jsonRequest)
    {
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');

        $this->request->method('isXmlHttpRequest')
            ->willReturn(true);
    }

    /**
     * Emulates cookie manager behavior.
     */
    private function withCookieManager()
    {
        $this->cookieManager->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $cookieMetadata = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactory->method('createCookieMetadata')
            ->willReturn($cookieMetadata);
        $cookieMetadata->method('setPath')
            ->with('/')
            ->willReturnSelf();
        $this->cookieManager->method('deleteCookie')
            ->with('mage-cache-sessid', $cookieMetadata)
            ->willReturnSelf();
    }

    /**
     * Emulates config behavior.
     */
    private function withScopeConfig()
    {
        /** @var ScopeConfigInterface|MockObject $scopeConfig */
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->controller->setScopeConfig($scopeConfig);
        $scopeConfig->method('getValue')
            ->with('customer/startup/redirect_dashboard')
            ->willReturn(0);
    }
}
