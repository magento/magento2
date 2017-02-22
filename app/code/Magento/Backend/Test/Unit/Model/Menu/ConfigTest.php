<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu;

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
            [],
            [],
            '',
            false
        );

        $this->_directorMock = $this->getMock(
            'Magento\Backend\Model\Menu\AbstractDirector',
            [],
            [],
            '',
            false
        );

        $this->_menuFactoryMock = $this->getMock(
            'Magento\Backend\Model\MenuFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_configReaderMock = $this->getMock(
            'Magento\Backend\Model\Menu\Config\Reader',
            [],
            [],
            '',
            false
        );

        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false,
            false
        );

        $this->_logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->_menuMock = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Psr\Log\LoggerInterface')]
        );

        $this->_menuBuilderMock = $this->getMock('Magento\Backend\Model\Menu\Builder', [], [], '', false);

        $this->_menuFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_menuMock));

        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_configReaderMock->expects($this->any())->method('read')->will($this->returnValue([]));

        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
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

        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue([]));

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
        return [
            'InvalidArgumentException' => ['InvalidArgumentException'],
            'BadMethodCallException' => ['BadMethodCallException'],
            'OutOfRangeException' => ['OutOfRangeException']
        ];
    }

    public function testGetMenuGenericExceptionIsNotLogged()
    {
        $this->_logger->expects($this->never())->method('critical');

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
