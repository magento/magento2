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
namespace Magento\Backend\Model\Menu;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_cacheInstanceMock = $this->getMock(
            'Magento\Framework\App\Cache\Type\Config',
            array(),
            array(),
            '',
            false
        );

        $this->_directorMock = $this->getMock(
            'Magento\Backend\Model\Menu\AbstractDirector',
            array(),
            array(),
            '',
            false
        );

        $this->_menuFactoryMock = $this->getMock(
            'Magento\Backend\Model\MenuFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->_configReaderMock = $this->getMock(
            'Magento\Backend\Model\Menu\Config\Reader',
            array(),
            array(),
            '',
            false
        );

        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array(),
            array(),
            '',
            false,
            false
        );

        $this->_logger = $this->getMock(
            'Magento\Framework\Logger',
            array('addStoreLog', 'log', 'logException'),
            array(),
            '',
            false
        );

        $this->_menuMock = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Magento\Framework\Logger', [], [], '', false)]
        );

        $this->_menuBuilderMock = $this->getMock('Magento\Backend\Model\Menu\Builder', array(), array(), '', false);

        $this->_menuFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_menuMock));

        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_configReaderMock->expects($this->any())->method('read')->will($this->returnValue(array()));

        $appState = $this->getMock('Magento\Framework\App\State', array('getAreaCode'), array(), '', false);
        $appState->expects(
            $this->any()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        );

        $this->_model = new \Magento\Backend\Model\Menu\Config(
            $this->_menuBuilderMock,
            $this->_directorMock,
            $this->_menuFactoryMock,
            $this->_configReaderMock,
            $this->_cacheInstanceMock,
            $this->_eventManagerMock,
            $this->_logger,
            $scopeConfig,
            $appState
        );
    }

    public function testGetMenuWithCachedObjectReturnsUnserializedObject()
    {
        $this->_cacheInstanceMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
        )->will(
            $this->returnValue('menu_cache')
        );

        $this->_menuMock->expects($this->once())->method('unserialize')->with('menu_cache');

        $this->assertEquals($this->_menuMock, $this->_model->getMenu());
    }

    public function testGetMenuWithNotCachedObjectBuidlsObject()
    {
        $this->_cacheInstanceMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
        )->will(
            $this->returnValue(false)
        );

        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue(array()));

        $this->_menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->returnValue($this->_menuMock)
        );

        $this->assertEquals($this->_menuMock, $this->_model->getMenu());
    }

    /**
     * @param string $expectedException
     *
     * @dataProvider getMenuExceptionLoggedDataProvider
     */
    public function testGetMenuExceptionLogged($expectedException)
    {
        $this->setExpectedException($expectedException);
        $this->_menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->throwException(new $expectedException())
        );

        $this->_model->getMenu();
    }

    public function getMenuExceptionLoggedDataProvider()
    {
        return array(
            'InvalidArgumentException' => array('InvalidArgumentException'),
            'BadMethodCallException' => array('BadMethodCallException'),
            'OutOfRangeException' => array('OutOfRangeException')
        );
    }

    public function testGetMenuGenericExceptionIsNotLogged()
    {
        $this->_logger->expects($this->never())->method('logException');

        $this->_menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->throwException(new \Exception())
        );
        try {
            $this->_model->getMenu();
        } catch (\Exception $e) {
            return;
        }
        $this->fail("Generic \Exception was not throwed");
    }
}
