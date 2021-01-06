<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Xpath;

/**
 * Checks product list toolbar.
 *
 * @see \Magento\Catalog\Block\Product\ProductList\Toolbar
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ToolbarTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    public function testGetPagerHtml()
    {
        /** @var $block Toolbar */
        $block = $this->layout->createBlock(Toolbar::class, 'block');
        /** @var $childBlock Text */
        $childBlock = $this->layout->addBlock(
            Text::class,
            'product_list_toolbar_pager',
            'block'
        );
        $expectedHtml = '<b>Any text there</b>';
        $this->assertNotEquals($expectedHtml, $block->getPagerHtml());
        $childBlock->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getPagerHtml());
    }

    /**
     * @magentoConfigFixture default_store catalog/frontend/list_mode grid
     */
    public function testToHtmlGridOnly(): void
    {
        $block = $this->layout->createBlock(Toolbar::class, 'block');
        $block->setTemplate('Magento_Catalog::product/list/toolbar/viewmode.phtml');
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/*[@data-value="grid"]',
                $block->toHtml()
            )
        );
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/*[@data-value="list"]',
                $block->toHtml()
            )
        );
    }

    /**
     * @magentoConfigFixture default_store catalog/frontend/list_mode list
     */
    public function testToHtmlListOnly(): void
    {
        $block = $this->layout->createBlock(Toolbar::class, 'block');
        $block->setTemplate('Magento_Catalog::product/list/toolbar/viewmode.phtml');
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/*[@data-value="grid"]',
                $block->toHtml()
            )
        );
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/*[@data-value="list"]',
                $block->toHtml()
            )
        );
    }

    /**
     * @magentoConfigFixture default_store catalog/frontend/list_mode grid-list
     */
    public function testToHtmlGridList(): void
    {
        $block = $this->layout->createBlock(Toolbar::class, 'block');
        $block->setTemplate('Magento_Catalog::product/list/toolbar/viewmode.phtml');
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/strong[@data-value="grid" and contains(@class, "active")]',
                $block->toHtml()
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/a[@data-value="list"]',
                $block->toHtml()
            )
        );
    }

    /**
     * @magentoConfigFixture default_store catalog/frontend/list_mode list-grid
     */
    public function testToHtmlListGrid(): void
    {
        $block = $this->layout->createBlock(Toolbar::class, 'block');
        $block->setTemplate('Magento_Catalog::product/list/toolbar/viewmode.phtml');
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/strong[@data-value="list" and contains(@class, "active")]',
                $block->toHtml()
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//div[contains(@class, "modes")]/a[@data-value="grid"]',
                $block->toHtml()
            )
        );
    }
}
