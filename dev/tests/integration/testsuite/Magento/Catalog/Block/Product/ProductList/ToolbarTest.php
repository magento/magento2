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
    /** @var string */
    private const XPATH_TEMPLATE_FOR_NOT_VISIBLE_ICON_CASES = '//div[contains(@class, "modes")]/*[@data-value="%s"]';

    /** @var string */
    private const ACTIVE_MODE_XPATH_TEMPLATE =
        '//div[contains(@class, "modes")]/strong[@data-value="%s" and contains(@class, "active")]';

    /** @var string */
    private const NOT_ACTIVE_MODE_XPATH_TEMPLATE = '//div[contains(@class, "modes")]/a[@data-value="%s"]';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Toolbar */
    private $toolbarBlock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->toolbarBlock = $this->layout->createBlock(Toolbar::class);
    }

    /**
     * @return void
     */
    public function testGetPagerHtml(): void
    {
        $this->toolbarBlock->setNameInLayout('block');
        /** @var $childBlock Text */
        $childBlock = $this->layout->addBlock(
            Text::class,
            'product_list_toolbar_pager',
            'block'
        );
        $expectedHtml = '<b>Any text there</b>';
        $this->assertNotEquals($expectedHtml, $this->toolbarBlock->getPagerHtml());
        $childBlock->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $this->toolbarBlock->getPagerHtml());
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/list_mode grid
     * @return void
     */
    public function testToHtmlGridOnly(): void
    {
        $htmlOutput = $this->getModeSwitcherHtml();
        $this->assertNotEmpty($htmlOutput);
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                sprintf(self::XPATH_TEMPLATE_FOR_NOT_VISIBLE_ICON_CASES, 'grid'),
                $htmlOutput
            )
        );
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                sprintf(self::XPATH_TEMPLATE_FOR_NOT_VISIBLE_ICON_CASES, 'list'),
                $htmlOutput
            )
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/list_mode list
     * @return void
     */
    public function testToHtmlListOnly(): void
    {
        $htmlOutput = $this->getModeSwitcherHtml();
        $this->assertNotEmpty($htmlOutput);
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                sprintf(self::XPATH_TEMPLATE_FOR_NOT_VISIBLE_ICON_CASES, 'grid'),
                $htmlOutput
            )
        );
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                sprintf(self::XPATH_TEMPLATE_FOR_NOT_VISIBLE_ICON_CASES, 'list'),
                $htmlOutput
            )
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/list_mode grid-list
     * @return void
     */
    public function testToHtmlGridList(): void
    {
        $htmlOutput = $this->getModeSwitcherHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(self::ACTIVE_MODE_XPATH_TEMPLATE, 'grid'),
                $htmlOutput
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(self::NOT_ACTIVE_MODE_XPATH_TEMPLATE, 'list'),
                $htmlOutput
            )
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/list_mode list-grid
     * @return void
     */
    public function testToHtmlListGrid(): void
    {
        $htmlOutput = $this->getModeSwitcherHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(self::ACTIVE_MODE_XPATH_TEMPLATE, 'list'),
                $htmlOutput
            )
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(self::NOT_ACTIVE_MODE_XPATH_TEMPLATE, 'grid'),
                $htmlOutput
            )
        );
    }

    /**
     * Mode switcher html
     *
     * @return string
     */
    private function getModeSwitcherHtml(): string
    {
        $this->toolbarBlock->setTemplate('Magento_Catalog::product/list/toolbar/viewmode.phtml');

        return $this->toolbarBlock->toHtml();
    }
}
