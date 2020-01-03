<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class checks configurable product displaying on category view page
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ConfigurableViewOnCategoryPageTest extends TestCase
{
    /** @var ObjectManagerInterface  */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var ListProduct $listingBlock */
    private $listingBlock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->listingBlock = $this->layout->createBlock(ListProduct::class);
        $this->listingBlock->setCategoryId(333);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_out_of_stock_children.php
     *
     * @return void
     */
    public function testOutOfStockProductWithEnabledConfigView(): void
    {
        $collection = $this->listingBlock->getLoadedProductCollection();
        $this->assertEquals(1, $collection->getSize());
        $this->assertCount(1, $collection->getItems());
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_out_of_stock_children.php
     *
     * @return void
     */
    public function testOutOfStockProductWithDisabledConfigView(): void
    {
        $collection = $this->listingBlock->getLoadedProductCollection();
        $this->assertEquals(0, $collection->getSize());
        $this->assertCount(0, $collection->getItems());
    }
}
