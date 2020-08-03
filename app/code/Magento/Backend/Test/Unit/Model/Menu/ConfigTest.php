<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Config\Reader;
use Magento\Backend\Model\MenuFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $cacheInstanceMock;

    /**
     * @var Reader|MockObject
     */
    private $configReaderMock;

    /**
     * @var Menu|MockObject
     */
    private $menuMock;

    /**
     * @var Builder|MockObject
     */
    private $menuBuilderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    private $model;

    protected function setUp(): void
    {
        $this->cacheInstanceMock = $this->createMock(Config::class);

        $menuFactoryMock = $this->createPartialMock(MenuFactory::class, ['create']);

        $this->configReaderMock = $this->createMock(Reader::class);

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->menuMock = $this->createMock(Menu::class);

        $this->menuBuilderMock = $this->createMock(Builder::class);

        $menuFactoryMock->expects($this->any())->method('create')->willReturn($this->menuMock);

        $this->configReaderMock->expects($this->any())->method('read')->willReturn([]);

        $appState = $this->createPartialMock(State::class, ['getAreaCode']);
        $appState->expects(
            $this->any()
        )->method(
            'getAreaCode'
        )->willReturn(
            FrontNameResolver::AREA_CODE
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
            \Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT
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
            \Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT
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
        )->willThrowException(
            new $expectedException()
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
        )->willThrowException(
            new \Exception()
        );
        try {
            $this->model->getMenu();
        } catch (\Exception $e) {
            return;
        }
        $this->fail("Generic \Exception was not thrown");
    }
}
