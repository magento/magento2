<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test customer ajax login controller
 */
namespace Magento\Customer\Controller\Ajax;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelper;

    protected function setUp()
    {
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            [
                'isPost',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie',
                'getRawBody',
                'getMethod',
                'isXmlHttpRequest'
            ],
            [],
            '',
            false
        );
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

        $this->dataHelper = $this->getMock(
            '\Magento\Core\Helper\Data',
            ['jsonDecode', 'jsonEncode'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Customer\Controller\Ajax\Login',
            [
                'customerSession' => $this->customerSession,
                'helper' => $this->dataHelper,
                'request' => $this->request,
                'response' => $this->response,
                'objectManager' => $this->objectManager,
                'customerAccountManagement' => $this->customerAccountManagementMock,
            ]
        );
    }

    public function testLogin()
    {
        $jsonRequest = '{"username":"customer@example.com", "password":"password"}';
        $loginSuccessResponse = '{"message":"Login successful."}';

        $this->request
            ->expects($this->any())
            ->method('getRawBody')
            ->willReturn($jsonRequest);

        $this->request
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn('POST');

        $this->request
            ->expects($this->any())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this->dataHelper
            ->expects($this->any())
            ->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'customer@example.com', 'password' => 'password']);

        $this->dataHelper
            ->expects($this->any())
            ->method('jsonEncode')
            ->with(['message' => 'Login successful.'])
            ->willReturn($loginSuccessResponse);

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

        $this->response->expects($this->once())->method('representJson')->with($loginSuccessResponse);

        $this->object->execute();
    }

    public function testLoginFailure()
    {
        $jsonRequest = '{"username":"invalid@example.com", "password":"invalid"}';
        $loginFailureResponse = '{"message":"Invalid login or password."}';

        $this->request
            ->expects($this->any())
            ->method('getRawBody')
            ->willReturn($jsonRequest);

        $this->request
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn('POST');

        $this->request
            ->expects($this->any())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this->dataHelper
            ->expects($this->any())
            ->method('jsonDecode')
            ->with($jsonRequest)
            ->willReturn(['username' => 'invalid@example.com', 'password' => 'invalid']);

        $this->dataHelper
            ->expects($this->any())
            ->method('jsonEncode')
            ->with(['message' => 'Invalid login or password.'])
            ->willReturn($loginFailureResponse);

        $customerMock = $this->getMockForAbstractClass('Magento\Customer\Api\Data\CustomerInterface');
        $this->customerAccountManagementMock
            ->expects($this->any())
            ->method('authenticate')
            ->with('invalid@example.com', 'invalid')
            ->willThrowException(new InvalidEmailOrPasswordException('Invalid login or password.', []));

        $this->customerSession->expects($this->never())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerMock);

        $this->customerSession->expects($this->never())->method('regenerateId');

        $this->response->expects($this->once())->method('representJson')->with($loginFailureResponse);

        $this->response->expects($this->once())->method('setHttpResponseCode')->with(401);

        $this->object->execute();
    }
}

