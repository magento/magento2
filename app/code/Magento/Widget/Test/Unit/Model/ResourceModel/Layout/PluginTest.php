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
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface as PageLayoutConfigBuilder;
use Magento\Framework\View\PageLayout\Config as PageLayoutConfig;
use Magento\Widget\Model\ResourceModel\Layout\Plugin;
use Magento\Widget\Model\ResourceModel\Layout\Update;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Update|MockObject
     */
    private $updateMock;

    /**
     * @var Merge|MockObject
     */
    private $mergeMock;

    /**
     * @var Plugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->updateMock = $this->createMock(Update::class);
        $this->mergeMock = $this->createMock(Merge::class);
        $this->mergeMock->method('getTheme')->willReturn($this->createStub(ThemeInterface::class));
        $this->mergeMock->method('getScope')->willReturn($this->createStub(ScopeInterface::class));
        $pageLayoutConfig = $this->createStub(PageLayoutConfig::class);
        $pageLayoutConfig->method('getPageLayouts')->willReturn(
            ['empty' => 'Empty', '1column' => '1 column', '2columns-left' => '2 columns with left bar']
        );
        $pageLayoutConfigBuilder = $this->createStub(PageLayoutConfigBuilder::class);
        $pageLayoutConfigBuilder->method('getPageLayoutsConfig')->willReturn($pageLayoutConfig);

        $this->plugin = new Plugin($this->updateMock, $pageLayoutConfigBuilder);
    }

    public function testRequestedPageLayoutHandleGetsDbUpdates(): void
    {
        $this->mergeMock->expects($this->once())->method('getHandles')->willReturn(['2columns-left']);
        $this->updateMock->expects($this->once())
            ->method('fetchUpdatesByHandle')
            ->with('2columns-left')
            ->willReturn('<body/>');

        $this->assertSame('<body/>', $this->callPlugin('2columns-left'));
    }

    public function testInheritedPageLayoutHandleGetsNoDbUpdates(): void
    {
        $this->mergeMock->expects($this->once())->method('getHandles')->willReturn(['2columns-left']);
        $this->updateMock->expects($this->never())->method('fetchUpdatesByHandle');

        $this->assertSame('', $this->callPlugin('1column'));
    }

    public function testInheritedNonPageLayoutHandleGetsDbUpdates(): void
    {
        $this->mergeMock->expects($this->once())->method('getHandles')->willReturn(['customer_account_index']);
        $this->updateMock->expects($this->once())
            ->method('fetchUpdatesByHandle')
            ->with('customer_account')
            ->willReturn('<body/>');

        $this->assertSame('<body/>', $this->callPlugin('customer_account'));
    }

    private function callPlugin(string $handle): string
    {
        return $this->plugin->aroundGetDbUpdateString(
            $this->mergeMock,
            function () {
                return '';
            },
            $handle
        );
    }
}
