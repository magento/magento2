<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test customer ajax login controller
 */
namespace Magento\Customer\Test\Unit\Controller\Ajax;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Ajax\Login
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Raw| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadata| \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieMetadata;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse', 'representJson', 'setHttpResponseCode']
        );
        $this->customerSession = $this->createPartialMock(
            \Magento\Customer\Model\Session::class,
            [
                'isLoggedIn',
                'getLastCustomerId',
                'getBeforeAuthUrl',
                'setBeforeAuthUrl',
                'setCustomerDataAsLoggedIn',
                'regenerateId'
            ]
        );
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['get']);
        $this->customerAccountManagementMock =
            $this->createPartialMock(\Magento\Customer\Model\AccountManagement::class, ['authenticate']);

        $this->jsonHelperMock = $this->createPartialMock(\Magento\Framework\Json\Helper\Data::class, ['jsonDecode']);

        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->cookieManager = $this->getMockBuilder(\Magento\Framework\Stdlib\CookieManagerInterface::class)
            ->setMethods(['getCookie', 'deleteCookie'])
            ->getMockForAbstractClass();
        $this->cookieMetadataFactory = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->cookieMetadata = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $contextMock->expects($this->atLeastOnce())->method('getRedirect')->willReturn($this->redirectMock);
        $contextMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($this->request);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Customer\Controller\Ajax\Login::class,
            [
                'context' => $contextMock,
                'customerSession' => $this->customerSession,
                'helper' => $this->jsonHelperMock,
                'response' => $this->response,
                'resultRawFactory' => $resultRawFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
                'objectManager' => $this->objectManager,
                'customerAccountManagement' => $this->customerAccountManagementMock,
                'cookieManager' => $this->cookieManager,
                'cookieMetadataFactory' => $this->cookieMetadataFactory
            ]
        );
    }

    public function testLogin()
    {
        $jsonRequest = '{"username":"customer@example.com", "password":"password"}';
        $loginSuccessResponse = '{"errors": false, "message":"Login successful."}';

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn($jsonRequest);

        $this->request
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn('POST');

        $this->request
            ->expects($this->any())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this->resultJsonFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->jsonHelperMock
            ->expects($this->any())
            ->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'customer@example.com', 'password' => 'password']);

        $customerMock = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerAccountManagementMock
            ->expects($this->any())
            ->method('authenticate')
            ->with('customer@example.com', 'password')
            ->willReturn($customerMock);

        $this->customerSession->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerMock);

        $this->customerSession->expects($this->once())->method('regenerateId');

        $redirectMock = $this->createMock(\Magento\Customer\Model\Account\Redirect::class);
        $this->object->setAccountRedirect($redirectMock);
        $redirectMock->expects($this->once())->method('getRedirectCookie')->willReturn('some_url1');

        $this->cookieManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $this->cookieMetadataFactory->expects($this->once())
            ->method('createCookieMetadata')
            ->willReturn($this->cookieMetadata);
        $this->cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with('/')
            ->willReturnSelf();
        $this->cookieManager->expects($this->once())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $this->cookieMetadata)
            ->willReturnSelf();

        $scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->object->setScopeConfig($scopeConfigMock);
        $scopeConfigMock->expects($this->once())->method('getValue')
            ->with('customer/startup/redirect_dashboard')
            ->willReturn(0);

        $this->redirectMock->expects($this->once())->method('success')->willReturn('some_url2');
        $this->resultRaw->expects($this->never())->method('setHttpResponseCode');

        $result = [
            'errors' => false,
            'message' => __('Login successful.'),
            'redirectUrl' => 'some_url2',
        ];

        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturn($loginSuccessResponse);
        $this->assertEquals($loginSuccessResponse, $this->object->execute());
    }

    public function testLoginFailure()
    {
        $jsonRequest = '{"username":"invalid@example.com", "password":"invalid"}';
        $loginFailureResponse = '{"message":"Invalid login or password."}';

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn($jsonRequest);

        $this->request
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn('POST');

        $this->request
            ->expects($this->any())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->jsonHelperMock
            ->expects($this->any())
            ->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'invalid@example.com', 'password' => 'invalid']);

        $customerMock = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerAccountManagementMock
            ->expects($this->any())
            ->method('authenticate')
            ->with('invalid@example.com', 'invalid')
            ->willThrowException(new InvalidEmailOrPasswordException(__('Invalid login or password.')));

        $this->customerSession->expects($this->never())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerMock);

        $this->customerSession->expects($this->never())->method('regenerateId');

        $result = [
            'errors' => true,
            'message' => __('Invalid login or password.')
        ];
        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturn($loginFailureResponse);

        $this->assertEquals($loginFailureResponse, $this->object->execute());
    }
}
