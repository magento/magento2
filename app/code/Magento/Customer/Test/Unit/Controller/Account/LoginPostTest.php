<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Test customer account controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoginPost
     */
    protected $controller;

    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var AccountManagementInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var Url | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formkeyValidator;

    /**
     * @var AccountRedirect | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountRedirect;

    /**
     * @var Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var Redirect | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    protected function setUp()
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'isLoggedIn',
                'setCustomerDataAsLoggedIn',
                'regenerateId',
                'setUsername',
            ])
            ->getMock();

        $this->accountManagement = $this->getMockBuilder('Magento\Customer\Api\AccountManagementInterface')
            ->getMockForAbstractClass();

        $this->url = $this->getMockBuilder('Magento\Customer\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formkeyValidator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->accountRedirect = $this->getMockBuilder('Magento\Customer\Model\Account\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new LoginPost(
            $this->context,
            $this->session,
            $this->accountManagement,
            $this->url,
            $this->formkeyValidator,
            $this->accountRedirect
        );
    }

    /**
     * @param boolean $isLoggedIn
     * @param boolean $isValidFormKey
     * @dataProvider invalidFormKeyDataProvider
     */
    public function testExecuteInvalidFormKey(
        $isLoggedIn,
        $isValidFormKey
    ) {
        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $this->formkeyValidator->expects($this->any())
            ->method('validate')
            ->with($this->request)
            ->willReturn($isValidFormKey);

        $this->redirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function invalidFormKeyDataProvider()
    {
        return [
            [
                'isLoggedIn' => true,
                'isValidFormKey' => false,
            ],
            [
                'isLoggedIn' => false,
                'isValidFormKey' => false,
            ],
            [
                'isLoggedIn' => true,
                'isValidFormKey' => true,
            ],
        ];
    }

    public function testExecuteNoPostData()
    {
        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->formkeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(null);

        $this->accountRedirect->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteEmptyLoginData()
    {
        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->formkeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPost')
            ->with('login')
            ->willReturn([]);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('A login and a password are required.'))
            ->willReturnSelf();

        $this->accountRedirect->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExecuteSuccess()
    {
        $username = 'user1';
        $password = 'pass1';

        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->formkeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPost')
            ->with('login')
            ->willReturn([
                'username' => $username,
                'password' => $password,
            ]);

        $customerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->getMockForAbstractClass();

        $this->accountManagement->expects($this->once())
            ->method('authenticate')
            ->with($username, $password)
            ->willReturn($customerMock);

        $this->session->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerMock)
            ->willReturnSelf();
        $this->session->expects($this->once())
            ->method('regenerateId')
            ->willReturnSelf();

        $this->accountRedirect->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @param array $exceptionData
     *
     * @dataProvider exceptionDataProvider
     */
    public function testExecuteWithException(
        $exceptionData
    ) {
        $username = 'user1';
        $password = 'pass1';


        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->formkeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPost')
            ->with('login')
            ->willReturn([
                'username' => $username,
                'password' => $password,
            ]);

        $exception = new $exceptionData['exception'](__($exceptionData['message']));

        $this->accountManagement->expects($this->once())
            ->method('authenticate')
            ->with($username, $password)
            ->willThrowException($exception);

        $this->mockExceptions($exceptionData['exception'], $username);

        $this->accountRedirect->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        return [
            [
                [
                    'message' => 'EmailNotConfirmedException',
                    'exception' => '\Magento\Framework\Exception\EmailNotConfirmedException',
                ],
            ],
            [
                [
                    'message' => 'AuthenticationException',
                    'exception' => '\Magento\Framework\Exception\AuthenticationException',
                ],
            ],
            [
                [
                    'message' => 'Exception',
                    'exception' => '\Exception',
                ],
            ],
        ];
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([
                'isPost',
                'getPost',
            ])
            ->getMock();

        $this->redirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $redirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($redirectFactory);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    /**
     * @param string $exception
     * @param string $username
     * @return void
     */
    protected function mockExceptions($exception, $username)
    {
        $url = 'url1';

        switch ($exception) {
            case '\Magento\Framework\Exception\EmailNotConfirmedException':
                $this->url->expects($this->once())
                    ->method('getEmailConfirmationUrl')
                    ->with($username)
                    ->willReturn($url);

                $message = __(
                    'This account is not confirmed.' .
                    ' <a href="%1">Click here</a> to resend confirmation email.',
                    $url
                );
                $this->messageManager->expects($this->once())
                    ->method('addError')
                    ->with($message)
                    ->willReturnSelf();

                $this->session->expects($this->once())
                    ->method('setUsername')
                    ->with($username)
                    ->willReturnSelf();
                break;

            case '\Magento\Framework\Exception\AuthenticationException':
                $this->messageManager->expects($this->once())
                    ->method('addError')
                    ->with(__('Invalid login or password.'))
                    ->willReturnSelf();

                $this->session->expects($this->once())
                    ->method('setUsername')
                    ->with($username)
                    ->willReturnSelf();
                break;

            case '\Exception':
                $this->messageManager->expects($this->once())
                    ->method('addError')
                    ->with(__('Invalid login or password.'))
                    ->willReturnSelf();
                break;
        }
    }
}
