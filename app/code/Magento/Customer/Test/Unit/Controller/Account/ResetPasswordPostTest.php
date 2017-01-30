<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ResetPasswordPostTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Controller\Account\ResetPasswordPost */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

    /** @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountManagementMock;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepositoryMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactoryMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['unsRpToken', 'unsRpCustomerId'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagementMock = $this->getMockBuilder('Magento\Customer\Api\AccountManagementInterface')
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods(['getQuery', 'getPost'])
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Customer\Controller\Account\ResetPasswordPost',
            [
                'customerSession' => $this->sessionMock,
                'resultPageFactory' => $this->pageFactoryMock,
                'accountManagement' => $this->accountManagementMock,
                'customerRepository' => $this->customerRepositoryMock,
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecute()
    {
        $token = 'token';
        $customerId = '11';
        $password = 'password';
        $passwordConfirmation = 'password';
        $email = 'email@email.com';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $customerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder('\Magento\Customer\Api\Data\CustomerInterface')
            ->getMockForAbstractClass();

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->accountManagementMock->expects($this->once())
            ->method('resetPassword')
            ->with($email, $token, $password)
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('unsRpToken');
        $this->sessionMock->expects($this->once())
            ->method('unsRpCustomerId');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You updated your password.'))
            ->willReturnSelf();

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/login', [])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithException()
    {
        $token = 'token';
        $customerId = '11';
        $password = 'password';
        $passwordConfirmation = 'password';
        $email = 'email@email.com';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $customerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder('\Magento\Customer\Api\Data\CustomerInterface')
            ->getMockForAbstractClass();

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $this->accountManagementMock->expects($this->once())
            ->method('resetPassword')
            ->with($email, $token, $password)
            ->willThrowException(new \Exception('Exception.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Something went wrong while saving the new password.'))
            ->willReturnSelf();

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/createPassword', ['id' => $customerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithWrongConfirmation()
    {
        $token = 'token';
        $customerId = '11';
        $password = 'password';
        $passwordConfirmation = 'wrong_password';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $customerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('New Password and Confirm New Password values didn\'t match.'))
            ->willReturnSelf();

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/createPassword', ['id' => $customerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    public function testExecuteWithEmptyPassword()
    {
        $token = 'token';
        $customerId = '11';
        $password = '';
        $passwordConfirmation = '';

        $this->requestMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturnMap(
                [
                    ['token', $token],
                    ['id', $customerId],
                ]
            );
        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['password', $password],
                    ['password_confirmation', $passwordConfirmation],
                ]
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Please enter a new password.'))
            ->willReturnSelf();

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/createPassword', ['id' => $customerId, 'token' => $token])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
