<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryCatalog\Model\GetSourceItemsBySkuAndSourceCodes;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetSourceItemsBySkuAndSourceCodesTest extends TestCase
{
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecuteSkuAssignedToSources()
    {
        $getSourceItems = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuAndSourceCodes::class);
        $items = $getSourceItems->execute('SKU-1', ['eu-1', 'eu-2']);
        $this->assertEquals(2, count($items));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecuteSkuNotAssignedToSources()
    {
        $getSourceItems = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuAndSourceCodes::class);
        $items = $getSourceItems->execute('SKU-2', ['eu-1']);
        $this->assertEmpty($items);
    }
}
