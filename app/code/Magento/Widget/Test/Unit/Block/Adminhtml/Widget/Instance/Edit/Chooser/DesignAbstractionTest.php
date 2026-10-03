<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Backend\Block\Context;
use Magento\Framework\App\Config;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Manager;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\PageLayout\Config as PageLayoutConfig;
use Magento\Framework\View\PageLayout\ConfigFactory as PageLayoutConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated as PageLayoutFileCollector;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Theme\Model\Theme;
use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class DesignAbstractionTest extends TestCase
{
    /**
     * @var Collection&Stub
     */
    private $themeCollectionMock;

    /**
     * @var PageLayoutConfigFactory|MockObject
     */
    private $pageLayoutConfigFactoryMock;

    /**
     * @var PageLayoutFileCollector|MockObject
     */
    private $pageLayoutFileCollectorMock;

    /**
     * @var DesignAbstraction
     */
    private $block;

    protected function setUp(): void
    {
        $scopeConfigMock = $this->createStub(Config::class);
        $scopeConfigMock->method('getValue')->willReturn(false);
        $escaperMock = $this->createStub(Escaper::class);
        $escaperMock->method('escapeHtml')->willReturnCallback(fn ($value) => (string)$value);
        $escaperMock->method('escapeHtmlAttr')->willReturnCallback(fn ($value) => (string)$value);
        $contextMock = $this->createStub(Context::class);
        $contextMock->method('getEventManager')->willReturn($this->createStub(Manager::class));
        $contextMock->method('getScopeConfig')->willReturn($scopeConfigMock);
        $contextMock->method('getEscaper')->willReturn($escaperMock);

        $this->themeCollectionMock = $this->createStub(Collection::class);
        $themeCollectionFactoryMock = $this->createStub(CollectionFactory::class);
        $themeCollectionFactoryMock->method('create')->willReturn($this->themeCollectionMock);

        $layoutProcessorMock = $this->createStub(ProcessorInterface::class);
        $layoutProcessorMock->method('isPageLayoutDesignAbstraction')->willReturnCallback(
            fn (array $abstraction) => $abstraction['design_abstraction'] === 'page_layout'
        );
        $layoutProcessorFactoryMock = $this->createStub(ProcessorFactory::class);
        $layoutProcessorFactoryMock->method('create')->willReturn($layoutProcessorMock);

        $appStateMock = $this->createStub(State::class);
        $appStateMock->method('emulateAreaCode')->willReturn(
            [
                'customer_account' => [
                    'name' => 'customer_account',
                    'label' => 'Customer My Account (All Pages)',
                    'design_abstraction' => 'custom',
                ],
            ]
        );

        $this->pageLayoutConfigFactoryMock = $this->createPartialMock(PageLayoutConfigFactory::class, ['create']);
        $this->pageLayoutFileCollectorMock = $this->createMock(PageLayoutFileCollector::class);

        $this->block = new DesignAbstraction(
            $contextMock,
            $layoutProcessorFactoryMock,
            $themeCollectionFactoryMock,
            $appStateMock,
            [],
            $this->pageLayoutConfigFactoryMock,
            $this->pageLayoutFileCollectorMock
        );
    }

    public function testPageLayoutsOfThemeAreListedInPageLayoutsGroup(): void
    {
        $themeMock = $this->createStub(Theme::class);
        $this->themeCollectionMock->method('getItemById')->willReturnCallback(
            fn ($themeId) => $themeId === 3 ? $themeMock : null
        );
        $this->pageLayoutFileCollectorMock->expects($this->once())
            ->method('getFilesContent')
            ->with($themeMock, 'layouts.xml')
            ->willReturn(['layouts.xml' => '<page_layouts/>']);
        $pageLayoutConfigMock = $this->createStub(PageLayoutConfig::class);
        $pageLayoutConfigMock->method('getPageLayouts')->willReturn(
            ['2columns-left' => '2 columns with left bar', '1column' => '1 column']
        );
        $this->pageLayoutConfigFactoryMock->expects($this->once())
            ->method('create')
            ->with(['configFiles' => ['layouts.xml' => '<page_layouts/>']])
            ->willReturn($pageLayoutConfigMock);

        $this->block->setTheme(3);
        $this->block->toHtml();
        $options = $this->block->getOptions();

        $this->assertSame('Page Layouts', (string)$options[2]['label']);
        $this->assertSame(
            [
                ['value' => '1column', 'label' => '1 column'],
                ['value' => '2columns-left', 'label' => '2 columns with left bar'],
            ],
            $options[2]['value']
        );
        $this->assertSame(
            [['value' => 'customer_account', 'label' => 'Customer My Account (All Pages)']],
            $options[1]['value']
        );
    }

    public function testNoPageLayoutsWithoutTheme(): void
    {
        $this->themeCollectionMock->method('getItemById')->willReturn(null);
        $this->pageLayoutFileCollectorMock->expects($this->never())->method('getFilesContent');
        $this->pageLayoutConfigFactoryMock->expects($this->never())->method('create');

        $this->block->toHtml();
        $options = $this->block->getOptions();

        $this->assertSame('Page Layouts', (string)$options[2]['label']);
        $this->assertSame([], $options[2]['value']);
    }
}
