<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Layout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Layout\LayoutPlugin;
use Magento\PageCache\Model\Spi\PageCacheTagsPreprocessorInterface;
use Magento\PageCache\Test\Unit\Block\Controller\StubBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\PageCache\Model\Layout\LayoutPlugin class.
 */
class LayoutPluginTest extends TestCase
{
    /**
     * @var LayoutPlugin
     */
    private $model;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceModeMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->createPartialMock(Layout::class, ['isCacheable', 'getAllBlocks']);
        $this->responseMock = $this->createMock(Http::class);
        $this->configMock = $this->createMock(Config::class);
        $this->maintenanceModeMock = $this->createMock(MaintenanceMode::class);
        $preprocessor = $this->createMock(PageCacheTagsPreprocessorInterface::class);
        $preprocessor->method('process')->willReturnArgument(0);

        $this->model = (new ObjectManagerHelper($this))->getObject(
            LayoutPlugin::class,
            [
                'response' => $this->responseMock,
                'config' => $this->configMock,
                'maintenanceMode' => $this->maintenanceModeMock,
                'pageCacheTagsPreprocessor' => $preprocessor
            ]
        );
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @param $maintenanceModeIsEnabled
     * @return void
     * @dataProvider afterGenerateElementsDataProvider
     */
    public function testAfterGenerateElements($cacheState, $layoutIsCacheable, $maintenanceModeIsEnabled): void
    {
        $maxAge = 180;

        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn($layoutIsCacheable);
        $this->configMock->expects($this->any())->method('isEnabled')->willReturn($cacheState);
        $this->maintenanceModeMock->expects($this->any())->method('isOn')
            ->willReturn($maintenanceModeIsEnabled);

        if ($layoutIsCacheable && $cacheState && !$maintenanceModeIsEnabled) {
            $this->configMock->expects($this->once())->method('getTtl')->willReturn($maxAge);
            $this->responseMock->expects($this->once())->method('setPublicHeaders')->with($maxAge);
        } else {
            $this->responseMock->expects($this->never())->method('setPublicHeaders');
        }

        $this->assertEmpty($this->model->afterGenerateElements($this->layoutMock));
    }

    /**
     * @return array
     */
    public static function afterGenerateElementsDataProvider(): array
    {
        return [
            'Full_cache state is true, Layout is cache-able' => [true, true, false],
            'Full_cache state is true, Layout is not cache-able' => [true, false, false],
            'Full_cache state is false, Layout is not cache-able' => [false, false, false],
            'Full_cache state is false, Layout is cache-able' => [false, true, false],
            'Full_cache state is true, Layout is cache-able, Maintenance mode is enabled' => [true, true, true],
        ];
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @param $expectedTags
     * @param $configCacheType
     * @param $ttl
     * @return void
     * @dataProvider afterGetOutputDataProvider
     */
    public function testAfterGetOutput($cacheState, $layoutIsCacheable, $expectedTags, $configCacheType, $ttl): void
    {
        $html = 'html';
        $this->configMock->expects($this->any())->method('isEnabled')->willReturn($cacheState);
        $blockStub = $this->createPartialMock(
            StubBlock::class,
            ['getIdentities']
        );
        $blockStub->setTtl($ttl);
        $blockStub->expects($this->any())->method('getIdentities')->willReturn(['identity1', 'identity2']);
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn($layoutIsCacheable);
        $this->layoutMock->expects($this->any())->method('getAllBlocks')->willReturn([$blockStub]);

        $this->configMock->expects($this->any())->method('getType')->willReturn($configCacheType);

        if ($layoutIsCacheable && $cacheState) {
            $this->responseMock->expects($this->once())->method('setHeader')->with('X-Magento-Tags', $expectedTags);
        } else {
            $this->responseMock->expects($this->never())->method('setHeader');
        }
        $output = $this->model->afterGetOutput($this->layoutMock, $html);
        $this->assertSame($output, $html);
    }

    /**
     * @return array
     */
    public static function afterGetOutputDataProvider(): array
    {
        $tags = 'identity1,identity2';
        return [
            'Cacheable layout, Full_cache state is true' => [true, true, $tags, null, 0],
            'Non-cacheable layout' => [true, false, null, null, 0],
            'Cacheable layout with Varnish' => [true, true, $tags, Config::VARNISH, 0],
            'Cacheable layout with Varnish, Full_cache state is false' => [
                false,
                true,
                $tags,
                Config::VARNISH,
                0,
            ],
            'Cacheable layout with Varnish and esi' => [
                true,
                true,
                null,
                Config::VARNISH,
                100,
            ],
            'Cacheable layout with Builtin' => [true, true, $tags, Config::BUILT_IN, 0],
            'Cacheable layout with Builtin, Full_cache state is false' => [
                false,
                true,
                $tags,
                Config::BUILT_IN,
                0,
            ],
            'Cacheable layout with Builtin and esi' => [
                true,
                false,
                $tags,
                Config::BUILT_IN,
                100,
            ],
        ];
    }
}
