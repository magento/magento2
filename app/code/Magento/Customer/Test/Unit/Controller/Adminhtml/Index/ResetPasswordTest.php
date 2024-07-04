<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Customer\Controller\Adminhtml\Index\ResetPassword;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\Warning;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResetPasswordTest extends TestCase
{
    /**
     * Request mock instance
     *
     * @var MockObject|RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var MockObject|ResponseInterface
     */
    protected $_response;

    /**
     * Instance of mocked tested object
     *
     * @var MockObject|Index
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var MockObject|\Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var MockObject|AccountManagementInterface
     */
    protected $_customerAccountManagementMock;

    /**
     * @var MockObject|CustomerRepositoryInterface
     */
    protected $_customerRepositoryMock;

    /**
     * Session mock instance
     *
     * @var MockObject|\Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend helper mock instance
     *
     * @var MockObject|Data
     */
    protected $_helper;

    /**
     * @var MockObject|ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * Prepare required values
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->_request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['setRedirect', 'getHeader', '__wakeup']
            )->getMock();

        $this->_response->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            'X-Frame-Options'
        )->willReturn(
            true
        );

        $this->_objectManager = $this->getMockBuilder(
            ObjectManager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['get', 'create']
            )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            FrontController::class
        )->disableOriginalConstructor()
            ->getMock();

        $actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            Session::class
        )->disableOriginalConstructor()
            ->addMethods(
                ['setIsUrlNotice', '__wakeup']
            )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getUrl']
            )->getMock();

        $this->messageManager = $this->getMockBuilder(
            Manager::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['addSuccessMessage', 'addMessage', 'addExceptionMessage', 'addErrorMessage']
            )->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $addContextArgs = [
            'getTranslator',
            'getFrontController',
            'getLayoutFactory'
        ];

        $contextArgs = [
            'getHelper',
            'getSession',
            'getAuthorization',
            'getObjectManager',
            'getActionFlag',
            'getMessageManager',
            'getEventManager',
            'getRequest',
            'getResponse',
            'getView',
            'getResultRedirectFactory'
        ];

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->addMethods($addContextArgs)
            ->onlyMethods($contextArgs)
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->_response);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManager);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($actionFlagMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->_helper);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->_session);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $viewMock =  $this->getMockBuilder(ViewInterface::class)
            ->getMock();
        $viewMock->expects($this->any())->method('loadLayout')->willReturnSelf();
        $contextMock->expects($this->any())->method('getView')->willReturn($viewMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->_customerAccountManagementMock = $this->getMockBuilder(
            AccountManagementInterface::class
        )->getMock();

        $this->_customerRepositoryMock = $this->getMockBuilder(
            CustomerRepositoryInterface::class
        )->getMock();

        $args = [
            'context' => $contextMock,
            'customerAccountManagement' => $this->_customerAccountManagementMock,
            'customerRepository' => $this->_customerRepositoryMock,
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            ResetPassword::class,
            $args
        );
    }

    public function testResetPasswordActionNoCustomer()
    {
        $redirectLink = 'customer/index';
        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            false
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($redirectLink);

        $this->assertInstanceOf(
            Redirect::class,
            $this->_testedObject->execute()
        );
    }

    public function testResetPasswordActionInvalidCustomerId()
    {
        $redirectLink = 'customer/index';
        $customerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            $customerId
        );

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->willThrowException(
            new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'customerId', 'fieldValue' => $customerId]
                )
            )
        );

        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            'customer/index',
            []
        )->willReturn(
            $redirectLink
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($redirectLink);

        $this->assertInstanceOf(
            Redirect::class,
            $this->_testedObject->execute()
        );
    }

    public function testResetPasswordActionCoreException()
    {
        $customerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            $customerId
        );

        // Setup a core exception to return
        $exception = new \Magento\Framework\Validator\Exception();
        $error = new Error('Something Bad happened');
        $exception->addMessage($error);

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->willThrowException(
            $exception
        );

        // Verify error message is set
        $this->messageManager->expects($this->once())
            ->method('addMessage')
            ->with($error);

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionSecurityException()
    {
        $securityText = 'Security violation.';
        $exception = new SecurityViolationException(__($securityText));
        $customerId = 1;
        $email = 'some@example.com';
        $websiteId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            $customerId
        );
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn($email);
        $customer->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->willReturn(
            $customer
        );
        $this->_customerAccountManagementMock->expects(
            $this->once()
        )->method(
            'initiatePasswordReset'
        )->willThrowException($exception);

        $this->messageManager->expects(
            $this->once()
        )->method(
            'addErrorMessage'
        )->with(
            $exception->getMessage()
        );

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionCoreExceptionWarn()
    {
        $warningText = 'Warning';
        $customerId = 1;

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with('customer_id', 0)
            ->willReturn($customerId);

        // Setup a core exception to return
        $exception = new \Magento\Framework\Validator\Exception(__($warningText));

        $error = new Warning('Something Not So Bad happened');
        $exception->addMessage($error);

        $this->_customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($exception);

        // Verify Warning is converted to an Error and message text is set to exception text
        $this->messageManager->expects($this->once())
            ->method('addMessage')
            ->with(new Error($warningText));

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionException()
    {
        $customerId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            $customerId
        );

        // Setup a core exception to return
        $exception = new \Exception('Something Really Bad happened');

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->willThrowException(
            $exception
        );

        // Verify error message is set
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addExceptionMessage'
        )->with(
            $exception,
            'Something went wrong while resetting customer password.'
        );

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionSendEmail()
    {
        $customerId = 1;
        $email = 'test@example.com';
        $websiteId = 1;
        $redirectLink = 'customer/*/edit';

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'customer_id',
            0
        )->willReturn(
            $customerId
        );

        $customer = $this->getMockForAbstractClass(CustomerInterface::class);

        $customer->expects($this->once())->method('getEmail')->willReturn($email);
        $customer->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->willReturn(
            $customer
        );

        // verify initiatePasswordReset() is called
        $this->_customerAccountManagementMock->expects(
            $this->once()
        )->method(
            'initiatePasswordReset'
        )->with(
            $email,
            AccountManagement::EMAIL_REMINDER,
            $websiteId
        );

        // verify success message
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addSuccessMessage'
        )->with(
            'The customer will receive an email with a link to reset password.'
        );

        // verify redirect
        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            'customer/*/edit',
            ['id' => $customerId, '_current' => true]
        )->willReturn(
            $redirectLink
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with(
                $redirectLink,
                ['id' => $customerId, '_current' => true]
            );

        $this->assertInstanceOf(
            Redirect::class,
            $this->_testedObject->execute()
        );
    }
}
