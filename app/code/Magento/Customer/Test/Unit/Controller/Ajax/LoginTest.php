<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test customer ajax login controller
 */
namespace Magento\Customer\Test\Unit\Controller\Ajax;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class LoginTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse', 'representJson', 'setHttpResponseCode'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock(
            '\Magento\Customer\Model\Session',
            [
                'isLoggedIn',
                'getLastCustomerId',
                'getBeforeAuthUrl',
                'setBeforeAuthUrl',
                'setCustomerDataAsLoggedIn',
                'regenerateId'
            ],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            ['get'],
            [],
            '',
            false
        );
        $this->customerAccountManagementMock =
            $this->getMock(
                '\Magento\Customer\Model\AccountManagement',
                ['authenticate'],
                [],
                '',
                false
            );

        $this->jsonHelperMock = $this->getMock(
            '\Magento\Framework\Json\Helper\Data',
            ['jsonDecode'],
            [],
            '',
            false
        );

        $this->resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRaw = $this->getMockBuilder('Magento\Framework\Controller\Result\Raw')
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Customer\Controller\Ajax\Login',
            [
                'customerSession' => $this->customerSession,
                'helper' => $this->jsonHelperMock,
                'request' => $this->request,
                'response' => $this->response,
                'resultRawFactory' => $resultRawFactory,
                'resultJsonFactory' => $this->resultJsonFactory,
                'objectManager' => $this->objectManager,
                'customerAccountManagement' => $this->customerAccountManagementMock,
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

        $customerMock = $this->getMockForAbstractClass('Magento\Customer\Api\Data\CustomerInterface');
        $this->customerAccountManagementMock
            ->expects($this->any())
            ->method('authenticate')
            ->with('customer@example.com', 'password')
            ->willReturn($customerMock);

        $this->customerSession->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerMock);

        $this->customerSession->expects($this->once())->method('regenerateId');

        $this->resultRaw->expects($this->never())->method('setHttpResponseCode');

        $result = [
            'errors' => false,
            'message' => __('Login successful.')
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

        $customerMock = $this->getMockForAbstractClass('Magento\Customer\Api\Data\CustomerInterface');
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
