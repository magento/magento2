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
 * @category    Magento
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require 'Mage/DesignEditor/controllers/Adminhtml/System/Design/EditorController.php';
/**
 * Test backend controller for the design editor
 */
class Mage_DesignEditor_Controller_Adminhtml_System_Design_EditorControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Adminhtml_System_Design_EditorController
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');

        $request = $this->getMock('Mage_Core_Controller_Request_Http');
        $request->expects($this->any())->method('setActionName')->will($this->returnSelf());

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);

        /** @var $layoutMock Mage_Core_Model_Layout|PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMock('Mage_Core_Model_Layout',
            array(
                'getBlock',
                'getUpdate',
                'addHandle',
                'load',
                'generateXml',
                'getNode',
                'generateElements',
                'getMessagesBlock'
            ),
            array(), '', false);
        /** @var $layoutMock Mage_Core_Model_Layout */
        $layoutMock->expects($this->any())->method('generateXml')->will($this->returnSelf());
        $layoutMock->expects($this->any())->method('getNode')
            ->will($this->returnValue(new Varien_Simplexml_Element('<root />')));
        $blockMessage = $this->getMock('Mage_Core_Block_Messages',
            array('addMessages', 'setEscapeMessageFlag', 'addStorageType'), array(), '', false);
        $layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMessage));

        $blockMock = $this->getMock('Mage_Core_Block_Template', array('setActive', 'getMenuModel', 'getParentItems'),
            array(), '', false);
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnSelf());
        $blockMock->expects($this->any())->method('getParentItems')->will($this->returnValue(array()));

        $layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $layoutMock->expects($this->any())->method('getUpdate')->will($this->returnSelf());

        $layoutFactory = $this->getMock('Mage_Core_Model_Layout_Factory', array('createLayout'), array(), '', false);
        $layoutFactory->expects($this->any())->method('createLayout')->will($this->returnValue($layoutMock));


        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Mage_DesignEditor_Adminhtml_System_Design_EditorController',
            array(
                'request' => $request,
                'objectManager' => $this->_objectManagerMock,
                'layoutFactory' => $layoutFactory,
                'invokeArgs' => array(
                    'helper' => $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false),
                    'session'=> $this->getMock('Mage_Backend_Model_Session', array(), array(), '', false),
            ))
        );

        $this->_model = $objectManagerHelper
            ->getObject('Mage_DesignEditor_Adminhtml_System_Design_EditorController', $constructArguments);
    }

    /**
     * Return mocked theme collection factory model
     *
     * @param int $countCustomization
     * @return Mage_Core_Model_Resource_Theme_CollectionFactory
     */
    protected function _getThemeCollectionFactory($countCustomization)
    {
        $themeCollectionMock = $this->getMockBuilder('Mage_Core_Model_Resource_Theme_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addTypeFilter', 'getSize'))
            ->getMock();

        $themeCollectionMock->expects($this->once())
            ->method('addTypeFilter')
            ->with(Mage_Core_Model_Theme::TYPE_VIRTUAL)
            ->will($this->returnValue($themeCollectionMock));

        $themeCollectionMock->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($countCustomization));

        /** @var Mage_Core_Model_Resource_Theme_CollectionFactory $collectionFactory */
        $collectionFactory = $this->getMock(
            'Mage_Core_Model_Resource_Theme_CollectionFactory', array('create'), array(), '', false
        );
        $collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($themeCollectionMock));

        return $collectionFactory;
    }

    /**
     * @covers Mage_DesignEditor_Adminhtml_System_Design_EditorController::indexAction
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($countCustomization)
    {
        $this->_objectManagerMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->_getObjectManagerMap($countCustomization, 'index')));
        $this->assertNull($this->_model->indexAction());
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return array(
            array(4),
            array(0)
        );
    }

    /**
     * @covers Mage_DesignEditor_Adminhtml_System_Design_EditorController::firstEntranceAction
     * @dataProvider firstEntranceActionDataProvider
     */
    public function testFirstEntranceAction($countCustomization)
    {
        $this->_objectManagerMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->_getObjectManagerMap($countCustomization)));
        $this->assertNull($this->_model->firstEntranceAction());
    }

    /**
     * @return array
     */
    public function firstEntranceActionDataProvider()
    {
        return array(
            array(3),
            array(0)
        );
    }

    /**
     * @param int $countCustomization
     * @return array
     */
    protected function _getObjectManagerMap($countCustomization)
    {
        $translate = $this->getMock('Mage_Core_Model_Translate', array(), array(), '', false);
        $translate->expects($this->any())->method('translate')
            ->will($this->returnSelf());

        $storeManager = $this->getMock('Mage_Core_Model_StoreManager',
            array('getStore', 'getBaseUrl'), array(), '', false);
        $storeManager->expects($this->any())->method('getStore')
            ->will($this->returnSelf());

        $eventManager = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $authMock = $this->getMock('Magento_AuthorizationInterface');
        $authMock->expects($this->any())->method('filterAclNodes')->will($this->returnSelf());
        $backendSession = $this->getMock('Mage_Backend_Model_Session', array('getMessages', 'getEscapeMessages'),
            array(), '', false);
        $backendSession->expects($this->any())->method('getMessages')->will(
            $this->returnValue($this->getMock('Mage_Core_Model_Message_Collection', array(), array(), '', false))
        );

        $inlineMock = $this->getMock('Mage_Core_Model_Translate_Inline', array(), array(), '', false);
        $aclFilterMock = $this->getMock('Mage_Core_Model_Layout_Filter_Acl', array(), array(), '', false);

        return array(
            array(
                'Mage_Core_Model_Resource_Theme_CollectionFactory',
                $this->_getThemeCollectionFactory($countCustomization)
            ),
            array('Mage_Core_Model_Translate', $translate),
            array('Mage_Core_Model_Config', $configMock),
            array('Mage_Core_Model_Event_Manager', $eventManager),
            array('Mage_Core_Model_StoreManager', $storeManager),
            array('Magento_AuthorizationInterface', $authMock),
            array('Mage_Backend_Model_Session', $backendSession),
            array('Mage_Core_Model_Translate_Inline', $inlineMock),
            array('Mage_Core_Model_Layout_Filter_Acl', $aclFilterMock),
        );
    }
}
