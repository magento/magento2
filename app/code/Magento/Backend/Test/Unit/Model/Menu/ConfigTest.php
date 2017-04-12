<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheInstanceMock;

    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var \Magento\Backend\Model\Menu|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuMock;

    /**
     * @var \Magento\Backend\Model\Menu\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuBuilderMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    private $model;

    protected function setUp()
    {
        $this->cacheInstanceMock = $this->getMock(
            \Magento\Framework\App\Cache\Type\Config::class,
            [],
            [],
            '',
            false
        );

        $menuFactoryMock = $this->getMock(
            \Magento\Backend\Model\MenuFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->configReaderMock = $this->getMock(
            \Magento\Backend\Model\Menu\Config\Reader::class,
            [],
            [],
            '',
            false
        );

        $this->logger = $this->getMock(\Psr\Log\LoggerInterface::class);

        $this->menuMock = $this->getMock(\Magento\Backend\Model\Menu::class, [], [], '', false);

        $this->menuBuilderMock = $this->getMock(\Magento\Backend\Model\Menu\Builder::class, [], [], '', false);

        $menuFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->menuMock));

        $this->configReaderMock->expects($this->any())->method('read')->will($this->returnValue([]));

        $appState = $this->getMock(\Magento\Framework\App\State::class, ['getAreaCode'], [], '', false);
        $appState->expects(
            $this->any()
        )->method(
            'getAreaCode'
        )->will(
            $this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
        );

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Backend\Model\Menu\Config::class,
            [
                'menuBuilder' => $this->menuBuilderMock,
                'menuFactory' => $menuFactoryMock,
                'configReader' => $this->configReaderMock,
                'configCacheType' => $this->cacheInstanceMock,
                'logger' => $this->logger,
                'appState' => $appState,
            ]
        );
    }

    public function testGetMenuWithCachedObjectReturnsUnserializedObject()
    {
        $this->cacheInstanceMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
        )->will(
            $this->returnValue('menu_cache')
        );

        $this->menuMock->expects($this->once())->method('unserialize')->with('menu_cache');

        $this->assertEquals($this->menuMock, $this->model->getMenu());
    }

    public function testGetMenuWithNotCachedObjectBuidlsObject()
    {
        $this->cacheInstanceMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
        )->will(
            $this->returnValue(false)
        );

        $this->configReaderMock->expects($this->once())->method('read')->will($this->returnValue([]));

        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->returnValue($this->menuMock)
        );

        $this->assertEquals($this->menuMock, $this->model->getMenu());
    }

    /**
     * @param string $expectedException
     *
     * @dataProvider getMenuExceptionLoggedDataProvider
     */
    public function testGetMenuExceptionLogged($expectedException)
    {
        $this->setExpectedException($expectedException);
        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->throwException(new $expectedException())
        );

        $this->model->getMenu();
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
        $this->logger->expects($this->never())->method('critical');

        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->throwException(new \Exception())
        );
        try {
            $this->model->getMenu();
        } catch (\Exception $e) {
            return;
        }
        $this->fail("Generic \Exception was not throwed");
    }
}
