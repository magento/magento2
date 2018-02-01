<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductInStockManageConfigTest extends TestCase
{
    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param int $stockId
     * @param array $expectedResults
     * @return void
     *
     * @dataProvider executeWithManageStockFalseDataProvider
     */
    public function testExecuteWithManageStockFalse(int $stockId, array $expectedResults)
    {
        foreach (['SKU-1', 'SKU-2', 'SKU-3'] as $key => $sku) {
            $isInStock = $this->isProductInStock->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isInStock);
        }
    }

    /**
     * @return array
     */
    public function executeWithManageStockFalseDataProvider(): array
    {
        return [
            ['10', [true, false, true]],
            ['20', [false, true, false]],
            ['30', [true, true, true]],
        ];
    }
}
