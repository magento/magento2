<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->cacheInstanceMock = $this->createMock(Config::class);

        $menuFactoryMock = $this->createPartialMock(MenuFactory::class, ['create']);

        $this->configReaderMock = $this->createMock(Reader::class);

        $this->logger = $this->createMock(LoggerInterface::class);

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
                'appState' => $appState
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMenuWithCachedObjectReturnsUnserializedObject(): void
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

    /**
     * @return void
     */
    public function testGetMenuWithNotCachedObjectBuildsObject(): void
    {
        $this->cacheInstanceMock
            ->method('load')
            ->with(\Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT)
            ->willReturn(false);

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
     * @param string $thrownException
     *
     * @return void
     */
    #[DataProvider('getMenuExceptionLoggedDataProvider')]
    public function testGetMenuExceptionLogged(string $thrownException): void
    {
        $this->expectException(LocalizedException::class);
        $this->logger->expects($this->once())->method('critical');
        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->willThrowException(
            new $thrownException('test error message')
        );

        $this->model->getMenu();
    }

    /**
     * @return void
     */
    public function testGetMenuExceptionMessageContainsOriginalError(): void
    {
        $originalMessage = 'Missing required param parent';
        $this->menuBuilderMock->expects($this->once())
            ->method('getResult')
            ->willThrowException(new \BadMethodCallException($originalMessage));

        try {
            $this->model->getMenu();
            $this->fail('Expected LocalizedException was not thrown');
        } catch (LocalizedException $e) {
            $this->assertStringContainsString($originalMessage, $e->getMessage());
            $this->assertInstanceOf(\BadMethodCallException::class, $e->getPrevious());
        }
    }

    /**
     * @return void
     */
    public function testGetMenuGenericExceptionIsWrappedInLocalizedException(): void
    {
        $this->logger->expects($this->once())->method('critical');
        $this->menuBuilderMock->expects($this->exactly(1))
            ->method('getResult')
            ->willThrowException(new \Exception('unexpected error'));

        $this->expectException(LocalizedException::class);
        $this->model->getMenu();
    }

    /**
     * @return array
     */
    public static function getMenuExceptionLoggedDataProvider(): array
    {
        return [
            'InvalidArgumentException' => ['InvalidArgumentException'],
            'BadMethodCallException' => ['BadMethodCallException'],
            'OutOfRangeException' => ['OutOfRangeException']
        ];
    }

    /**
     * @return void
     */
    public function testGetMenuGenericExceptionIsNotLogged(): void
    {
        $this->logger->expects($this->once())->method('critical');

        $this->menuBuilderMock->expects(
            $this->exactly(1)
        )->method(
            'getResult'
        )->willThrowException(
            new \Exception()
        );
        try {
            $this->model->getMenu();
        } catch (LocalizedException $e) {
            return;
        }
        $this->fail('LocalizedException was not thrown for generic \\Exception');
    }
}
