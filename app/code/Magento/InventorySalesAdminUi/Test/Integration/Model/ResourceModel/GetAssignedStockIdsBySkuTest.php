<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Test\Integration\Model\ResourceModel;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku;

class GetAssignedStockIdsBySkuTest extends TestCase
{
    /**
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    protected function setUp()
    {
        parent::setUp();

        $this->getAssignedStockIdsBySku = Bootstrap::getObjectManager()->get(GetAssignedStockIdsBySku::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     *
     * @magentoDbIsolation disabled
     */
    public function testExecute()
    {
        $productSku = 'SKU-1';
        $expectedStockIds = [10, 30];

        self::assertEquals($expectedStockIds, $this->getAssignedStockIdsBySku->execute($productSku));
    }
}
