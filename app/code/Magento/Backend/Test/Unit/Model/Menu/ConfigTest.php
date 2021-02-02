<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheInstanceMock;

    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configReaderMock;

    /**
     * @var \Magento\Backend\Model\Menu|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuMock;

    /**
     * @var \Magento\Backend\Model\Menu\Builder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuBuilderMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    private $model;

    protected function setUp(): void
    {
        $this->cacheInstanceMock = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);

        $menuFactoryMock = $this->createPartialMock(\Magento\Backend\Model\MenuFactory::class, ['create']);

        $this->configReaderMock = $this->createMock(\Magento\Backend\Model\Menu\Config\Reader::class);

        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->menuMock = $this->createMock(\Magento\Backend\Model\Menu::class);

        $this->menuBuilderMock = $this->createMock(\Magento\Backend\Model\Menu\Builder::class);

        $menuFactoryMock->expects($this->any())->method('create')->willReturn($this->menuMock);

        $this->configReaderMock->expects($this->any())->method('read')->willReturn([]);

        $appState = $this->createPartialMock(\Magento\Framework\App\State::class, ['getAreaCode']);
        $appState->expects(
            $this->any()
        )->method(
            'getAreaCode'
        )->willReturn(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
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
        )->willReturn(
            'menu_cache'
        );

        $this->menuMock->expects($this->once())->method('unserialize')->with('menu_cache');

        $this->assertEquals($this->menuMock, $this->model->getMenu());
    }

    public function testGetMenuWithNotCachedObjectBuildsObject()
    {
        $this->cacheInstanceMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            $this->equalTo(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
        )->willReturn(
            false
        );

        $this->configReaderMock->expects($this->once())->method('read')->willReturn([]);

        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->willReturn(
            $this->menuMock
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
        $this->expectException($expectedException);
        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->will(
            $this->throwException(new $expectedException())
        );

        $this->model->getMenu();
    }

    /**
     * @return array
     */
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
        $this->fail("Generic \Exception was not thrown");
    }
}
