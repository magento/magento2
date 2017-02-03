<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\Collection as MessageCollection;
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

    /**
     * @var MessageCollection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageCollection;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMapperMock;

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

        $this->customerAccountManagement = $this->getMockBuilder('Magento\Customer\Api\AccountManagementInterface')
            ->getMockForAbstractClass();

        $this->customerRepository = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();

        $this->validator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerExtractor = $this->getMockBuilder('Magento\Customer\Model\CustomerExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMapperMock = $this->getMockBuilder('Magento\Customer\Model\Customer\Mapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new EditPost(
            $this->context,
            $this->session,
            $this->customerAccountManagement,
            $this->customerRepository,
            $this->validator,
            $this->customerExtractor
        );

        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty('customerMapper');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->customerMapperMock);
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

        $address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();

        $currentCustomerMock = $this->getCurrentCustomerMock($customerId, $address);
        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('change_password')
            ->willReturn(false);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

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

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->willReturn($this->messageCollection);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the account information.'))
            ->willReturnSelf();

        $this->messageCollection->expects($this->once())
            ->method('getCount')
            ->willReturn(0);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('customer/account')
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
        $currentCustomerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);

        $newCustomerMock = $this->getNewCustomerMock($customerId, $address);

        $this->customerMapperMock->expects($this->once())
            ->method('toFlatArray')
            ->with($currentCustomerMock)
            ->willReturn([]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('change_password')
            ->willReturn(true);
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

        $this->session->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        // Prepare errors processing
        if ($errors['counter'] > 0) {
            $this->mockChangePasswordErrors($currentPassword, $newPassword, $errors, $customerEmail);
        } else {
            $this->customerAccountManagement->expects($this->once())
                ->method('changePassword')
                ->with($customerEmail, $currentPassword, $newPassword)
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

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->willReturn($this->messageCollection);

        $this->messageCollection->expects($this->once())
            ->method('getCount')
            ->willReturn($errors['counter']);


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
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('change_password')
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

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->willReturn($this->messageCollection);

        $this->messageCollection->expects($this->once())
            ->method('getCount')
            ->willReturn($counter);

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
                'message' => 'AuthenticationException',
                'exception' => '\Magento\Framework\Exception\AuthenticationException',
            ],
            [
                'counter' => 1,
                'message' => 'InputException',
                'exception' => '\Magento\Framework\Exception\InputException',
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

        $this->messageCollection = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();

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
                ->with($exception, __('Something went wrong while changing the password.'))
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

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturnSelf();
    }
}
