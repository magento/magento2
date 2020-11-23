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
    private $backendDataHelperMock;

    /**
     * @var Forgotpassword
     */
    private $controller;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var SecurityManager|MockObject
     */
    private $securityManagerMock;

    /**
     * @var User|MockObject
     */
    private $userCollectionMock;

    protected function setUp(): void
    {
        $contextMock = $this->prepareContext();

        $userFactoryMock = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->securityManagerMock = $this->getMockBuilder(SecurityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->userCollectionMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSize', 'addFieldToFilter', 'load'])
            ->getMock();

        $userCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->userCollectionMock);

        $this->backendDataHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $notificatorMock = $this->getMockBuilder(NotificatorInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new Forgotpassword(
            $contextMock,
            $userFactoryMock,
            $this->securityManagerMock,
            $userCollectionFactoryMock,
            $this->backendDataHelperMock,
            $notificatorMock
        );
    }

    public function testExecuteNoEmailNoParams()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->messageManagerMock->expects($this->never())
            ->method('addError');

        $this->controller->execute();
    }

    public function testExecuteEmptyEmail()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Please enter an email address.'));

        $this->controller->execute();
    }

    public function testExecuteInvalidEmail()
    {
        $email = 'invalid email address';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Please correct this email address:'));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $email = 'user1@example.com';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $message = __('We\'ll email you a link to reset your password.');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with($message)
            ->willReturnSelf();

        $this->backendDataHelperMock->expects($this->once())
            ->method('getHomePageUrl')
            ->willReturn('/homepage.URL');

        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteSecurityViolationException()
    {
        $email = 'user1@example.com';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $message = __('security violation exception message');
        $this->securityManagerMock->expects($this->once())
            ->method('performSecurityCheck')
            ->with(PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST, $email)
            ->willThrowException(new SecurityViolationException($message));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message)
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('admin')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteException()
    {
        $email = 'user1@example.com';
        $exception = new \Exception('Exception');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(['unused', 'but', 'cannot', 'be', 'empty']);

        $this->userCollectionMock->expects($this->once())
            ->method('getSize')
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('We\'re unable to send the password reset email.'))
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('admin')
            ->willReturnSelf();

        $this->controller->execute();
    }

    private function prepareContext()
    {
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'getParams'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $viewMock = $this->getMockBuilder(ViewInterface::class)
            ->getMockForAbstractClass();

        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactoryMock);

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($viewMock);

        return $contextMock;
    }
}
