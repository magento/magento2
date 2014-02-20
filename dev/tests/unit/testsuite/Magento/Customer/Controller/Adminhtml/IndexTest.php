<?php
/**
 * Unit test for \Magento\Customer\Controller\Adminhtml\Index controller
 *
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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class \Magento\Customer\Controller\Adminhtml\IndexTest
 */
namespace Magento\Customer\Controller\Adminhtml;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Request mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Response mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\App\ResponseInterface
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\App\ObjectManager
     */
    protected $_objectManager;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Prepare required values
     */
    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Magento\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_response = $this->getMockBuilder('Magento\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(array('setRedirect', 'getHeader'))
            ->getMock();

        $this->_response->expects($this->any())
            ->method('getHeader')
            ->with($this->equalTo('X-Frame-Options'))
            ->will($this->returnValue(true));

        $this->_objectManager = $this->getMockBuilder('Magento\App\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'create'))
            ->getMock();
        $frontControllerMock = $this->getMockBuilder('Magento\App\FrontController')
            ->disableOriginalConstructor()
            ->getMock();

        $actionFlagMock = $this->getMockBuilder('Magento\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(array('setIsUrlNotice', '__wakeup'))
            ->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getUrl'))
            ->getMock();

        $this->messageManager = $this->getMockBuilder('Magento\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods(array('addSuccess'))
            ->getMock();

        $contextArgs = array(
            'getHelper', 'getSession', 'getAuthorization', 'getTranslator', 'getObjectManager',
            'getFrontController', 'getActionFlag', 'getMessageManager',
            'getLayoutFactory', 'getEventManager', 'getRequest', 'getResponse'
        );
        $contextMock = $this->getMockBuilder('\Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods($contextArgs)
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->_request));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->_response));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->_objectManager));
        $contextMock->expects($this->any())
            ->method('getFrontController')
            ->will($this->returnValue($frontControllerMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($actionFlagMock));

        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($this->_helper));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->_session));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManager));

        $args = array('context' => $contextMock);

        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject('Magento\Customer\Controller\Adminhtml\Index', $args);
    }

    /**
     * Test \Magento\Backend\Controller\Customer::resetPasswordAction()
     */
    public function testResetPasswordActionNoCustomer()
    {
        $redirectLink = 'http://example.com/customer/';
        $this->_request->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'), $this->equalTo(0))
            ->will($this->returnValue(false)
        );
        $this->_helper->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('customer/index'), $this->equalTo(array()))
            ->will($this->returnValue($redirectLink));

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));
        $this->_testedObject->resetPasswordAction();
    }

    /**
     * Test \Magento\Backend\Controller\Customer::resetPasswordAction()
     */
    public function testResetPasswordActionNoCustomerId()
    {
        $redirectLink = 'http://example.com/customer/';
        $customerId = 1;

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'), $this->equalTo(0))
            ->will($this->returnValue($customerId)
        );

        $customerMock = $this->_getCustomerMock($customerId, false);

        $this->_objectManager->expects($this->any())
            ->method('create')
            ->with($this->equalTo('Magento\Customer\Model\Customer'))
            ->will($this->returnValue($customerMock));

        $this->_helper->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('customer/index'), $this->equalTo(array()))
            ->will($this->returnValue($redirectLink));

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));
        $this->_testedObject->resetPasswordAction();
    }

    /**
     * Test that sendPasswordReminderEmail() is called
     */
    public function testResetPasswordActionSendEmail()
    {
        $customerId = 1;
        $token = 2;
        $testUrl = 'http://example.com';

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'), $this->equalTo(0))
            ->will($this->returnValue($customerId)
        );

        $customerMock = $this->_getCustomerMock($customerId, true);
        $customerMock->expects($this->once())
            ->method('changeResetPasswordLinkToken')
            ->with($this->equalTo($token));
        $customerMock->expects($this->once())
            ->method('setResetPasswordUrl')
            ->with($this->equalTo($testUrl));
        $customerMock->expects($this->once())
            ->method('sendPasswordReminderEmail');

        $customerHelperMock = $this->getMock('Magento\Customer\Helper\Data',
            array('generateResetPasswordLinkToken'), array(), '', false
        );
        $customerHelperMock->expects($this->any())
            ->method('generateResetPasswordLinkToken')
            ->will($this->returnValue($token));

        $coreHelperMock = $this->getMockBuilder('Magento\Url')
            ->disableOriginalConstructor()
            ->setMethods(array('getUrl', '__wakeup'))
            ->getMock();
        $coreHelperMock->expects($this->any())->method('getUrl')->will($this->returnValue($testUrl));

        $this->_objectManager->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo('Magento\Customer\Model\Customer'))
            ->will($this->returnValue($customerMock));

        $this->_objectManager->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('Magento\Customer\Helper\Data'))
            ->will($this->returnValue($customerHelperMock));

        $this->_objectManager->expects($this->at(2))
            ->method('create')
            ->with($this->equalTo('Magento\UrlInterface'))
            ->will($this->returnValue($coreHelperMock));

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with($this->equalTo('Customer will receive an email with a link to reset password.'));
        $this->_testedObject->resetPasswordAction();
    }

    /**
     * Return customer mock instance
     *
     * @param int $customerId
     * @param null|int $returnId
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Customer
     */
    protected function _getCustomerMock($customerId, $returnId = null)
    {
        $customerMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getId', 'changeResetPasswordLinkToken', 'setResetPasswordUrl',
                'sendPasswordReminderEmail', '__wakeup'))
            ->getMock();
        $customerMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo($customerId));
        $customerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($returnId));
        return $customerMock;
    }
}
