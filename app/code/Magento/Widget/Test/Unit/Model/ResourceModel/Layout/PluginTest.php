<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Layout\Reader\Context as ReaderContext;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Page\Layout\Reader as PageLayoutReader;
use Magento\Framework\View\PageLayout\Config as PageLayoutConfig;
use Magento\Framework\View\PageLayout\ConfigFactory as PageLayoutConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated as PageLayoutFileCollector;
use Magento\Widget\Model\ResourceModel\Layout\PageLayoutReaderPlugin;
use Magento\Widget\Model\ResourceModel\Layout\Plugin;
use Magento\Widget\Model\ResourceModel\Layout\Update;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private const CURRENT_THEME = 'frontend/Vendor/current';
    private const OTHER_THEME = 'frontend/Vendor/other';
    private const THEME_WITHOUT_LAYOUTS_XML = 'frontend/Vendor/bare';

    private const PAGE_LAYOUTS_BY_THEME = [
        self::CURRENT_THEME => [
            'empty' => 'Empty',
            '1column' => '1 column',
            '2columns-left' => '2 columns with left bar',
            '123' => 'Numeric page layout',
        ],
        self::OTHER_THEME => ['customer_account' => 'Page layout named like a layout handle'],
    ];

    /**
     * @var Update|MockObject
     */
    private $updateMock;

    /**
     * @var string[]
     */
    private $collectedThemes = [];

    /**
     * @var PageLayoutReaderPlugin
     */
    private $pageLayoutReaderPlugin;

    /**
     * @var Plugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->updateMock = $this->createMock(Update::class);
        $pageLayoutFileCollector = $this->createStub(PageLayoutFileCollector::class);
        $pageLayoutFileCollector->method('getFilesContent')->willReturnCallback(
            function (ThemeInterface $theme) {
                $this->collectedThemes[] = $theme->getFullPath();
                return isset(self::PAGE_LAYOUTS_BY_THEME[$theme->getFullPath()])
                    ? ['layouts.xml' => $theme->getFullPath()]
                    : [];
            }
        );
        $pageLayoutConfigFactory = $this->createStub(PageLayoutConfigFactory::class);
        $pageLayoutConfigFactory->method('create')->willReturnCallback(
            function (array $arguments) {
                $config = $this->createStub(PageLayoutConfig::class);
                $config->method('getPageLayouts')->willReturn(
                    self::PAGE_LAYOUTS_BY_THEME[$arguments['configFiles']['layouts.xml']]
                );
                return $config;
            }
        );

        $this->pageLayoutReaderPlugin = new PageLayoutReaderPlugin();
        $this->plugin = new Plugin(
            $this->updateMock,
            $pageLayoutConfigFactory,
            $pageLayoutFileCollector,
            $this->pageLayoutReaderPlugin
        );
    }

    public function testRequestedPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('2columns-left');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::CURRENT_THEME, true, ['2columns-left'], '2columns-left')
        );
    }

    public function testInheritedPageLayoutHandleGetsNoDbUpdatesDuringPageLayoutRead(): void
    {
        $this->updateMock->expects($this->never())->method('fetchUpdatesByHandle');

        $this->assertSame('', $this->callPlugin(self::CURRENT_THEME, true, ['2columns-left'], '1column'));
    }

    public function testInheritedNonPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('customer_account');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::CURRENT_THEME, false, ['customer_account_index'], 'customer_account')
        );
    }

    public function testInheritedPageLayoutOfMergeThemeGetsNoDbUpdates(): void
    {
        $this->updateMock->expects($this->never())->method('fetchUpdatesByHandle');

        $this->assertSame('', $this->callPlugin(self::OTHER_THEME, true, ['2columns-left'], 'customer_account'));
    }

    public function testPageLayoutDeclaredOnlyByAnotherThemeDoesNotSuppressDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('customer_account');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::CURRENT_THEME, true, ['2columns-left'], 'customer_account')
        );
        $this->assertSame([self::CURRENT_THEME], $this->collectedThemes);
    }

    public function testPageLayoutNamedLikeLayoutHandleDoesNotSuppressDbUpdatesOutsidePageLayoutRead(): void
    {
        $this->expectDbUpdatesFetchedFor('customer_account');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::OTHER_THEME, false, ['customer_account_index'], 'customer_account')
        );
    }

    public function testMergeOutsidePageLayoutReadDoesNotReadLayoutsXml(): void
    {
        $this->expectDbUpdatesFetchedFor('1column');

        $this->assertSame('<body/>', $this->callPlugin(self::CURRENT_THEME, false, ['default'], '1column'));
        $this->assertSame([], $this->collectedThemes);
    }

    public function testThemeWithoutLayoutsXmlSuppressesNothing(): void
    {
        $this->expectDbUpdatesFetchedFor('1column');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::THEME_WITHOUT_LAYOUTS_XML, true, ['2columns-left'], '1column')
        );
    }

    public function testRequestedNumericPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('123');

        $this->assertSame('<body/>', $this->callPlugin(self::CURRENT_THEME, true, [123], '123'));
    }

    public function testPageLayoutReadDoesNotLoadMergeFileLayoutUpdates(): void
    {
        $this->updateMock->expects($this->never())->method('fetchUpdatesByHandle');
        $merge = $this->createMock(Merge::class);
        $merge->method('getTheme')->willReturn($this->createTheme(self::CURRENT_THEME));
        $merge->method('getHandles')->willReturn(['2columns-left']);
        $merge->expects($this->never())->method('getFileLayoutUpdatesXml');
        $merge->expects($this->never())->method('load');

        $this->assertSame('', $this->invokeDuringPageLayoutRead($merge, '1column'));
    }

    private function expectDbUpdatesFetchedFor(string $handle): void
    {
        $this->updateMock->expects($this->once())
            ->method('fetchUpdatesByHandle')
            ->with($handle)
            ->willReturn('<body/>');
    }

    private function callPlugin(
        string $themePath,
        bool $duringPageLayoutRead,
        array $requestedHandles,
        string $handle
    ): string {
        $merge = $this->createStub(Merge::class);
        $merge->method('getTheme')->willReturn($this->createTheme($themePath));
        $merge->method('getScope')->willReturn($this->createStub(ScopeInterface::class));
        $merge->method('getHandles')->willReturn($requestedHandles);

        return $duringPageLayoutRead
            ? $this->invokeDuringPageLayoutRead($merge, $handle)
            : $this->invokePlugin($merge, $handle);
    }

    private function invokeDuringPageLayoutRead(Merge $merge, string $handle): string
    {
        $result = null;
        $this->pageLayoutReaderPlugin->aroundRead(
            $this->createStub(PageLayoutReader::class),
            function () use ($merge, $handle, &$result) {
                $result = $this->invokePlugin($merge, $handle);
            },
            $this->createStub(ReaderContext::class),
            '2columns-left'
        );
        return $result;
    }

    private function createTheme(string $themePath): ThemeInterface
    {
        $theme = $this->createStub(ThemeInterface::class);
        $theme->method('getId')->willReturn(crc32($themePath));
        $theme->method('getFullPath')->willReturn($themePath);
        return $theme;
    }

    private function invokePlugin(Merge $merge, string $handle): string
    {
        return $this->plugin->aroundGetDbUpdateString(
            $merge,
            function () {
                return '';
            },
            $handle
        );
    }
}
