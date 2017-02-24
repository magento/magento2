<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $resultRedirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isLoggedIn',
                'setCustomerDataAsLoggedIn',
                'regenerateId',
                'setUsername',
            ])
            ->getMock();

        $this->accountManagement = $this->getMockBuilder(\Magento\Customer\Api\AccountManagementInterface::class)
            ->getMockForAbstractClass();

        $this->url = $this->getMockBuilder(\Magento\Customer\Model\Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formkeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->accountRedirect = $this->getMockBuilder(\Magento\Customer\Model\Account\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new LoginPost(
            $this->context,
            $this->session,
            $this->accountManagement,
            $this->url,
            $this->formkeyValidator,
            $this->accountRedirect
        );
        $reflection = new \ReflectionClass(get_class($this->controller));
        $reflectionProperty = $reflection->getProperty('scopeConfig');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->controller, $this->scopeConfig);
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

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
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
            ->willReturn($this->resultRedirect);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
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
            ->willReturn($this->resultRedirect);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteSuccessCustomRedirect()
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

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('customer/startup/redirect_dashboard')
            ->willReturn(0);

        $cookieUrl = 'someUrl1';
        $returnUrl = 'someUrl2';
        $this->accountRedirect->expects($this->once())
            ->method('getRedirectCookie')
            ->willReturn($cookieUrl);
        $this->accountRedirect->expects($this->once())
            ->method('clearRedirectCookie');

        $this->redirect->expects($this->once())
            ->method('success')
            ->with($cookieUrl)
            ->willReturn($returnUrl);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($returnUrl);

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

        $this->accountRedirect->expects($this->never())
            ->method('getRedirect')
            ->willReturn($this->resultRedirect);

        $cookieMetadataManager = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(false);
        $refClass = new \ReflectionClass(LoginPost::class);
        $refProperty = $refClass->getProperty('cookieMetadataManager');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->controller, $cookieMetadataManager);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
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

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('customer/startup/redirect_dashboard')
            ->willReturn(1);

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
            ->willReturn($this->resultRedirect);

        $cookieMetadataManager = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadataManager->expects($this->once())
            ->method('getCookie')
            ->with('mage-cache-sessid')
            ->willReturn(true);
        $cookieMetadataFactory = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMetadata = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadata::class)
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

        $refClass = new \ReflectionClass(LoginPost::class);
        $cookieMetadataManagerProperty = $refClass->getProperty('cookieMetadataManager');
        $cookieMetadataManagerProperty->setAccessible(true);
        $cookieMetadataManagerProperty->setValue($this->controller, $cookieMetadataManager);

        $cookieMetadataFactoryProperty = $refClass->getProperty('cookieMetadataFactory');
        $cookieMetadataFactoryProperty->setAccessible(true);
        $cookieMetadataFactoryProperty->setValue($this->controller, $cookieMetadataFactory);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
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
            ->willReturn($this->resultRedirect);

        $this->assertSame($this->resultRedirect, $this->controller->execute());
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
                    'exception' => \Magento\Framework\Exception\EmailNotConfirmedException::class,
                ],
            ],
            [
                [
                    'message' => 'AuthenticationException',
                    'exception' => \Magento\Framework\Exception\AuthenticationException::class,
                ],
            ],
            [
                [
                    'message' => 'Exception',
                    'exception' => '\Exception',
                ],
            ],
            [
                [
                    'message' => 'UserLockedException',
                    'exception' => \Magento\Framework\Exception\State\UserLockedException::class,
                ],
            ],
        ];
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isPost',
                'getPost',
            ])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->getMock();
        $this->context->expects($this->atLeastOnce())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);

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
            case \Magento\Framework\Exception\EmailNotConfirmedException::class:
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

            case \Magento\Framework\Exception\AuthenticationException::class:
                $this->messageManager->expects($this->once())
                    ->method('addError')
                    ->with(__('You did not sign in correctly or your account is temporarily disabled.'))
                    ->willReturnSelf();

                $this->session->expects($this->once())
                    ->method('setUsername')
                    ->with($username)
                    ->willReturnSelf();
                break;

            case '\Exception':
                $this->messageManager->expects($this->once())
                    ->method('addError')
                    ->with(__('An unspecified error occurred. Please contact us for assistance.'))
                    ->willReturnSelf();
                break;

            case \Magento\Framework\Exception\State\UserLockedException::class:
                $message = __(
                    'You did not sign in correctly or your account is temporarily disabled.'
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
        }
    }
}
