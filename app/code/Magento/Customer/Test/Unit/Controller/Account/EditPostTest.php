<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

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
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var CustomerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerExtractor;

    /**
     * @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailNotification;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var AuthenticationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationMock;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMapperMock;

    protected function setUp()
    {
        $this->prepareContext();

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'setCustomerFormData', 'logout', 'start'])
            ->getMock();

        $this->customerAccountManagement = $this->getMockBuilder(\Magento\Customer\Model\AccountManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->validator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerExtractor = $this->getMockBuilder(\Magento\Customer\Model\CustomerExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailNotification = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMapperMock = $this->getMockBuilder(\Magento\Customer\Model\Customer\Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new EditPost(
            $this->context,
            $this->customerSession,
            $this->customerAccountManagement,
            $this->customerRepository,
            $this->validator,
            $this->customerExtractor
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'emailNotification',
            $this->emailNotification
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'authentication',
            $this->authenticationMock
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'customerMapper',
            $this->customerMapperMock
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

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGeneralSave()
    {
        $customerId = 1;
        $currentPassword = '1234567';
        $customerEmail = 'customer@example.com';

        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $currentCustomerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

        $this->customerSession->expects($this->once())
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
            ->method('credentialsChanged')
            ->with($currentCustomerMock, $customerEmail, false)
            ->willReturnSelf();

        $newCustomerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'customer_account_edited',
                ['email' => $customerEmail]
            );

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the account information.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('customer/account')
            ->willReturnSelf();

        $this->authenticationMock->expects($this->once())
            ->method('authenticate')
            ->willReturn(true);

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @param int $testNumber
     * @param string $exceptionClass
     * @param string $errorMessage
     *
     * @dataProvider changeEmailExceptionDataProvider
     */
    public function testChangeEmailException($testNumber, $exceptionClass, $errorMessage)
    {
        $customerId = 1;
        $password = '1234567';

        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

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

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($currentCustomerMock);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with('change_email')
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('getPost')
            ->with('current_password')
            ->willReturn($password);

        $exception = new $exceptionClass($errorMessage);
        $this->authenticationMock->expects($this->once())
            ->method('authenticate')
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($errorMessage)
            ->willReturnSelf();

        if ($testNumber==1) {
            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('*/*/edit')
                ->willReturnSelf();
        }

        if ($testNumber==2) {
            $this->customerSession->expects($this->once())
                ->method('logout');

            $this->customerSession->expects($this->once())
                ->method('start');

            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('customer/account/login')
                ->willReturnSelf();
        }

        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function changeEmailExceptionDataProvider()
    {
        return [
            [
                'testNumber' => 1,
                'exceptionClass' => \Magento\Framework\Exception\InvalidEmailOrPasswordException::class,
                'errorMessage' => __('The password doesn\'t match this account.')
            ],
            [
                'testNumber' => 2,
                'exceptionClass' => \Magento\Framework\Exception\State\UserLockedException::class,
                'errorMessage' => __('Invalid login or password.')
            ]
        ];
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

        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

        $this->customerSession->expects($this->once())
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
                ]
            ],
            [
                'current_password' => '',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user3@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => __('Password confirmation doesn\'t match entered password.'),
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 0,
                    'message' => '',
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'AuthenticationException',
                    'exception' => \Magento\Framework\Exception\AuthenticationException::class,
                ]
            ],
            [
                'current_password' => 'user1@example.com',
                'new_password' => 'user2@example.com',
                'confirmation_password' => 'user2@example.com',
                'errors' => [
                    'counter' => 1,
                    'message' => 'Exception',
                    'exception' => \Exception::class,
                ]
            ]
        ];
    }

    /**
     * @param string $message
     * @param string $exception
     *
     * @dataProvider exceptionDataProvider
     */
    public function testGeneralException(
        $message,
        $exception
    ) {
        $customerId = 1;

        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

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

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerSession->expects($this->once())
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
                'message' => 'LocalizedException',
                'exception' => \Magento\Framework\Exception\LocalizedException::class,
            ],
            [
                'message' => 'Exception',
                'exception' => \Exception::class,
            ],
        ];
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
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

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

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
        $newCustomerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
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
        $currentCustomerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
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
                ->with($exception, __('We can\'t save the customer.'))
                ->willReturnSelf();
        }

        $this->customerSession->expects($this->once())
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
