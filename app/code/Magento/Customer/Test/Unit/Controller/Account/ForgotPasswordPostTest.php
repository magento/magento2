<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Controller\Account\ForgotPasswordPost;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPostTest extends TestCase
{
    /**
     * @var ForgotPasswordPost
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $accountManagement;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var ResultRedirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var ResultRedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Request|MockObject
     */
    protected $request;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var SessionCleanerInterface|MockObject
     */
    private $sessionCleanerMock;

    protected function setUp(): void
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->accountManagement = $this->getMockBuilder(AccountManagementInterface::class)
            ->getMockForAbstractClass();

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->sessionCleanerMock = $this->createMock(SessionCleanerInterface::class);

        $this->controller = new ForgotPasswordPost(
            $this->context,
            $this->session,
            $this->accountManagement,
            $this->escaper,
            $this->customerRepository,
            $this->sessionCleanerMock
        );
    }

    public function testExecuteEmptyEmail()
    {
        $this->request->expects($this->once())
            ->method('getPost')
            ->with('email')
            ->willReturn(null);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Please enter your email.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/forgotpassword')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecute()
    {
        $email = 'user1@example.com';
        $customerId = '1';

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('email')
            ->willReturn($email);

        $this->accountManagement->expects($this->once())
            ->method('initiatePasswordReset')
            ->with($email, AccountManagement::EMAIL_RESET)
            ->willReturnSelf();

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('get')
            ->with($email)
            ->willReturn($customer);

        $this->sessionCleanerMock->expects($this->once())->method('clearFor')->with($customerId)->willReturnSelf();

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($email)
            ->willReturn($email);

        $message = __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $email
        );
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with($message)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteNoSuchEntityException()
    {
        $email = 'user1@example.com';

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('email')
            ->willReturn($email);

        $this->accountManagement->expects($this->once())
            ->method('initiatePasswordReset')
            ->with($email, AccountManagement::EMAIL_RESET)
            ->willThrowException(new NoSuchEntityException(__('NoSuchEntityException')));

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customer->expects($this->never())
            ->method('getId');

        $this->sessionCleanerMock->expects($this->never())->method('clearFor');

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($email)
            ->willReturn($email);

        $message = __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $email
        );
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with($message)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteException()
    {
        $email = 'user1@example.com';
        $exception = new \Exception('Exception');

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('email')
            ->willReturn($email);

        $this->accountManagement->expects($this->once())
            ->method('initiatePasswordReset')
            ->with($email, AccountManagement::EMAIL_RESET)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('We\'re unable to send the password reset email.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/forgotpassword')
            ->willReturnSelf();

        $this->controller->execute();
    }

    protected function prepareContext()
    {
        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getPost',
            ])
            ->getMock();

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }
}
