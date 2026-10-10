<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Layout;

use Laminas\Http\Header\HeaderInterface;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\Response\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Layout\LayoutPlugin;
use Magento\PageCache\Model\Spi\PageCacheTagsPreprocessorInterface;
use Magento\PageCache\Test\Unit\Block\Controller\StubBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @var Http|MockObject
     */
    private $responseMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var Config|MockObject
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
     */
    #[DataProvider('afterGenerateElementsDataProvider')]
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
     */
    #[DataProvider('afterGetOutputDataProvider')]
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

        // When FPC is off for a cacheable layout, revoke may inspect Cache-Control.
        $this->responseMock->method('getHeader')->willReturn(false);

        if ($layoutIsCacheable && $cacheState) {
            $this->responseMock->expects($this->once())->method('setHeader')->with('X-Magento-Tags', $expectedTags);
            $this->responseMock->expects($this->never())->method('setNoCacheHeaders');
        } else {
            $this->responseMock->expects($this->never())->method('setHeader');
            $this->responseMock->expects($this->never())->method('setNoCacheHeaders');
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

    /**
     * Issue #40281: public headers set while FPC was on must be revoked when FPC is off at getOutput.
     */
    public function testAfterGetOutputRevokesPublicHeadersWhenFullPageCacheDisabledMidRequest(): void
    {
        $html = 'html';
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);

        $cacheControlHeader = $this->createMock(HeaderInterface::class);
        $cacheControlHeader->method('getFieldValue')
            ->willReturn('public, max-age=86400, s-maxage=86400');

        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($cacheControlHeader);
        $this->responseMock->expects($this->once())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->never())->method('setHeader');

        $this->assertSame($html, $this->model->afterGetOutput($this->layoutMock, $html));
    }

    /**
     * Do not force no-cache when response was never marked public.
     */
    public function testAfterGetOutputDoesNotRevokeWhenNoPublicHeadersPresent(): void
    {
        $html = 'html';
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);

        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn(false);
        $this->responseMock->expects($this->never())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->never())->method('setHeader');

        $this->assertSame($html, $this->model->afterGetOutput($this->layoutMock, $html));
    }

    /**
     * Private/no-store Cache-Control must not be rewritten when FPC is disabled.
     */
    public function testAfterGetOutputDoesNotRevokeNonPublicCacheControl(): void
    {
        $html = 'html';
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);

        $cacheControlHeader = $this->createMock(HeaderInterface::class);
        $cacheControlHeader->method('getFieldValue')
            ->willReturn('no-store, no-cache, must-revalidate, max-age=0');

        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($cacheControlHeader);
        $this->responseMock->expects($this->never())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->never())->method('setHeader');

        $this->assertSame($html, $this->model->afterGetOutput($this->layoutMock, $html));
    }

    /**
     * Align with Kernel::process(): bare "public" without s-maxage is not treated as FPC-storeable.
     */
    public function testAfterGetOutputDoesNotRevokePublicWithoutSMaxAge(): void
    {
        $html = 'html';
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);

        $cacheControlHeader = $this->createMock(HeaderInterface::class);
        $cacheControlHeader->method('getFieldValue')->willReturn('public, max-age=86400');

        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($cacheControlHeader);
        $this->responseMock->expects($this->never())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->never())->method('setHeader');

        $this->assertSame($html, $this->model->afterGetOutput($this->layoutMock, $html));
    }

    /**
     * Full race sequence: public headers while FPC is on, then revoke when FPC is off at getOutput.
     *
     * @see https://github.com/magento/magento2/issues/40281
     */
    public function testAfterGenerateElementsThenDisableFpcThenAfterGetOutputRevokesInOneSequence(): void
    {
        $html = 'html';
        $ttl = 86400;

        $this->layoutMock->method('isCacheable')->willReturn(true);
        $this->maintenanceModeMock->method('isOn')->willReturn(false);
        $this->configMock->method('getTtl')->willReturn($ttl);
        $this->configMock->method('isEnabled')->willReturnOnConsecutiveCalls(true, false);

        $this->responseMock->expects($this->once())->method('setPublicHeaders')->with($ttl);

        $cacheControlHeader = $this->createMock(HeaderInterface::class);
        $cacheControlHeader->method('getFieldValue')
            ->willReturn('public, max-age=86400, s-maxage=86400');
        $this->responseMock->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($cacheControlHeader);
        $this->responseMock->expects($this->once())->method('setNoCacheHeaders');
        $this->responseMock->expects($this->never())->method('setHeader');

        $this->model->afterGenerateElements($this->layoutMock);
        $this->assertSame($html, $this->model->afterGetOutput($this->layoutMock, $html));
    }
}
