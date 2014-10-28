<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\Customer;

/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_acctServiceMock;

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
     * Prepare required values
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http'
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
            'Magento\Framework\App\ObjectManager'
        )->disableOriginalConstructor()->setMethods(
            array('get', 'create')
        )->getMock();
        $frontControllerMock = $this->getMockBuilder(
            'Magento\Framework\App\FrontController'
        )->disableOriginalConstructor()->getMock();

        $actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder(
            'Magento\Backend\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            array('setIsUrlNotice', '__wakeup')
        )->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder(
            'Magento\Backend\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getUrl')
        )->getMock();

        $this->messageManager = $this->getMockBuilder(
            'Magento\Framework\Message\Manager'
        )->disableOriginalConstructor()->setMethods(
            array('addSuccess', 'addMessage', 'addException')
        )->getMock();

        $contextArgs = array(
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
            'getTitle',
            'getView'
        );
        $contextMock = $this->getMockBuilder(
            '\Magento\Backend\App\Action\Context'
        )->disableOriginalConstructor()->setMethods(
            $contextArgs
        )->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->_request));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->_response));
        $contextMock->expects(
            $this->any()
        )->method(
            'getObjectManager'
        )->will(
            $this->returnValue($this->_objectManager)
        );
        $contextMock->expects(
            $this->any()
        )->method(
            'getFrontController'
        )->will(
            $this->returnValue($frontControllerMock)
        );
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($actionFlagMock));

        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($this->_helper));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->_session));
        $contextMock->expects(
            $this->any()
        )->method(
            'getMessageManager'
        )->will(
            $this->returnValue($this->messageManager)
        );
        $titleMock =  $this->getMockBuilder('\Magento\Framework\App\Action\Title')->getMock();
        $contextMock->expects($this->any())->method('getTitle')->will($this->returnValue($titleMock));
        $viewMock =  $this->getMockBuilder('\Magento\Framework\App\ViewInterface')->getMock();
        $viewMock->expects($this->any())->method('loadLayout')->will($this->returnSelf());
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($viewMock));

        $this->_acctServiceMock = $this->getMockBuilder(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        )->getMock();

        $args = array('context' => $contextMock, 'accountService' => $this->_acctServiceMock);



        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\ResetPassword',
            $args
        );
    }

    public function testResetPasswordActionNoCustomer()
    {
        $redirectLink = 'http://example.com/customer/';
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

        $this->_helper->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('customer/index'),
            $this->equalTo(array())
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));
        $this->_testedObject->execute();
    }

    public function testResetPasswordActionInvalidCustomerId()
    {
        $redirectLink = 'http://example.com/customer/';
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

        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'getCustomer'
        )->with(
            $customerId
        )->will(
            $this->throwException(new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    ['fieldName' => 'customerId', 'fieldValue' => $customerId]
                )
            )
        );

        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('customer/index'),
            $this->equalTo(array())
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));
        $this->_testedObject->execute();
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
        $exception = new \Magento\Framework\Model\Exception();
        $error = new \Magento\Framework\Message\Error('Something Bad happened');
        $exception->addMessage($error);

        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'getCustomer'
        )->with(
            $customerId
        )->will(
            $this->throwException($exception)
        );

        // Verify error message is set
        $this->messageManager->expects($this->once())->method('addMessage')->with($this->equalTo($error));

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionCoreExceptionWarn()
    {
        $warningText = 'Warning';
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
        $exception = new \Magento\Framework\Model\Exception($warningText);
        $error = new \Magento\Framework\Message\Warning('Something Not So Bad happened');
        $exception->addMessage($error);

        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'getCustomer'
        )->with(
            $customerId
        )->will(
            $this->throwException($exception)
        );

        // Verify Warning is converted to an Error and message text is set to exception text
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addMessage'
        )->with(
            $this->equalTo(new \Magento\Framework\Message\Error($warningText))
        );

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

        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'getCustomer'
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
            $this->equalTo('An error occurred while resetting customer password.')
        );

        $this->_testedObject->execute();
    }

    public function testResetPasswordActionSendEmail()
    {
        $customerId = 1;
        $email = "test@example.com";
        $websiteId = 1;
        $redirectLink = 'http://example.com';

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


        $customerBuilder = $this->getMock('\Magento\Customer\Service\V1\Data\CustomerBuilder', [], [], '', false);
        $data = [
            'id' => $customerId,
            'email' => $email,
            'website_id' => $websiteId
        ];
        $customerBuilder->expects($this->once())->method('getData')->will($this->returnValue($data));

        $customer = new \Magento\Customer\Service\V1\Data\Customer($customerBuilder);

        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'getCustomer'
        )->with(
            $customerId
        )->will(
            $this->returnValue($customer)
        );

        // verify initiatePasswordReset() is called
        $this->_acctServiceMock->expects(
            $this->once()
        )->method(
            'initiatePasswordReset'
        )->with(
            $email,
            CustomerAccountServiceInterface::EMAIL_REMINDER,
            $websiteId
        );

        // verify success message
        $this->messageManager->expects(
            $this->once()
        )->method(
            'addSuccess'
        )->with(
            $this->equalTo('Customer will receive an email with a link to reset password.')
        );

        // verify redirect
        $this->_helper->expects(
            $this->any()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('customer/*/edit'),
            $this->equalTo(array('id' => $customerId, '_current' => true))
        )->will(
            $this->returnValue($redirectLink)
        );

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));

        $this->_testedObject->execute();
    }
}
