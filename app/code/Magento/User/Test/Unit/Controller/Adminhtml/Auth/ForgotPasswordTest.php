<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Controller\Adminhtml\Auth;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\SecurityManager;
use Magento\User\Controller\Adminhtml\Auth\Forgotpassword;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\Spi\NotificatorInterface;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $backendDataHelper;

    /**
     * @var Forgotpassword
     */
    private $controller;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirect;

    /**
     * @var SecurityManager|MockObject
     */
    private $securityManager;

    /**
     * @var User|MockObject
     */
    private $userCollection;

    protected function setUp(): void
    {
        $context = $this->prepareContext();

        $userFactory = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->securityManager = $this->getMockBuilder(SecurityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->userCollection = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSize', 'addFieldToFilter', 'load'])
            ->getMock();

        $userCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->userCollection);

        $this->backendDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $notificator = $this->getMockBuilder(NotificatorInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new Forgotpassword(
            $context,
            $userFactory,
            $this->securityManager,
            $userCollectionFactory,
            $this->backendDataHelper,
            $notificator
        );
    }

    public function testExecuteNoEmailNoParams()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn(null);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->messageManager->expects($this->never())
            ->method('addError');

        $this->controller->execute();
    }

    public function testExecuteEmptyEmail()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn(null);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('Please enter an email address.'));

        $this->controller->execute();
    }

    public function testExecuteInvalidEmail()
    {
        $email = 'invalid email address';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('Please correct this email address:'));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $email = 'user1@example.com';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $message = __('We\'ll email you a link to reset your password.');

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with($message)
            ->willReturnSelf();

        $this->backendDataHelper->expects($this->once())
            ->method('getHomePageUrl')
            ->willReturn('/homepage.URL');

        $this->response->expects($this->once())
            ->method('setRedirect')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteSecurityViolationException()
    {
        $email = 'user1@example.com';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $message = __('security violation exception message');
        $this->securityManager->expects($this->once())
            ->method('performSecurityCheck')
            ->with(PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, $email)
            ->willThrowException(new SecurityViolationException($message));

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($message)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('admin')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteException()
    {
        $email = 'user1@example.com';
        $exception = new \Exception('Exception');

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->userCollection->expects($this->once())
            ->method('getSize')
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('We\'re unable to send the password reset email.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('admin')
            ->willReturnSelf();

        $this->controller->execute();
    }

    private function prepareContext()
    {
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'getParams'])
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $view = $this->getMockBuilder(ViewInterface::class)
            ->getMockForAbstractClass();

        $context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);

        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);

        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $context->expects($this->any())
            ->method('getView')
            ->willReturn($view);

        return $context;
    }
}
