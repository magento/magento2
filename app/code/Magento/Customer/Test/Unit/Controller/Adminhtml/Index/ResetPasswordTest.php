<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResetPasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Request mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * Instance of mocked tested object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Controller\Adminhtml\Index
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\AccountManagementInterface
     */
    protected $_customerAccountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryMock;

    /**
     * Session mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend helper mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * Prepare required values
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['setRedirect', 'getHeader', '__wakeup']
        )->getMock();

        $this->_response->expects(
            $this->any()
        )->method(
            'getHeader'
        )->with(
            $this->equalTo('X-Frame-Options')
        )->will(
            $this->returnValue(true)
        );

        $this->_objectManager = $this->getMockBuilder(
            \Magento\Framework\App\ObjectManager::class
        )->disableOriginalConstructor()->setMethods(
            ['get', 'create']
        )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            \Magento\Framework\App\FrontController::class
        )->disableOriginalConstructor()->getMock();

        $actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            \Magento\Backend\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setIsUrlNotice', '__wakeup']
        )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            \Magento\Backend\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['getUrl']
        )->getMock();

        $this->messageManager = $this->getMockBuilder(
            \Magento\Framework\Message\Manager::class
        )->disableOriginalConstructor()->setMethods(
            ['addSuccess', 'addMessage', 'addException', 'addErrorMessage']
        )->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $contextArgs = [
            'getHelper',
            'getSession',
            'getAuthorization',
            'getTranslator',
            'getObjectManager',
            'getFrontController',
            'getActionFlag',
            'getMessageManager',
            'getLayoutFactory',
            'getEventManager',
            'getRequest',
            'getResponse',
            'getView',
            'getResultRedirectFactory'
        ];
        $contextMock = $this->getMockBuilder(
            \Magento\Backend\App\Action\Context::class
        )->disableOriginalConstructor()->setMethods(
            $contextArgs
        )->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->_request);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->_response);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->_objectManager);
        $contextMock->expects($this->any())->method('getFrontController')->willReturn($frontControllerMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($actionFlagMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->_helper);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->_session);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $viewMock =  $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->getMock();
        $viewMock->expects($this->any())->method('loadLayout')->willReturnSelf();
        $contextMock->expects($this->any())->method('getView')->willReturn($viewMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->_customerAccountManagementMock = $this->getMockBuilder(
            \Magento\Customer\Api\AccountManagementInterface::class
        )->getMock();

        $this->_customerRepositoryMock = $this->getMockBuilder(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        )->getMock();

        $args = [
            'context' => $contextMock,
            'customerAccountManagement' => $this->_customerAccountManagementMock,
            'customerRepository' => $this->_customerRepositoryMock,
        ];

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\ResetPassword::class,
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
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue(false)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo($redirectLink));

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
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
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($customerId)
        );

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->will(
            $this->throwException(
                new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'customerId', 'fieldValue' => $customerId]
                    )
                )
            )
        );

        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('customer/index'),
            $this->equalTo([])
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo($redirectLink));

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
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
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($customerId)
        );

        // Setup a core exception to return
        $exception = new \Magento\Framework\Validator\Exception();
        $error = new \Magento\Framework\Message\Error('Something Bad happened');
        $exception->addMessage($error);

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->will(
            $this->throwException($exception)
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
        $exception = new \Magento\Framework\Exception\SecurityViolationException(__($securityText));
        $customerId = 1;
        $email = 'some@example.com';
        $websiteId = 1;

        $this->_request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($customerId)
        );
        $customer = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            ['getId', 'getEmail', 'getWebsiteId']
        );
        $customer->expects($this->once())->method('getEmail')->will($this->returnValue($email));
        $customer->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->will(
            $this->returnValue($customer)
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
            $this->equalTo($exception->getMessage())
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

        $error = new \Magento\Framework\Message\Warning('Something Not So Bad happened');
        $exception->addMessage($error);

        $this->_customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($exception);

        // Verify Warning is converted to an Error and message text is set to exception text
        $this->messageManager->expects($this->once())
            ->method('addMessage')
            ->with(new \Magento\Framework\Message\Error($warningText));

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
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($customerId)
        );

        // Setup a core exception to return
        $exception = new \Exception('Something Really Bad happened');

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->will(
            $this->throwException($exception)
        );

        // Verify error message is set
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addException'
        )->with(
            $this->equalTo($exception),
            $this->equalTo('Something went wrong while resetting customer password.')
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
            $this->equalTo('customer_id'),
            $this->equalTo(0)
        )->will(
            $this->returnValue($customerId)
        );

        $customer = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            ['getId', 'getEmail', 'getWebsiteId']
        );

        $customer->expects($this->once())->method('getEmail')->will($this->returnValue($email));
        $customer->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $this->_customerRepositoryMock->expects(
            $this->once()
        )->method(
            'getById'
        )->with(
            $customerId
        )->will(
            $this->returnValue($customer)
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
            'addSuccess'
        )->with(
            $this->equalTo('The customer will receive an email with a link to reset password.')
        );

        // verify redirect
        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('customer/*/edit'),
            $this->equalTo(['id' => $customerId, '_current' => true])
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with(
                $redirectLink,
                ['id' => $customerId, '_current' => true]
            );

        $this->assertInstanceOf(
             \Magento\Backend\Model\View\Result\Redirect::class,
            $this->_testedObject->execute()
        );
    }
}
