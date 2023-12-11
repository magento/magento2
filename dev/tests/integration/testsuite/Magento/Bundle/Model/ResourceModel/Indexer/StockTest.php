<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;

class StockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $processor;

    protected function setUp(): void
    {
        $this->processor = Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/product_in_category.php
     */
    public function testReindexAll()
    {
        $this->processor->reindexAll();

        $categoryFactory = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productCollection->joinField(
            'qty',
            'cataloginventory_stock_status',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->assertCount(3, $productCollection);

        $expectedResult = [
            'Simple Product' => 22,
            'Custom Design Simple Product' => 24,
            'Bundle Product' => 0
        ];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals($expectedResult[$product->getName()], $product->getQty());
        }
    }

    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple1', 'stock_item' => ['use_config_manage_stock' => 0, 'use_config_backorders' => 0]],
            's1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple2', 'stock_item' => ['use_config_manage_stock' => 0, 'use_config_backorders' => 0]],
            's2'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple3', 'stock_item' => ['use_config_manage_stock' => 0, 'use_config_backorders' => 0]],
            's3'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple4', 'stock_item' => ['use_config_manage_stock' => 0, 'use_config_backorders' => 0]],
            's4'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$s1.sku$', 'qty' => 2, 'can_change_quantity' => 0],
            'link1'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$s2.sku$', 'qty' => 2, 'can_change_quantity' => 0],
            'link2'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$s3.sku$', 'qty' => 2, 'can_change_quantity' => 1],
            'link3'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$s4.sku$', 'qty' => 2, 'can_change_quantity' => 0],
            'link4'
        ),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link3$']], 'opt2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link4$'], 'required' => false], 'opt3'),
        DataFixture(BundleProductFixture::class, ['sku' => 'bundle1', '_options' => ['$opt1$', '$opt2$', '$opt3$']]),
    ]
    /**
     * @dataProvider reindexRowDataProvider
     * @param array $stockItems
     * @param bool $expectedStockStatus
     * @return void
     */
    public function testReindexRow(array $stockItems, bool $expectedStockStatus): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        foreach ($stockItems as $sku => $stockItem) {
            $child = $productRepository->get($sku);
            $child->setStockData($stockItem);
            $productRepository->save($child);
        }
        $bundle = $productRepository->get('bundle1');
        $this->processor->reindexRow($bundle->getId());

        $stockStatusResource = Bootstrap::getObjectManager()->get(StockStatusResource::class);
        $stockStatus = $stockStatusResource->getProductsStockStatuses($bundle->getId(), 0);
        self::assertEquals($expectedStockStatus, (bool) $stockStatus[$bundle->getId()]);
    }

    public function reindexRowDataProvider(): array
    {
        return [
            [
                [
                    'simple1' => ['manage_stock' => true, 'backorders' => false, 'qty' => 2],
                    'simple2' => ['manage_stock' => true, 'backorders' => false, 'qty' => 2],
                    'simple3' => ['manage_stock' => true, 'backorders' => false, 'qty' => 2],
                    'simple4' => ['manage_stock' => true, 'backorders' => false, 'qty' => 2],
                ],
                true,
            ],
            [
                [
                    'simple1' => ['manage_stock' => true, 'backorders' => false, 'qty' => 1],
                    'simple3' => ['manage_stock' => true, 'backorders' => false, 'qty' => 1],
                    'simple4' => ['manage_stock' => true, 'backorders' => false, 'qty' => 1],
                ],
                true,
            ],
            [
                [
                    'simple1' => ['manage_stock' => true, 'backorders' => false, 'qty' => 1],
                    'simple2' => ['manage_stock' => true, 'backorders' => false, 'qty' => 1],
                ],
                false,
            ],
            [
                [
                    'simple3' => ['manage_stock' => true, 'backorders' => false, 'qty' => 0],
                ],
                false,
            ],
            [
                [
                    'simple4' => ['manage_stock' => true, 'backorders' => false, 'qty' => 0],
                ],
                true,
            ],
            [
                [
                    'simple1' => ['manage_stock' => false, 'backorders' => false, 'qty' => 0],
                    'simple2' => ['manage_stock' => false, 'backorders' => false, 'qty' => 0],
                    'simple3' => ['manage_stock' => false, 'backorders' => false, 'qty' => 0],
                ],
                true,
            ],
            [
                [
                    'simple1' => ['manage_stock' => true, 'backorders' => true, 'qty' => 0],
                    'simple2' => ['manage_stock' => true, 'backorders' => true, 'qty' => 0],
                    'simple3' => ['manage_stock' => true, 'backorders' => true, 'qty' => 0],
                ],
                true,
            ],
        ];
    }
}
