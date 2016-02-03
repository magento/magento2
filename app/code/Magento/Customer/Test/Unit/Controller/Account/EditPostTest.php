<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Helper\EmailNotification;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditPost
     */
    protected $model;

    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\AccountManagement | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var Validator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var CustomerExtractor | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerExtractor;

    /**
     * @var EmailNotification | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotification;

    /**
     * @var CurrentCustomer | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentCustomerHelper;

    /**
     * @var RedirectFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Redirect | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    protected function setUp()
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerId',
                'setCustomerFormData',
            ])
            ->getMock();

        $this->customerAccountManagement = $this->getMockBuilder('Magento\Customer\Model\AccountManagement')
            ->disableOriginalConstructor()
            ->setMethods(['changePassword'])
            ->getMock();

        $this->customerRepository = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();

        $this->validator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerExtractor = $this->getMockBuilder('Magento\Customer\Model\CustomerExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentCustomerHelper = $this->getMockBuilder('Magento\Customer\Helper\Session\CurrentCustomer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailNotification = $this->getMockBuilder('Magento\Customer\Helper\EmailNotification')
            ->disableOriginalConstructor()
            ->setMethods(['sendNotificationEmailsIfRequired'])
            ->getMock();

        $this->model = new EditPost(
            $this->context,
            $this->session,
            $this->customerAccountManagement,
            $this->customerRepository,
            $this->validator,
            $this->customerExtractor,
            $this->currentCustomerHelper,
            $this->emailNotification
        );
    }

    public function testInvalidFormKey()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    public function testNoPostValues()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    public function testGeneralSave()
    {
        $customerId = 1;
        $currentPassword = '1234567';

        $address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('current_password')
            ->willReturn($currentPassword);

        $this->currentCustomerHelper->expects($this->once())
            ->method('validatePassword')
            ->with($currentPassword)
            ->willReturn(true);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);

        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($newCustomerMock)
            ->willReturnSelf();

        $this->customerExtractor->expects($this->once())
            ->method('extract')
            ->with('customer_account_edit', $this->request)
            ->willReturn($newCustomerMock);

        $this->emailNotification->expects($this->once())
            ->method('sendNotificationEmailsIfRequired')
            ->with($currentCustomerMock, $newCustomerMock, false)
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the account information.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('customer/account')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return void
     */
    public function testChangeEmailError()
    {
        $customerId = 1;
        $password = '1234567';
        $errorMessage = __('The password doesn\'t match this account.');

        $address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->customerExtractor->expects($this->once())
            ->method('extract')
            ->with('customer_account_edit', $this->request)
            ->willReturn($newCustomerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->with('change_email')
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('current_password')
            ->willReturn($password);

        $exception = new \Magento\Framework\Exception\InvalidEmailOrPasswordException(__($errorMessage));
        $this->currentCustomerHelper->expects($this->once())
            ->method('validatePassword')
            ->with($password)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($errorMessage)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmationPassword
     * @param [] $errors
     *
     * @dataProvider changePasswordDataProvider
     */
    public function testChangePassword(
        $currentPassword,
        $newPassword,
        $confirmationPassword,
        $errors
    ) {
        $customerId = 1;
        $customerEmail = 'user1@example.com';

        $address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);

        $this->customerExtractor->expects($this->once())
            ->method('extract')
            ->with('customer_account_edit', $this->request)
            ->willReturn($newCustomerMock);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturnOnConsecutiveCalls(false, false, true);

        $this->request->expects($this->any())
            ->method('getPostValue')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getPost')
            ->willReturnMap([
                ['current_password', null, $currentPassword],
                ['password', null, $newPassword],
                ['password_confirmation', null, $confirmationPassword],
            ]);

        $currentCustomerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        // Prepare errors processing
        if ($errors['counter'] > 0) {
            $this->mockChangePasswordErrors($currentPassword, $newPassword, $errors, $customerEmail);
        } else {
            $this->customerAccountManagement->expects($this->once())
                ->method('changePassword')
                ->with($customerEmail, $currentPassword, $newPassword)
                ->willReturnSelf();

            $this->customerRepository->expects($this->once())
                ->method('save')
                ->with($newCustomerMock)
                ->willReturnSelf();

            $this->messageManager->expects($this->once())
                ->method('addSuccess')
                ->with(__('You saved the account information.'))
                ->willReturnSelf();

            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('customer/account')
                ->willReturnSelf();
        }

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function changePasswordDataProvider()
    {
        return [
            [
                'current_password' => '',
                'new_password' => '',
                'confirmation_password' => '',
                'errors' => [
                    'counter' => 1,
                    'message' => __('Please enter new password.'),
                ],
            ],
            [
                'current_password' => '',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user3@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => __('Confirm your new password.'),
                ],
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 0,
                    'message' => '',
                ],
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'AuthenticationException',
                    'exception' => '\Magento\Framework\Exception\AuthenticationException',
                ],
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'Exception',
                    'exception' => '\Exception',
                ],
            ],
        ];
    }

    /**
     * @param int $counter
     * @param string $message
     * @param string $exception
     *
     * @dataProvider exceptionDataProvider
     */
    public function testGeneralException(
        $counter,
        $message,
        $exception
    ) {
        $customerId = 1;

        $address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $exception = new $exception(__($message));

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                ['change_email'],
                ['change_password']
            )
            ->willReturn(false);

        $this->request->expects($this->any())
            ->method('getPostValue')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->session->expects($this->once())
            ->method('setCustomerFormData')
            ->with(true)
            ->willReturnSelf();

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($newCustomerMock)
            ->willThrowException($exception);

        $this->customerExtractor->expects($this->once())
            ->method('extract')
            ->with('customer_account_edit', $this->request)
            ->willReturn($newCustomerMock);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function exceptionDataProvider()
    {
        return [
            [
                'counter' => 1,
                'message' => 'LocalizedException',
                'exception' => '\Magento\Framework\Exception\LocalizedException',
            ],
            [
                'counter' => 1,
                'message' => 'Exception',
                'exception' => '\Exception',
            ],
        ];
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);
    }

    /**
     * @param int $customerId
     * @param \PHPUnit_Framework_MockObject_MockObject $address
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getNewCustomerMock($customerId, $address)
    {
        $newCustomerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->getMockForAbstractClass();

        $newCustomerMock->expects($this->once())
            ->method('setId')
            ->with($customerId)
            ->willReturnSelf();
        $newCustomerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $newCustomerMock->expects($this->once())
            ->method('setAddresses')
            ->with([$address])
            ->willReturn(null);

        return $newCustomerMock;
    }

    /**
     * @param int $customerId
     * @param \PHPUnit_Framework_MockObject_MockObject $address
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCurrentCustomerMock($customerId, $address)
    {
        $currentCustomerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $currentCustomerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);

        return $currentCustomerMock;
    }

    /**
     * @param string $currentPassword
     * @param string $newPassword
     * @param [] $errors
     * @param string $customerEmail
     * @return void
     */
    protected function mockChangePasswordErrors($currentPassword, $newPassword, $errors, $customerEmail)
    {
        if (!empty($errors['exception'])) {
            $exception = new $errors['exception'](__($errors['message']));

            $this->customerAccountManagement->expects($this->once())
                ->method('changePassword')
                ->with($customerEmail, $currentPassword, $newPassword)
                ->willThrowException($exception);

            $this->messageManager->expects($this->any())
                ->method('addException')
                ->with($exception, __('We can\'t save the customer.')
                    . $exception->getMessage()
                    . '<pre>' . $exception->getTraceAsString() . '</pre>')
                ->willReturnSelf();
        }

        $this->session->expects($this->once())
            ->method('setCustomerFormData')
            ->with(true)
            ->willReturnSelf();

        $this->messageManager->expects($this->any())
            ->method('addError')
            ->with($errors['message'])
            ->willReturnSelf();

        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();
    }
}
