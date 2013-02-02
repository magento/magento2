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
class Mage_DesignEditor_Model_StateTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Name of layout classes that will be used as main layout
     */
    const LAYOUT_DESIGN_CLASS_NAME     = 'Mage_DesignEditor_Model_Layout';
    const LAYOUT_NAVIGATION_CLASS_NAME = 'Mage_Core_Model_Layout';
    /**#@-*/

    /**#@+
     * Url model classes that will be used instead of Mage_Core_Model_Url in different vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Mage_DesignEditor_Model_Url_NavigationMode';
    const URL_MODEL_DESIGN_MODE_CLASS_NAME     = 'Mage_DesignEditor_Model_Url_DesignMode';
    /**#@-*/

    /**#@+
     * Layout update resource models
     */
    const LAYOUT_UPDATE_RESOURCE_MODEL_CORE_CLASS_NAME = 'Mage_Core_Model_Resource_Layout_Update';
    const LAYOUT_UPDATE_RESOURCE_MODEL_VDE_CLASS_NAME  = 'Mage_DesignEditor_Model_Resource_Layout_Update';
    /**#@-*/

    /**#@+
     * Import behaviors
     */
    const MODE_DESIGN     = 'design';
    const MODE_NAVIGATION = 'navigation';
    /**#@-*/

    /*
     * Test area code
     */
    const AREA_CODE = 'front';

    /**
     * Test theme id
     */
    const THEME_ID = 1;

    /**
     * @var Mage_DesignEditor_Model_State
     */
    protected $_model;

    /**
     * @var Mage_Backend_Model_Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendSession;

    /**
     * @var Mage_Core_Model_Layout_Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutFactory;

    /**
     * @var Mage_DesignEditor_Model_Url_Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelFactory;

    /**
     * @var Mage_Core_Model_Cache|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheManager;

    /**
     * @var Mage_DesignEditor_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataHelper;

    /**
     * @var Magento_ObjectManager_Zend|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Design_Package|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designPackage;

    /**
     * @var Mage_Core_Model_App|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    /**
     * @var array
     */
    protected $_cacheTypes = array('type1', 'type2');

    public function setUp()
    {
        $this->_backendSession = $this->getMock('Mage_Backend_Model_Session', array('setData', 'getData', 'unsetData'),
            array(), '', false
        );
        $this->_layoutFactory = $this->getMock('Mage_Core_Model_Layout_Factory', array('createLayout'),
            array(), '', false
        );
        $this->_urlModelFactory = $this->getMock('Mage_DesignEditor_Model_Url_Factory', array('replaceClassName'),
            array(), '', false
        );
        $this->_cacheManager = $this->getMock('Mage_Core_Model_Cache', array('canUse', 'banUse'),
            array(), '', false
        );
        $this->_dataHelper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getDisabledCacheTypes'),
            array(), '', false
        );
        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend', array('addAlias'),
            array(), '', false
        );
        $this->_designPackage = $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false);
        $this->_application = $this->getMock('Mage_Core_Model_App', array('getStore'),
            array(), '', false
        );

        $this->_model = new Mage_DesignEditor_Model_State(
            $this->_backendSession,
            $this->_layoutFactory,
            $this->_urlModelFactory,
            $this->_cacheManager,
            $this->_dataHelper,
            $this->_objectManager,
            $this->_designPackage,
            $this->_application
        );
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_backendSession, '_backendSession', $this->_model);
        $this->assertAttributeEquals($this->_layoutFactory, '_layoutFactory', $this->_model);
        $this->assertAttributeEquals($this->_urlModelFactory, '_urlModelFactory', $this->_model);
        $this->assertAttributeEquals($this->_cacheManager, '_cacheManager', $this->_model);
        $this->assertAttributeEquals($this->_dataHelper, '_dataHelper', $this->_model);
        $this->assertAttributeEquals($this->_objectManager, '_objectManager', $this->_model);
    }

    protected function _setAdditionalExpectations()
    {
        $this->_dataHelper->expects($this->once())
            ->method('getDisabledCacheTypes')
            ->will($this->returnValue($this->_cacheTypes));

        $this->_cacheManager->expects($this->at(0))
            ->method('canUse')
            ->with('type1')
            ->will($this->returnValue(true));
        $this->_cacheManager->expects($this->at(1))
            ->method('banUse')
            ->with('type1')
            ->will($this->returnSelf());

        $this->_cacheManager->expects($this->at(2))
            ->method('canUse')
            ->with('type2')
            ->will($this->returnValue(true));
        $this->_cacheManager->expects($this->at(3))
            ->method('banUse')
            ->with('type2')
            ->will($this->returnSelf());
    }

    public function testUpdateDesignMode()
    {
        $this->_setAdditionalExpectations();
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array('getParam'),
            array(), '', false);

        $controller = $this->getMock('Mage_Adminhtml_Controller_Action', array('getFullActionName'), array(),
            '', false);

        $request->expects($this->once())
            ->method('getParam')
            ->with('handle', '')
            ->will($this->returnValue('default'));

        $this->_backendSession->expects($this->once())
            ->method('setData')
            ->with('vde_current_mode', Mage_DesignEditor_Model_State::MODE_DESIGN);
        $this->_backendSession->expects($this->once())
            ->method('getData')
            ->with('theme_id')
            ->will($this->returnValue(self::THEME_ID));

        $this->_urlModelFactory->expects($this->once())
            ->method('replaceClassName')
            ->with(self::URL_MODEL_DESIGN_MODE_CLASS_NAME);

        $this->_layoutFactory->expects($this->once())
            ->method('createLayout')
            ->with(array('area' => self::AREA_CODE), self::LAYOUT_DESIGN_CLASS_NAME);

        $this->_objectManager->expects($this->once())
            ->method('addAlias')
            ->with(self::LAYOUT_UPDATE_RESOURCE_MODEL_CORE_CLASS_NAME,
            self::LAYOUT_UPDATE_RESOURCE_MODEL_VDE_CLASS_NAME);

        $store = $this->getMock('Mage_Core_Model_Store', array('setConfig'), array(), '', false);
        $store->expects($this->once())
            ->method('setConfig')
            ->with(Mage_Core_Model_Design_Package::XML_PATH_THEME_ID, self::THEME_ID);

        $this->_application->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->_model->update(self::AREA_CODE, $request, $controller);
    }

    public function testReset()
    {
        $this->_backendSession->expects($this->any())
            ->method('unsetData')
            ->with($this->logicalOr(
                Mage_DesignEditor_Model_State::CURRENT_HANDLE_SESSION_KEY,
                Mage_DesignEditor_Model_State::CURRENT_MODE_SESSION_KEY,
                Mage_DesignEditor_Model_State::CURRENT_URL_SESSION_KEY
            ))
            ->will($this->returnValue($this->_backendSession));

        $this->_model->reset();
    }

    public function testUpdateNavigationMode()
    {
        $this->_setAdditionalExpectations();
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array('getParam', 'isAjax', 'getPathInfo'),
            array(), '', false);

        $controller = $this->getMock('Mage_Adminhtml_Controller_Action', array('getFullActionName'), array(),
            '', false);

        $request->expects($this->once())
            ->method('getParam')
            ->with('handle', '')
            ->will($this->returnValue(''));

        $request->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(false));

        $controller->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('index'));

        $this->_backendSession->expects($this->at(0))
            ->method('setData')
            ->with('vde_current_handle', 'index');

        $request->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue('/'));

        $this->_backendSession->expects($this->at(1))
            ->method('setData')
            ->with('vde_current_url', '/');

        $this->_backendSession->expects($this->at(2))
            ->method('setData')
            ->with('vde_current_mode', Mage_DesignEditor_Model_State::MODE_NAVIGATION);

        $this->_urlModelFactory->expects($this->once())
            ->method('replaceClassName')
            ->with(self::URL_MODEL_NAVIGATION_MODE_CLASS_NAME);

        $this->_layoutFactory->expects($this->once())
            ->method('createLayout')
            ->with(array('area' => self::AREA_CODE), self::LAYOUT_NAVIGATION_CLASS_NAME);

        $this->_objectManager->expects($this->once())
            ->method('addAlias')
            ->with(self::LAYOUT_UPDATE_RESOURCE_MODEL_CORE_CLASS_NAME,
            self::LAYOUT_UPDATE_RESOURCE_MODEL_VDE_CLASS_NAME);

        $this->_model->update(self::AREA_CODE, $request, $controller);
    }
}
