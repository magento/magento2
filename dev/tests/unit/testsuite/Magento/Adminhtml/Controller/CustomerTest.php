<?php
/**
 * Unit test for \Magento\Adminhtml\Controller\Customer controller
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class \Magento\Adminhtml\Controller\CustomerTest
 */
namespace Magento\Adminhtml\Controller;

class CustomerTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Adminhtml\Controller\Customer
     */
    protected $_testedObject;

    /**
     * ObjectManager mock instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\ObjectManager
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

        $this->_objectManager = $this->getMockBuilder('Magento\Core\Model\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'create'))
            ->getMock();
        $frontControllerMock = $this->getMockBuilder('Magento\App\FrontController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_session = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(array('setIsUrlNotice', 'addSuccess'))
            ->getMock();
        $this->_session->expects($this->any())->method('setIsUrlNotice');

        $this->_helper = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getUrl'))
            ->getMock();

        $translator = $this->getMockBuilder('Magento\Core\Model\Translate')
            ->disableOriginalConstructor()
            ->setMethods(array('getTranslateInline'))
            ->getMock();

        $contextArgs = array(
            'getHelper', 'getSession', 'getAuthorization', 'getTranslator', 'getObjectManager', 'getFrontController',
            'getLayoutFactory', 'getEventManager', 'getRequest', 'getResponse'
        );
        $contextMock = $this->getMockBuilder('Magento\Backend\Controller\Context')
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

        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($this->_helper));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->_session));
        $contextMock->expects($this->any())->method('getTranslator')->will($this->returnValue($translator));

        $args = array('context' => $contextMock);

        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_testedObject = $helperObjectManager->getObject('Magento\Adminhtml\Controller\Customer', $args);
    }

    /**
     * Test \Magento\Adminhtml\Controller\Customer::resetPasswordAction()
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
            ->with($this->equalTo('*/customer'), $this->equalTo(array()))
            ->will($this->returnValue($redirectLink));

        $this->_response->expects($this->once())->method('setRedirect')->with($this->equalTo($redirectLink));
        $this->_testedObject->resetPasswordAction();
    }

    /**
     * Test \Magento\Adminhtml\Controller\Customer::resetPasswordAction()
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

        $customerMock = $this->_getCustomerMock($customerId);

        $this->_objectManager->expects($this->any())
            ->method('create')
            ->with($this->equalTo('Magento\Customer\Model\Customer'))
            ->will($this->returnValue($customerMock));

        $this->_helper->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('*/customer'), $this->equalTo(array()))
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

        $customerHelperMock = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('generateResetPasswordLinkToken'))
            ->getMock();
        $customerHelperMock->expects($this->once())
            ->method('generateResetPasswordLinkToken')
            ->will($this->returnValue($token));

        $coreHelperMock = $this->getMockBuilder('Magento\Core\Model\Url')
            ->disableOriginalConstructor()
            ->setMethods(array('getUrl'))
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
            ->with($this->equalTo('Magento\Core\Model\Url'))
            ->will($this->returnValue($coreHelperMock));

        $this->_session->expects($this->once())
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
                'sendPasswordReminderEmail'))
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
