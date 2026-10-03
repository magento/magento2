<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\PageLayout\Config as PageLayoutConfig;
use Magento\Framework\View\PageLayout\ConfigFactory as PageLayoutConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated as PageLayoutFileCollector;
use Magento\Widget\Model\ResourceModel\Layout\Plugin;
use Magento\Widget\Model\ResourceModel\Layout\Update;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private const CURRENT_THEME = 'frontend/Vendor/current';
    private const OTHER_THEME = 'frontend/Vendor/other';

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
                return ['layouts.xml' => $theme->getFullPath()];
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

        $this->plugin = new Plugin($this->updateMock, $pageLayoutConfigFactory, $pageLayoutFileCollector);
    }

    public function testRequestedPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('2columns-left');

        $this->assertSame('<body/>', $this->callPlugin(self::CURRENT_THEME, ['2columns-left'], '2columns-left'));
    }

    public function testInheritedPageLayoutHandleGetsNoDbUpdates(): void
    {
        $this->updateMock->expects($this->never())->method('fetchUpdatesByHandle');

        $this->assertSame('', $this->callPlugin(self::CURRENT_THEME, ['2columns-left'], '1column'));
    }

    public function testInheritedNonPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('customer_account');

        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::CURRENT_THEME, ['customer_account_index'], 'customer_account')
        );
    }

    public function testPageLayoutDeclaredOnlyByAnotherThemeDoesNotSuppressDbUpdates(): void
    {
        $this->assertSame('', $this->callPlugin(self::OTHER_THEME, ['customer_account_index'], 'customer_account'));

        $this->expectDbUpdatesFetchedFor('customer_account');
        $this->assertSame(
            '<body/>',
            $this->callPlugin(self::CURRENT_THEME, ['customer_account_index'], 'customer_account')
        );
        $this->assertSame([self::OTHER_THEME, self::CURRENT_THEME], $this->collectedThemes);
    }

    public function testRequestedNumericPageLayoutHandleGetsDbUpdates(): void
    {
        $this->expectDbUpdatesFetchedFor('123');

        $this->assertSame('<body/>', $this->callPlugin(self::CURRENT_THEME, [123], '123'));
    }

    private function expectDbUpdatesFetchedFor(string $handle): void
    {
        $this->updateMock->expects($this->once())
            ->method('fetchUpdatesByHandle')
            ->with($handle)
            ->willReturn('<body/>');
    }

    private function callPlugin(string $themePath, array $requestedHandles, string $handle): string
    {
        $theme = $this->createStub(ThemeInterface::class);
        $theme->method('getId')->willReturn(array_search($themePath, array_keys(self::PAGE_LAYOUTS_BY_THEME)));
        $theme->method('getFullPath')->willReturn($themePath);
        $merge = $this->createStub(Merge::class);
        $merge->method('getTheme')->willReturn($theme);
        $merge->method('getScope')->willReturn($this->createStub(ScopeInterface::class));
        $merge->method('getHandles')->willReturn($requestedHandles);

        return $this->plugin->aroundGetDbUpdateString(
            $merge,
            function () {
                return '';
            },
            $handle
        );
    }
}
