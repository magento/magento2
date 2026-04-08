<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\View\Tabs;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Tabs class
 *
 * @covers \Magento\Catalog\Block\Product\View\Tabs
 */
class TabsTest extends TestCase
{
    /**
     * @var Tabs
     */
    private Tabs $block;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var ObjectManager
     */
    private ObjectManager $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);
        $this->layoutMock = $this->createMock(Layout::class);
        /** @var Tabs $block */
        $block = $this->helper->getObject(Tabs::class, ['layout' => $this->layoutMock]);
        $this->block = $block;
    }

    /**
     * Test addTab method
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTab(): void
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->with('template')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->with('block')->willReturn($tabBlock);

        $this->block->addTab('alias', 'title', 'block', 'template', 'header');

        $expectedTabs = [['alias' => 'alias', 'title' => 'title', 'header' => 'header']];
        $this->assertSame($expectedTabs, $this->block->getTabs());
    }

    /**
     * Test addTab method without header parameter
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithoutHeader(): void
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->with('template')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->with('block')->willReturn($tabBlock);

        $this->block->addTab('alias', 'title', 'block', 'template');

        $expectedTabs = [['alias' => 'alias', 'title' => 'title', 'header' => null]];
        $this->assertSame($expectedTabs, $this->block->getTabs());
    }

    /**
     * Test addTab with invalid parameters
     *
     * @param string|null $title
     * @param string|null $block
     * @param string|null $template
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    #[DataProvider('invalidParametersDataProvider')]
    public function testAddTabWithInvalidParameters(?string $title, ?string $block, ?string $template): void
    {
        $this->layoutMock->expects($this->never())->method('createBlock');

        $this->block->addTab('alias', $title, $block, $template, 'header');

        $this->assertEmpty($this->block->getTabs());
    }

    /**
     * @return array
     */
    public static function invalidParametersDataProvider(): array
    {
        return [
            'empty_title' => ['', 'block', 'template'],
            'null_title' => [null, 'block', 'template'],
            'empty_block' => ['title', '', 'template'],
            'null_block' => ['title', null, 'template'],
            'empty_template' => ['title', 'block', ''],
            'null_template' => ['title', 'block', null],
            'zero_values' => ['0', '0', '0']
        ];
    }

    /**
     * Test adding multiple tabs
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddMultipleTabs(): void
    {
        $tabBlock1 = $this->createMock(Template::class);
        $tabBlock1->expects($this->once())->method('setTemplate')->with('template1')->willReturnSelf();

        $tabBlock2 = $this->createMock(Template::class);
        $tabBlock2->expects($this->once())->method('setTemplate')->with('template2')->willReturnSelf();

        $this->layoutMock->expects($this->exactly(2))
            ->method('createBlock')
            ->willReturnOnConsecutiveCalls($tabBlock1, $tabBlock2);

        $this->block->addTab('alias1', 'title1', 'block1', 'template1', 'header1');
        $this->block->addTab('alias2', 'title2', 'block2', 'template2', 'header2');

        $expectedTabs = [
            ['alias' => 'alias1', 'title' => 'title1', 'header' => 'header1'],
            ['alias' => 'alias2', 'title' => 'title2', 'header' => 'header2']
        ];
        $this->assertSame($expectedTabs, $this->block->getTabs());
    }

    /**
     * Test getTabs returns empty array when no tabs added
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testGetTabsWhenEmpty(): void
    {
        $this->assertEmpty($this->block->getTabs());
        $this->assertIsArray($this->block->getTabs());
    }

    /**
     * Test addTab with null layout - should cause error when trying to call methods on null
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @return void
     */
    public function testAddTabWithNullLayoutCausesError(): void
    {
        $this->expectException(\TypeError::class);
        
        // Create block with null layout - getLayout() will return null
        /** @var Tabs $block */
        $block = $this->helper->getObject(Tabs::class, ['layout' => null]);

        // This should throw TypeError when trying to call createBlock on null
        $block->addTab('alias', 'title', 'block', 'template');
    }

    /**
     * Test addTab when createBlock returns null
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @return void
     */
    public function testAddTabWhenCreateBlockReturnsNull(): void
    {
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with('block')
            ->willReturn(null);

        $this->expectException(\Error::class);
        $this->block->addTab('alias', 'title', 'block', 'template');
    }

    /**
     * Test addTab with whitespace-only values - these are actually valid
     *
     * @param string $title
     * @param string $block
     * @param string $template
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @return void
     */
    #[DataProvider('whitespaceParametersDataProvider')]
    public function testAddTabWithWhitespaceOnlyValues(string $title, string $block, string $template): void
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->any())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab('alias', $title, $block, $template);

        $tabs = $this->block->getTabs();
        // Whitespace-only strings are still truthy in PHP, so tabs are added
        $this->assertCount(1, $tabs);
    }

    /**
     * Data provider for whitespace parameters
     *
     * @return array
     */
    public static function whitespaceParametersDataProvider(): array
    {
        return [
            'whitespace_title' => ['   ', 'block', 'template'],
            'whitespace_block' => ['title', '   ', 'template'],
            'whitespace_template' => ['title', 'block', '   '],
            'tab_title' => ["\t", 'block', 'template'],
            'newline_title' => ["\n", 'block', 'template']
        ];
    }

    /**
     * Test addTab with very long strings
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithVeryLongStrings(): void
    {
        $longAlias = str_repeat('a', 1000);
        $longTitle = str_repeat('Title ', 200);
        $longHeader = str_repeat('Header ', 200);

        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab($longAlias, $longTitle, 'block', 'template', $longHeader);

        $tabs = $this->block->getTabs();
        $this->assertCount(1, $tabs);
        $this->assertSame($longAlias, $tabs[0]['alias']);
        $this->assertSame($longTitle, $tabs[0]['title']);
        $this->assertSame($longHeader, $tabs[0]['header']);
    }

    /**
     * Test addTab with special characters
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithSpecialCharacters(): void
    {
        $alias = 'test-alias_123';
        $title = 'Title with <special> & "characters"';
        $header = 'Header with \'quotes\' & symbols!@#$%';

        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab($alias, $title, 'block', 'template', $header);

        $tabs = $this->block->getTabs();
        $this->assertCount(1, $tabs);
        $this->assertSame($alias, $tabs[0]['alias']);
        $this->assertSame($title, $tabs[0]['title']);
        $this->assertSame($header, $tabs[0]['header']);
    }

    /**
     * Test addTab with unicode characters
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithUnicodeCharacters(): void
    {
        $title = 'Título 中文 العربية Ελληνικά';
        $header = 'Заголовок עברית 日本語';

        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab('unicode', $title, 'block', 'template', $header);

        $tabs = $this->block->getTabs();
        $this->assertCount(1, $tabs);
        $this->assertSame($title, $tabs[0]['title']);
        $this->assertSame($header, $tabs[0]['header']);
    }

    /**
     * Test addTab with numeric string values
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithNumericStrings(): void
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab('123', '456', '789', '101112', '131415');

        $tabs = $this->block->getTabs();
        $this->assertCount(1, $tabs);
        $this->assertSame('123', $tabs[0]['alias']);
        $this->assertSame('456', $tabs[0]['title']);
    }

    /**
     * Test adding tab with same alias multiple times
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::addTab
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testAddTabWithDuplicateAlias(): void
    {
        $tabBlock1 = $this->createMock(Template::class);
        $tabBlock1->expects($this->once())->method('setTemplate')->willReturnSelf();

        $tabBlock2 = $this->createMock(Template::class);
        $tabBlock2->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->exactly(2))
            ->method('createBlock')
            ->willReturnOnConsecutiveCalls($tabBlock1, $tabBlock2);

        $this->block->addTab('same-alias', 'title1', 'block1', 'template1', 'header1');
        $this->block->addTab('same-alias', 'title2', 'block2', 'template2', 'header2');

        $tabs = $this->block->getTabs();
        $this->assertCount(2, $tabs);
    }

    /**
     * Test getTabs persistence
     *
     * @covers \Magento\Catalog\Block\Product\View\Tabs::getTabs
     * @return void
     */
    public function testGetTabsPersistence(): void
    {
        $tabBlock = $this->createMock(Template::class);
        $tabBlock->expects($this->once())->method('setTemplate')->willReturnSelf();

        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($tabBlock);

        $this->block->addTab('alias', 'title', 'block', 'template');

        $tabs1 = $this->block->getTabs();
        $tabs2 = $this->block->getTabs();

        $this->assertSame($tabs1, $tabs2);
    }
}
