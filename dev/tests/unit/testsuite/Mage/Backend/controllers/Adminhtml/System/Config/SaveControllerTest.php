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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once (realpath(dirname(__FILE__) . '/../../../../../../../../../../')
    . '/app/code/core/Mage/Backend/Controller/System/ConfigAbstract.php');

require_once (realpath(dirname(__FILE__) . '/../../../../../../../../../../')
    . '/app/code/core/Mage/Backend/controllers/Adminhtml/System/Config/SaveController.php');

class Mage_Backend_Adminhtml_System_Config_SaveControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Adminhtml_System_Config_SaveController
     */
    protected $_controller;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMock;

    public function setUp()
    {
        $this->_requestMock = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false, false);
        $responseMock = $this->getMock('Mage_Core_Controller_Response_Http', array(), array(), '', false, false);
        $objectManagerMock = $this->getMock('Magento_ObjectManager', array(), array(), '', false, false);
        $frontControllerMock = $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false, false);
        $authorizationMock = $this->getMock('Mage_Core_Model_Authorization', array(), array(), '', false, false);
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false, false);
        $configStructureMock = $this->getMock('Mage_Backend_Model_Config_Structure',
            array(), array(), '', false, false
        );
        $this->_configFactoryMock = $this->getMock('Mage_Backend_Model_Config_Factory',
            array(), array(), '', false, false
        );
        $this->_eventManagerMock = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false, false);
        $this->_appMock = $this->getMock('Mage_Core_Model_App', array(), array(), '', false, false);
        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false, false);
        $this->_sessionMock = $this->getMock('Mage_Backend_Model_Session',
            array('addSuccess', 'addException'), array(), '', false, false
        );

        $this->_authMock = $this->getMock('Mage_Backend_Model_Auth_Session',
            array('getUser'), array(), '', false, false
        );

        $this->_sectionMock = $this->getMock(
            'Mage_Backend_Model_Config_Structure_Element_Section', array(), array(), '', false
        );

        $configStructureMock->expects($this->any())->method('getElement')
            ->will($this->returnValue($this->_sectionMock));

        $helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $helperMock->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $responseMock->expects($this->once())->method('setRedirect')->with('*/system_config/edit');

        $this->_controller = new Mage_Backend_Adminhtml_System_Config_SaveController($this->_requestMock,
            $responseMock,
            $objectManagerMock,
            $frontControllerMock,
            $authorizationMock,
            $configStructureMock,
            $this->_configMock,
            $this->_configFactoryMock,
            $this->_eventManagerMock,
            $this->_appMock,
            $this->_authMock,
            array(
                'helper' => $helperMock,
                'session' => $this->_sessionMock,
            )
        );
    }

    public function testIndexActionWithAllowedSection()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $this->_configMock->expects($this->once())->method('reinit');
        $this->_sessionMock->expects($this->once())->method('addSuccess')->with('The configuration has been saved.');

        $groups = array('some_key' => 'some_value');
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $requestPostMap = array(
            array('groups', null, $groups),
            array('config_state', null, 'test_config_state'),
        );

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Mage_Backend_Model_Config', array(), array(), '', false, false);
        $backendConfigMock->expects($this->once())->method('save');

        $params = array('section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groups
        );
        $this->_configFactoryMock->expects($this->once())->method('create')->with(array('data' => $params))
            ->will($this->returnValue($backendConfigMock));

        $this->_controller->indexAction();
    }

    public function testIndexActionWithNotAllowedSection()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $backendConfigMock = $this->getMock('Mage_Backend_Model_Config', array(), array(), '', false, false);
        $backendConfigMock->expects($this->never())->method('save');
        $this->_configMock->expects($this->never())->method('reinit');
        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_sessionMock->expects($this->never())->method('addSuccess');
        $this->_sessionMock->expects($this->once())->method('addException');

        $this->_configFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($backendConfigMock));

        $this->_controller->indexAction();
    }

    public function testIndexActionSaveState()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(false));
        $data = array('some_key' => 'some_value');

        $userMock = $this->getMock('Mage_User_Model_User', array(), array(), '', false, false);
        $userMock->expects($this->once())->method('saveExtra')->with(array('configState' => $data));
        $this->_authMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

        $this->_requestMock->expects($this->any())
            ->method('getPost')->with('config_state')->will($this->returnValue($data));
        $this->_controller->indexAction();
    }

    public function testIndexActionGetGroupForSave()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $groups = array('some.key' => 'some.val');
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $requestPostMap = array(
            array('groups', null, $groups),
            array('config_state', null, 'test_config_state'),
        );

        $fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $files = require_once($fixturePath . 'files_array.php');

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $this->_requestMock->expects($this->once())
            ->method('getFiles')
            ->with('groups')
            ->will($this->returnValue($files));

        $groupToSave = require_once($fixturePath . 'expected_array.php');

        $params = array('section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groupToSave
        );
        $backendConfigMock = $this->getMock('Mage_Backend_Model_Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects($this->once())->method('create')->with(array('data' => $params))
            ->will($this->returnValue($backendConfigMock));
        $backendConfigMock->expects($this->once())->method('save');

        $this->_controller->indexAction();
    }

    public function testIndexActionSaveAdvanced()
    {
        $this->_sectionMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $requestParamMap = array(
            array('section', null, 'advanced'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store'),
        );

        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Mage_Backend_Model_Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($backendConfigMock));
        $backendConfigMock->expects($this->once())->method('save');

        $this->_appMock->expects($this->once())
            ->method('cleanCache')
            ->with(array('layout', Mage_Core_Model_Layout_Merge::LAYOUT_GENERAL_CACHE_TAG));
        $this->_controller->indexAction();
    }
}
