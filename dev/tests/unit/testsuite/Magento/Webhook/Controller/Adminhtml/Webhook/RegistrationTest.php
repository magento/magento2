<?php
/**
 * \Magento\Webhook\Controller\Adminhtml\Webhook\Registration
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Controller\Adminhtml\Webhook;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockObjectManager;

    /** @var \Magento\Webhook\Controller\Adminhtml\Webhook\Registration */
    protected $_registrationContr;

    /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
    protected $_objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $_mockApp;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEventManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockTranslateModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockLayoutFilter;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockBackendModSess;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockBackendCntCtxt;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRequest;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockSubSvc;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockResponse;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockBackendHlpData;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfigScope;

    protected function setUp()
    {
        /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_setMageObjectManager();

        $this->_mockBackendHlpData = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        // Initialize mocks which are used in several test cases
        $this->_mockApp = $this->getMockBuilder('Magento\Core\Model\App')
            ->setMethods( array('getConfig'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockConfig = $this->getMockBuilder('Magento\Core\Model\Config')->disableOriginalConstructor()
            ->getMock();
        $this->_mockApp->expects($this->any())->method('getConfig')->will($this->returnValue($this->_mockConfig));
        $this->_mockEventManager = $this->getMockBuilder('Magento\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockLayoutFilter = $this->getMockBuilder('Magento\Core\Model\Layout\Filter\Acl')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockBackendModSess = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockTranslateModel = $this->getMockBuilder('Magento\Core\Model\Translate')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockConfigScope = $this->getMockBuilder('Magento\Config\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockSubSvc = $this->getMockBuilder('Magento\Webhook\Service\SubscriptionV1')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequest = $this->getMockBuilder('Magento\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockResponse = $this->getMock('Magento\App\Response\Http');
    }

    public function testActivateActionException()
    {
        $expectedMessage = 'not subscribed';
        $this->_mockSubSvc->expects($this->any())->method('get')
            ->will($this->throwException(new \Magento\Core\Exception($expectedMessage)));

        // verify the error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($expectedMessage));
        $registrationContr = $this->_createRegistrationController();
        $registrationContr->activateAction();
    }

    public function testActivateAction()
    {
        $this->_verifyLoadAndRenderLayout();
        $this->_registrationContr = $this->_createRegistrationController();
        $this->_registrationContr->activateAction();
    }

    public function testAcceptAction()
    {
        // Verify redirect to registration
        $this->_mockBackendHlpData->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/webhook_registration/user'), array('id' => ''));
        $this->_registrationContr = $this->_createRegistrationController();
        $this->_registrationContr->acceptAction();
    }

    public function testAcceptActionNotSubscribed()
    {
        $expectedMessage = 'not subscribed';
        $this->_mockSubSvc->expects($this->any())->method('get')
            ->will($this->throwException(new \Magento\Core\Exception($expectedMessage)));

        // verify the error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($expectedMessage));

        $registrationContr = $this->_createRegistrationController();
        $registrationContr->acceptAction();
    }

    public function testUserAction()
    {
        $this->_verifyLoadAndRenderLayout();
        $this->_registrationContr = $this->_createRegistrationController();
        $this->_registrationContr->userAction();
    }

    public function testUserActionNotSubscribed()
    {
        $expectedMessage = 'not subscribed';
        $this->_mockSubSvc->expects($this->any())->method('get')
            ->will($this->throwException(new \Magento\Core\Exception($expectedMessage)));

        // verify the error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($expectedMessage));

        $registrationContr = $this->_createRegistrationController();
        $registrationContr->userAction();
    }

    public function testRegisterActionNoData()
    {
        // Use real translate model
        $this->_mockTranslateModel = null;

        // Verify error message
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('API Key, API Secret and Contact Email are required fields.'));
        $registrationContr = $this->_createRegistrationController();
        $registrationContr->registerAction();
    }

    public function testRegisterActionInvalidEmail()
    {

        $this->_mockRequest->expects($this->any())->method('getParam')
            ->will( $this->returnValueMap(
                    array(
                         array('id', null, '1'),
                         array('apikey', null, '2'),
                         array('apisecret', null, 'secret'),
                         array('email', null, 'invalid.email.example.com'),
                         array('company', null, 'Example')
                    )
                ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // Verify error message
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('Invalid Email address provided'));
        $registrationContr = $this->_createRegistrationController();
        $registrationContr->registerAction();
    }

    public function testRegisterAction()
    {
        $this->_mockRequest->expects($this->any())->method('getParam')
            ->will( $this->returnValueMap(
                array(
                    array('id', null, '1'),
                    array('apikey', null, '2'),
                    array('apisecret', null, 'secret'),
                    array('email', null, 'test@example.com'),
                    array('company', null, 'Example')
                )
            ));

        $this->_mockSubSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));
        $this->_mockSubSvc->expects($this->any())->method('update')->will($this->returnArgument(0));

        $registrationContr = $this->_createRegistrationController();

        // Verify redirect to success page
        $this->_mockBackendHlpData->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/webhook_registration/succeeded'), array('id' => '1'));

        $registrationContr->registerAction();
    }

    public function testFailedAction()
    {
        $this->_verifyLoadAndRenderLayout();
        $this->_registrationContr = $this->_createRegistrationController();
        $this->_registrationContr->failedAction();
    }

    public function testSucceededAction()
    {
        $this->_mockRequest->expects($this->any())->method('getParam')
            ->will( $this->returnValueMap(
                    array(
                         array('id', null, '1'),
                    )
                ));

        $this->_verifyLoadAndRenderLayout();

        $this->_mockSubSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest' )
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been activated.'));

        $this->_registrationContr = $this->_createRegistrationController();
        $this->_registrationContr->succeededAction();
    }

    public function testSucceededActionNotSubscribed()
    {
        $this->_verifyLoadAndRenderLayout();
        $this->_mockSubSvc = $this->getMockBuilder('Magento\Webhook\Service\SubscriptionV1')
            ->disableOriginalConstructor()
            ->getMock();
        $expectedMessage = 'not subscribed';
        $this->_mockSubSvc->expects($this->any())->method('get')
            ->will($this->throwException(new \Magento\Core\Exception($expectedMessage)));
        // verify the error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($expectedMessage));

        $registrationContr = $this->_createRegistrationController();
        $registrationContr->succeededAction();
    }

    /**
     * Makes sure that Mage has a mock object manager set.
     *
     */
    protected function _setMageObjectManager()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        \Magento\Core\Model\ObjectManager::setInstance($this->_mockObjectManager);
    }

    /**
     * Creates the RegistrationController to test.
     * @return \Magento\Webhook\Controller\Adminhtml\Webhook\Registration
     */
    protected function _createRegistrationController()
    {
        // Mock Layout passed into constructor
        $layoutMock = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMergeMock = $this->getMockBuilder('Magento\Core\Model\Layout\Merge')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->any())->method('getUpdate')->will($this->returnValue($layoutMergeMock));
        $testElement = new \Magento\Simplexml\Element('<test>test</test>');
        $layoutMock->expects($this->any())->method('getNode')->will($this->returnValue($testElement));
        $blockMock = $this->getMockBuilder('Magento\Core\Block\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMock));

        $contextParameters = array(
            'layout' => $layoutMock,
            'objectManager' => $this->_mockObjectManager,
            'session' => $this->_mockBackendModSess,
            'request' => $this->_mockRequest,
            'response' => $this->_mockResponse,
            'helper' => $this->_mockBackendHlpData,
            'translator' => $this->_mockTranslateModel,
        );

        $this->_mockBackendCntCtxt = $this->_objectManagerHelper
            ->getObject('Magento\Backend\Controller\Context',
                $contextParameters);

        $regControllerParams = array(
            'context' => $this->_mockBackendCntCtxt,
            'subscriptionService' => $this->_mockSubSvc,
        );

        /** @var \Magento\Webhook\Controller\Adminhtml\Webhook\Registration $registrationContr */
        $registrationContr = $this->_objectManagerHelper
            ->getObject('Magento\Webhook\Controller\Adminhtml\Webhook\Registration',
                $regControllerParams);
        return $registrationContr;
    }

    /**
     * Common mock 'expect' pattern.
     * Calls that need to be mocked out when
     * \Magento\Backend\Controller\AbstractAction loadLayout() and renderLayout() are called.
     */
    protected function _verifyLoadAndRenderLayout()
    {
        $map = array(
            array('Magento\Core\Model\Config', $this->_mockConfig),
            array('Magento\Core\Model\Layout\Filter\Acl', $this->_mockLayoutFilter),
            array('Magento\Backend\Model\Session', $this->_mockBackendModSess),
            array('Magento\Core\Model\Translate', $this->_mockTranslateModel),
            array('Magento\Config\ScopeInterface', $this->_mockConfigScope),
        );
        $this->_mockObjectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
    }
}
