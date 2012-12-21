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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_DesignEditor_Model_StateTest extends PHPUnit_Framework_TestCase
{
    /*
     * Test area code
     */
    const AREA_CODE = 'front';

    /**
     * @var Mage_DesignEditor_Model_State
     */
    protected $_model;

    /**
     * @var Mage_Backend_Model_Session
     */
    protected $_backendSession;

    /**
     * @var Mage_Core_Model_Layout_Factory
     */
    protected $_layoutFactory;

    /**
     * @var Mage_DesignEditor_Model_Url_Factory
     */
    protected $_urlModelFactory;

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cacheManager;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_dataHelper;

    /**
     * @var array
     */
    protected $_cacheTypes = array('type1', 'type2');

    public function setUp()
    {
        $this->_backendSession = $this->getMock('Mage_Backend_Model_Session', array('setData'),
            array(), '', false
        );
        $this->_layoutFactory = $this->getMock('Mage_Core_Model_Layout_Factory', array('createLayout'),
            array(), '', false
        );
        $this->_urlModelFactory = $this->getMock('Mage_DesignEditor_Model_Url_Factory', array('replaceClassName'),
            array(), '', false
        );
        $this->_cacheManager = $this->getMock('Mage_Core_Model_Cache', array('banUse', 'cleanType'),
            array(), '', false
        );
        $this->_dataHelper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getDisabledCacheTypes'),
            array(), '', false
        );
        $this->_model = new Mage_DesignEditor_Model_State(
            $this->_backendSession,
            $this->_layoutFactory,
            $this->_urlModelFactory,
            $this->_cacheManager,
            $this->_dataHelper
        );
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_backendSession, '_backendSession', $this->_model);
        $this->assertAttributeEquals($this->_layoutFactory, '_layoutFactory', $this->_model);
        $this->assertAttributeEquals($this->_urlModelFactory, '_urlModelFactory', $this->_model);
        $this->assertAttributeEquals($this->_cacheManager, '_cacheManager', $this->_model);
        $this->assertAttributeEquals($this->_dataHelper, '_dataHelper', $this->_model);
    }

    protected function _setAdditionalExpectations()
    {
        $this->_dataHelper->expects($this->once())
            ->method('getDisabledCacheTypes')
            ->will($this->returnValue($this->_cacheTypes));

        $this->_cacheManager->expects($this->at(0))
            ->method('banUse')
            ->with('type1')
            ->will($this->returnSelf());
        $this->_cacheManager->expects($this->at(1))
            ->method('cleanType')
            ->with('type1')
            ->will($this->returnSelf());
        $this->_cacheManager->expects($this->at(2))
            ->method('banUse')
            ->with('type2')
            ->will($this->returnSelf());
        $this->_cacheManager->expects($this->at(3))
            ->method('cleanType')
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

        $this->_urlModelFactory->expects($this->once())
            ->method('replaceClassName')
            ->with('Mage_DesignEditor_Model_Url_DesignMode');

        $this->_layoutFactory->expects($this->once())
            ->method('createLayout')
            ->with(array('area' => self::AREA_CODE), 'Mage_DesignEditor_Model_Layout');

        $this->_model->update(self::AREA_CODE, $request, $controller);
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
            ->with('Mage_DesignEditor_Model_Url_NavigationMode');

        $this->_layoutFactory->expects($this->once())
            ->method('createLayout')
            ->with(array('area' => self::AREA_CODE), 'Mage_Core_Model_Layout');

        $this->_model->update(self::AREA_CODE, $request, $controller);
    }
}
