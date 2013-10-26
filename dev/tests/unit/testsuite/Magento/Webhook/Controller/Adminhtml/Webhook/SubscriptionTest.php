<?php
/**
 * \Magento\Webhook\Controller\Adminhtml\Webhook\Subscription
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

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockObjectManager;

    /** @var \Magento\Webhook\Controller\Adminhtml\Webhook\Subscription */
    protected $_subscriptionContr;

    /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
    protected $_objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $_mockApp;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockLayoutFilter;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEventManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockTranslateModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockBackendModSess;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockBackendCntCtxt;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockSubscriptionSvc;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRegistry;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockRequest;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockResponse;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockConfigScope;

    /**
     * Setup object manager and initialize mocks
     */
    protected function setUp()
    {
        /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_setMageObjectManager();

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
        $this->_mockSubscriptionSvc = $this->getMockBuilder('Magento\Webhook\Service\SubscriptionV1')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockRequest = $this->getMockBuilder('Magento\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockResponse = $this->getMockBuilder('Magento\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockRegistry = $this->getMockBuilder('Magento\Core\Model\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockConfigScope = $this->getMockBuilder('Magento\Config\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIndexAction()
    {
        $this->_verifyLoadAndRenderLayout();

        // renderLayout
        $this->_subscriptionContr = $this->_createSubscriptionController();
        $this->_subscriptionContr->indexAction();
    }

    public function testNewAction()
    {
        // verify the request is forwarded to 'edit' action
        $this->_mockRequest->expects($this->any())->method('setActionName')->with('edit')
            ->will( $this->returnValue($this->_mockRequest));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->newAction();
    }

    public function testEditActionHasData()
    {
        // put data in session, the magic function getFormData is called so, must match __call method name
        $this->_mockBackendModSess->expects($this->any())
            ->method('__call')->will($this->returnValue(array('testkey' =>'testvalue')));

        $this->_verifyLoadAndRenderLayout();

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->editAction();
    }

    public function testEditActionNoDataAdd()
    {
        // Set the registry object to return 'new' so the 'Add Subscription' path is followed
        $this->_mockRegistry->expects($this->any())->method('registry')->will($this->returnValue('new'));

        $this->_verifyLoadAndRenderLayout();

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->editAction();
    }

    public function testEditException()
    {
        $exceptionMessage = 'An exception happened';
        // have load layout throw an exception
        $this->_mockObjectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Config\ScopeInterface')
            ->will($this->throwException(new \Magento\Core\Exception($exceptionMessage)));

        // verify the error
        $this->_mockBackendModSess->expects($this->once())
            ->method('addError')
            ->with($this->equalTo($exceptionMessage));

        $this->_subscriptionContr = $this->_createSubscriptionController();
        $this->_subscriptionContr->editAction();
    }

    public function testSaveAction()
    {
        // Use real translate model
        $this->_mockTranslateModel = null;

        $this->_mockRequest->expects($this->any())
            ->method('getPost')->will($this->returnValue(array('apikey' => 'abc')));
        $this->_mockRequest->expects($this->any())->method('getParam')->will($this->returnValue('1'));

        $this->_mockSubscriptionSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been saved.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->saveAction();
    }

    public function testSaveActionNoData()
    {
        // Use real translate model
        $this->_mockTranslateModel = null;

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

        // verify the error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('The subscription \'\' has not been saved, as no data was provided.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->saveAction();
    }

    public function testSaveActionException()
    {
        $this->_mockRequest->expects($this->any())->method('getParam')->will($this->returnValue('1'));

        // Have subscription service throw an exception to test exception path
        $exceptionMessage = 'an exception happened';
        $this->_mockSubscriptionSvc->expects($this->any())
            ->method('get')
            ->with(1)
            ->will($this->throwException(new \Magento\Core\Exception($exceptionMessage)));

        // Verify error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($exceptionMessage));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->saveAction();
    }

    public function testSaveActionNew()
    {
        // Set the registry object to return 'new' so the 'create' path is followed
        $this->_mockRegistry->expects($this->any())->method('registry')->will($this->returnValue('new'));

        $this->_mockRequest->expects($this->any())->method('getPost')
            ->will($this->returnValue(array('apikey' => 'abc')));
        $this->_mockRequest->expects($this->any())->method('getParam')->will($this->returnValue('1'));

        $this->_mockSubscriptionSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been saved.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->saveAction();
    }

    /**
     * Test Save when action is not new, but there is no ID
     */
    public function testSaveActionNoId()
    {
        // Set the registry object to return 'new' so the 'create' path is followed
        $this->_mockRegistry->expects($this->any())->method('registry')->will($this->returnValue('old'));

        $this->_mockRequest->expects($this->any())->method('getPost')
            ->will($this->returnValue(array('apikey' => 'abc', 'name' => 'testSubscription')));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'testSubscription\' has been saved.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->saveAction();
    }

    /**
     * Test deleteAction when subscription is an alias, not created by user.
     */
    public function testDeleteActionAlias()
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

        $this->_mockSubscriptionSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'),
                       'alias' => 'true'
                )
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;
        // Verify error message
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('The subscription \'nameTest\' can not be removed.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->deleteAction();
    }

    public function testDeleteAction()
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

        $this->_mockSubscriptionSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been removed.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->deleteAction();
    }

    public function testDeleteActionException ()
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


        $this->_mockSubscriptionSvc->expects($this->any())->method('get')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));

        // Have subscription service throw an exception to go down exception path
        $exceptionMessage = 'Exceptions happen.';
        $this->_mockSubscriptionSvc->expects($this->any())
            ->method('delete')
            ->will($this->throwException(new \Magento\Core\Exception($exceptionMessage)));

        // Verify error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($exceptionMessage));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->deleteAction();
    }

    public function testRevokeAction()
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

        $this->_mockSubscriptionSvc->expects($this->any())->method('revoke')->will($this->returnValue(
                array( 'name' => 'nameTest',
                       'subscription_id' => '1',
                       'topics' => array('topic1', 'topic2'))
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // verify success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been revoked.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->revokeAction();
    }

    public function testRevokeActionNoData()
    {
        // Verify error
        $this->_mockTranslateModel = null;
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('No Subscription ID was provided with the request.'));
        $this->_subscriptionContr = $this->_createSubscriptionController();
        $this->_subscriptionContr->revokeAction();
    }

    public function testRevokeActionException()
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

        // Have subscription service throw an exception to go down exception path
        $exceptionMessage = 'Exceptions happen.';
        $this->_mockSubscriptionSvc->expects($this->any())
            ->method('revoke')
            ->will($this->throwException(new \Magento\Core\Exception($exceptionMessage)));

        // Verify error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($exceptionMessage));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->revokeAction();
    }

    public function testActivateAction()
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

        $this->_mockSubscriptionSvc->expects($this->once())->method('activate')->with(1)->will($this->returnValue(
                array( 'name' => 'nameTest')
            ));

        // Use real translate model
        $this->_mockTranslateModel = null;

        // success message
        $this->_mockBackendModSess->expects($this->once())->method('addSuccess')
            ->with($this->equalTo('The subscription \'nameTest\' has been activated.'));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->activateAction();
    }

    public function testActivateActionNoData()
    {
        // Use real translate model
        $this->_mockTranslateModel = null;

        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo('No Subscription ID was provided with the request.'));
        $this->_subscriptionContr = $this->_createSubscriptionController();
        $this->_subscriptionContr->activateAction();
    }

    public function testActivateActionException()
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

        // Have subscription service throw an exception to go down exception path
        $exceptionMessage = 'An exception occurred';
        $this->_mockSubscriptionSvc->expects($this->any())
            ->method('activate')
            ->will($this->throwException(new \Magento\Core\Exception($exceptionMessage)));

        // Verify error
        $this->_mockBackendModSess->expects($this->once())->method('addError')
            ->with($this->equalTo($exceptionMessage));

        $subscriptionContr = $this->_createSubscriptionController();
        $subscriptionContr->activateAction();
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
     * Creates the SubscriptionController to test.
     *
     * @return \Magento\Webhook\Controller\Adminhtml\Webhook\Subscription
     */
    protected function _createSubscriptionController()
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

        // for _setActiveMenu
        $blockMock = $this->getMockBuilder('Magento\Backend\Block\Menu')
            ->disableOriginalConstructor()
            ->getMock();
        $menuMock = $this->getMockBuilder('Magento\Backend\Model\Menu')
            ->disableOriginalConstructor()
            ->getMock();
        $menuMock->expects($this->any())->method('getParentItems')->will($this->returnValue(array()));
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnValue($menuMock));

        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMock));
        $layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));

        $contextParameters = array(
            'layout' => $layoutMock,
            'objectManager' => $this->_mockObjectManager,
            'session' => $this->_mockBackendModSess,
            'translator' => $this->_mockTranslateModel,
            'request' => $this->_mockRequest,
            'response' => $this->_mockResponse,
        );

        $this->_mockBackendCntCtxt = $this->_objectManagerHelper
            ->getObject('Magento\Backend\Controller\Context',
                $contextParameters);

        $subControllerParams = array(
            'context' => $this->_mockBackendCntCtxt,
            'subscriptionService' => $this->_mockSubscriptionSvc,
            'registry' => $this->_mockRegistry,
        );

        /** Create SubscriptionController to test */
        $subscriptionContr = $this->_objectManagerHelper
            ->getObject('Magento\Webhook\Controller\Adminhtml\Webhook\Subscription',
                $subControllerParams);
        return $subscriptionContr;
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
