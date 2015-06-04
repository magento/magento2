<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Account;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);

        $this->messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);

        $this->resultRedirectFactory = $this->getMock(
            'Magento\Framework\Controller\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->context = $this->objectManager->getObject(
            'Magento\Framework\App\Action\Context',
            [
                'request' => $this->request,
                'response' => $this->response,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->resultRedirectFactory
            ]
        );

        $this->redirectResultMock = $this->getMock('Magento\Framework\Controller\Result\Redirect', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->resultPageFactory = $this->getMock('Magento\Framework\View\Result\PageFactory', [], [], '', false);
        $this->customerAccountManagement = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AccountManagementInterface',
            [],
            '',
            false
        );
        $this->customerRepository = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false
        );
        $this->formKeyValidator = $this->getMock('Magento\Framework\Data\Form\FormKey\Validator', [], [], '', false);
        $this->customerExtractor = $this->getMock('Magento\Customer\Model\CustomerExtractor', [], [], '', false);
        $this->customer = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            'dataCustomer',
            false
        );
    }

    /**
     * @return \Magento\Customer\Controller\Account\EditPost
     */
    public function getController()
    {
        return $this->objectManager->getObject(
            'Magento\Customer\Controller\Account\EditPost',
            [
                'context' => $this->context,
                'customerSession' => $this->customerSession,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'resultPageFactory' => $this->resultPageFactory,
                'customerAccountManagement' => $this->customerAccountManagement,
                'customerRepository' => $this->customerRepository,
                'formKeyValidator' => $this->formKeyValidator,
                'customerExtractor' => $this->customerExtractor
            ]
        );
    }

    public function testEditPostActionWithInvalidFormKey()
    {
        $this->resultRedirectFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectResultMock);
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $this->redirectResultMock
            ->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturn('http://test.com/customer/account/edit');

        $this->assertSame($this->redirectResultMock, $this->getController()->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testEditPostActionWithAuthenticationExceptionWhenTryingChangePassword()
    {
        $customerId = 24;
        $address = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
        $loadedCustomer = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            'loadedCustomer',
            false
        );

        $loadedCustomer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address, $address]);

        $this->resultRedirectFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectResultMock);
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->request
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->customerSession
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerExtractor
            ->expects($this->once())
            ->method('extract')
            ->willReturn($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $this->customer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $this->customerRepository
            ->expects($this->exactly(2))
            ->method('getById')
            ->with($customerId)
            ->willReturn($loadedCustomer);
        $this->customer
            ->expects($this->once())
            ->method('setAddresses')
            ->with([$address, $address]);
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('change_password')
            ->willReturn(true);

        $this->request
            ->expects($this->at(2))
            ->method('getPost')
            ->with('current_password', null)
            ->willReturn(123);
        $this->request
            ->expects($this->at(3))
            ->method('getPost')
            ->with('password', null)
            ->willReturn(321);
        $this->request
            ->expects($this->at(4))
            ->method('getPost')
            ->with('password_confirmation', null)
            ->willReturn(321);

        $this->customerAccountManagement
            ->expects($this->once())
            ->method('changePassword')
            ->willThrowException(new \Magento\Framework\Exception\AuthenticationException(__('Error')));
        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('Error');

        $exception = new \Magento\Framework\Exception\InputException(__('Error'));
        $this->customerRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addException')
            ->with($exception, 'Invalid input');
        $this->request
            ->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);

        $messageCollection = $this->getMock('Magento\Framework\Message\Collection', [], [], '', false);
        $messageCollection
            ->expects($this->once())
            ->method('getCount')
            ->willReturn(3);
        $this->messageManager
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn($messageCollection);
        $this->customerSession
            ->expects($this->once())
            ->method('__call')
            ->with('setCustomerFormData', [[]]);

        $this->redirectResultMock
            ->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit')
            ->willReturn('http://test.com/customer/account/edit');

        $this->assertSame($this->redirectResultMock, $this->getController()->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testEditPostActionWithoutErrors()
    {
        $customerId = 24;
        $address = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
        $loadedCustomer = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            'loadedCustomer',
            false
        );

        $loadedCustomer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address, $address]);

        $this->resultRedirectFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectResultMock);
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->request
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $this->customerSession
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerExtractor
            ->expects($this->once())
            ->method('extract')
            ->willReturn($this->customer);
        $this->customer
            ->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $this->customer
            ->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $this->customerRepository
            ->expects($this->exactly(2))
            ->method('getById')
            ->with($customerId)
            ->willReturn($loadedCustomer);
        $this->customer
            ->expects($this->once())
            ->method('setAddresses')
            ->with([$address, $address]);
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('change_password')
            ->willReturn(true);

        $this->request
            ->expects($this->at(2))
            ->method('getPost')
            ->with('current_password', null)
            ->willReturn(123);
        $this->request
            ->expects($this->at(3))
            ->method('getPost')
            ->with('password', null)
            ->willReturn(321);
        $this->request
            ->expects($this->at(4))
            ->method('getPost')
            ->with('password_confirmation', null)
            ->willReturn(321);

        $this->customerAccountManagement
            ->expects($this->once())
            ->method('changePassword');

        $this->customerRepository
            ->expects($this->once())
            ->method('save');

        $messageCollection = $this->getMock('Magento\Framework\Message\Collection', [], [], '', false);
        $messageCollection
            ->expects($this->once())
            ->method('getCount')
            ->willReturn(0);
        $this->messageManager
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn($messageCollection);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->with('You saved the account information.');

        $this->redirectResultMock
            ->expects($this->once())
            ->method('setPath')
            ->with('customer/account')
            ->willReturn('http://test.com/customer/account/edit');

        $this->assertSame($this->redirectResultMock, $this->getController()->execute());
    }
}
