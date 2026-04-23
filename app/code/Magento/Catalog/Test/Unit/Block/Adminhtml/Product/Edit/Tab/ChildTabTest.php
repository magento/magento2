<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab
 */
class ChildTabTest extends TestCase
{
    use MockCreationTrait;

    /**
     * System under test
     *
     * @var ChildTab
     */
    private ChildTab $block;

    /**
     * Mock for tab interface
     *
     * @var TabInterface&MockObject
     */
    private TabInterface|MockObject $tabMock;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->tabMock = $this->createPartialMockWithReflection(
            TabInterface::class,
            ['getTabLabel', 'getTabTitle', 'canShowTab', 'isHidden', 'toHtml', 'getTabId', 'getData']
        );
        $this->block = $helper->getObject(ChildTab::class);
    }

    /**
     * Test that setTab correctly sets the tab property and returns $this for chaining
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::setTab
     * @return void
     */
    public function testSetTabSetsTabAndReturnsThis(): void
    {
        $result = $this->block->setTab($this->tabMock);

        $this->assertSame($this->block, $result);
    }

    /**
     * Test that getTitle returns the correct tab title from the tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::getTitle
     * @return void
     */
    public function testGetTitleReturnsTabTitle(): void
    {
        $expectedTitle = 'Product Tab Title';

        $this->tabMock->expects($this->once())
            ->method('getTabTitle')
            ->willReturn($expectedTitle);

        $this->block->setTab($this->tabMock);
        $result = $this->block->getTitle();

        $this->assertSame($expectedTitle, $result);
    }

    /**
     * Test that getContent returns the HTML content from the tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::getContent
     * @return void
     */
    public function testGetContentReturnsTabHtml(): void
    {
        $expectedHtml = '<div>Tab Content</div>';

        $this->tabMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expectedHtml);

        $this->block->setTab($this->tabMock);
        $result = $this->block->getContent();

        $this->assertSame($expectedHtml, $result);
    }

    /**
     * Test that getTabId returns the correct tab ID from the tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::getTabId
     * @return void
     */
    public function testGetTabIdReturnsTabId(): void
    {
        $expectedTabId = 'product_tab_id';

        $this->tabMock->expects($this->once())
            ->method('getTabId')
            ->willReturn($expectedTabId);

        $this->block->setTab($this->tabMock);
        $result = $this->block->getTabId();

        $this->assertSame($expectedTabId, $result);
    }

    /**
     * Test that isTabOpened returns boolean for various truthy/falsy values
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::isTabOpened
     * @param mixed $openedValue
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('openedDataProvider')]
    public function testIsTabOpenedHandlesDifferentDataTypes(mixed $openedValue, bool $expectedResult): void
    {
        $this->tabMock->expects($this->once())
            ->method('getData')
            ->with('opened')
            ->willReturn($openedValue);

        $this->block->setTab($this->tabMock);
        $result = $this->block->isTabOpened();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Data provider for opened data type scenarios
     *
     * @return array
     */
    public static function openedDataProvider(): array
    {
        return [
            'boolean_true' => [true, true],
            'boolean_false' => [false, false],
            'null_value' => [null, false],
            'integer_1' => [1, true],
            'integer_0' => [0, false],
            'string_true' => ['true', true],
            'string_false' => ['false', true],
            'string_empty' => ['', false],
            'string_1' => ['1', true],
            'string_0' => ['0', false],
        ];
    }

    /**
     * Test that method chaining works correctly with setTab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::setTab
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab::getTitle
     * @return void
     */
    public function testMethodChainingWorksWithSetTab(): void
    {
        $expectedTitle = 'Chained Tab Title';

        $this->tabMock->expects($this->once())
            ->method('getTabTitle')
            ->willReturn($expectedTitle);

        $result = $this->block->setTab($this->tabMock)->getTitle();

        $this->assertSame($expectedTitle, $result);
    }
}
