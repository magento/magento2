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
namespace Magento\DesignEditor\Model;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Name of layout classes that will be used as main layout
     */
    const LAYOUT_NAVIGATION_CLASS_NAME = 'Magento\Framework\View\Layout';

    /**
     * Url model classes that will be used instead of \Magento\Framework\UrlInterface in different vde modes
     */
    const URL_MODEL_NAVIGATION_MODE_CLASS_NAME = 'Magento\DesignEditor\Model\Url\NavigationMode';

    /**#@+
     * Layout update resource models
     */
    const LAYOUT_UPDATE_RESOURCE_MODEL_CORE_CLASS_NAME = 'Magento\Core\Model\Resource\Layout\Update';

    const LAYOUT_UPDATE_RESOURCE_MODEL_VDE_CLASS_NAME = 'Magento\DesignEditor\Model\Resource\Layout\Update';

    /**#@-*/

    /**#@+
     * Import behaviors
     */
    const MODE_DESIGN = 'design';

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
     * @var \Magento\DesignEditor\Model\State
     */
    protected $_model;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendSession;

    /**
     * @var AreaEmulator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaEmulator;

    /**
     * @var \Magento\DesignEditor\Model\Url\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheStateMock;

    /**
     * @var \Magento\DesignEditor\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    /**
     * @var array
     */
    protected $_cacheTypeList = array('type1', 'type2');

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_backendSession = $this->getMock(
            'Magento\Backend\Model\Session',
            array('setData', 'getData', 'unsetData'),
            array(),
            '',
            false
        );
        $this->_areaEmulator = $this->getMock(
            'Magento\DesignEditor\Model\AreaEmulator',
            array('emulateLayoutArea'),
            array(),
            '',
            false
        );
        $this->_urlModelFactory = $this->getMock(
            'Magento\DesignEditor\Model\Url\Factory',
            array('replaceClassName'),
            array(),
            '',
            false
        );
        $this->_cacheStateMock = $this->getMockBuilder(
            'Magento\Framework\App\Cache\StateInterface'
        )->disableOriginalConstructor()->getMock();

        $this->_dataHelper = $this->getMock(
            'Magento\DesignEditor\Helper\Data',
            array('getDisabledCacheTypes'),
            array(),
            '',
            false
        );

        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');

        $mutableConfig = $this->getMockForAbstractClass('\Magento\Framework\App\Config\MutableScopeConfigInterface');
        $mutableConfig->expects(
            $this->any()
        )->method(
            'setValue'
        )->with(
            $this->equalTo(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID),
            $this->equalTo(self::THEME_ID),
            $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        )->will(
            $this->returnSelf()
        );

        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_configMock->expects(
            $this->any()
        )->method(
            'setNode'
        )->with(
            $this->equalTo('default/' . \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID),
            $this->equalTo(self::THEME_ID)
        )->will(
            $this->returnSelf()
        );

        $this->_theme = $this->getMock('Magento\Core\Model\Theme', array('getId', '__wakeup'), array(), '', false);
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(self::THEME_ID));

        $this->_themeContext = $this->getMock(
            'Magento\DesignEditor\Model\Theme\Context',
            array('getEditableTheme', 'getVisibleTheme', 'reset', 'setEditableThemeById'),
            array(),
            '',
            false
        );
        $this->_themeContext->expects(
            $this->any()
        )->method(
            'getVisibleTheme'
        )->will(
            $this->returnValue($this->_theme)
        );

        $this->_model = new \Magento\DesignEditor\Model\State(
            $this->_backendSession,
            $this->_areaEmulator,
            $this->_urlModelFactory,
            $this->_cacheStateMock,
            $this->_dataHelper,
            $this->_objectManager,
            $this->_configMock,
            $this->_themeContext,
            $mutableConfig
        );
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_backendSession, '_backendSession', $this->_model);
        $this->assertAttributeEquals($this->_areaEmulator, '_areaEmulator', $this->_model);
        $this->assertAttributeEquals($this->_urlModelFactory, '_urlModelFactory', $this->_model);
        $this->assertAttributeEquals($this->_cacheStateMock, '_cacheState', $this->_model);
        $this->assertAttributeEquals($this->_dataHelper, '_dataHelper', $this->_model);
        $this->assertAttributeEquals($this->_objectManager, '_objectManager', $this->_model);
    }

    protected function _setAdditionalExpectations()
    {
        $this->_dataHelper->expects(
            $this->any()
        )->method(
            'getDisabledCacheTypes'
        )->will(
            $this->returnValue($this->_cacheTypeList)
        );

        $this->_cacheStateMock->expects(
            $this->at(0)
        )->method(
            'isEnabled'
        )->with(
            'type1'
        )->will(
            $this->returnValue(true)
        );
        $this->_cacheStateMock->expects(
            $this->at(1)
        )->method(
            'setEnabled'
        )->with(
            'type1',
            false
        )->will(
            $this->returnSelf()
        );

        $this->_cacheStateMock->expects(
            $this->at(2)
        )->method(
            'isEnabled'
        )->with(
            'type2'
        )->will(
            $this->returnValue(true)
        );
        $this->_cacheStateMock->expects(
            $this->at(3)
        )->method(
            'setEnabled'
        )->with(
            'type2',
            false
        )->will(
            $this->returnSelf()
        );
    }

    public function testReset()
    {
        $this->_backendSession->expects(
            $this->any()
        )->method(
            'unsetData'
        )->with(
            $this->logicalOr(
                \Magento\DesignEditor\Model\State::CURRENT_MODE_SESSION_KEY,
                \Magento\DesignEditor\Model\State::CURRENT_URL_SESSION_KEY
            )
        )->will(
            $this->returnValue($this->_backendSession)
        );
        $this->assertEquals($this->_model, $this->_model->reset());
    }

    public function testUpdateNavigationMode()
    {
        $this->_setAdditionalExpectations();
        $request = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);

        $request->expects($this->once())->method('getPathInfo')->will($this->returnValue('/'));

        $this->_backendSession->expects($this->at(0))->method('setData')->with('vde_current_url', '/');

        $this->_backendSession->expects(
            $this->at(1)
        )->method(
            'setData'
        )->with(
            'vde_current_mode',
            \Magento\DesignEditor\Model\State::MODE_NAVIGATION
        );

        $this->_urlModelFactory->expects(
            $this->once()
        )->method(
            'replaceClassName'
        )->with(
            self::URL_MODEL_NAVIGATION_MODE_CLASS_NAME
        );

        $this->_areaEmulator->expects($this->once())->method('emulateLayoutArea')->with(self::AREA_CODE);
        $controller = $this->getMock('Magento\Backend\Controller\Adminhtml\Action', array(), array(), '', false);

        $this->_model->update(self::AREA_CODE, $request, $controller);
    }
}
