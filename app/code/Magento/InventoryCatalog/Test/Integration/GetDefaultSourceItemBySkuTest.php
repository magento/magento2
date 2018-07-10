<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryCatalog\Model\GetDefaultSourceItemBySku;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetDefaultSourceItemBySkuTest extends TestCase
{
    /**
     * @var GetDefaultSourceItemBySku
     */
    private $getDefaultSourceItemBySku;

    protected function setUp()
    {
        parent::setUp();
        $this->getDefaultSourceItemBySku = Bootstrap::getObjectManager()->get(GetDefaultSourceItemBySku::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testExecuteOnDefaultSource()
    {
        self::assertNotNull(
            $this->getDefaultSourceItemBySku->execute('SKU-1'),
            'Unable to find default source_item for a product assigned to Default source only'
        );
        self::assertNotNull(
            $this->getDefaultSourceItemBySku->execute('SKU-2'),
            'Unable to find default source_item for a product assigned to Default source and others'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecuteOnNonDefaultSource()
    {
        self::assertNull(
            $this->getDefaultSourceItemBySku->execute('SKU-1'),
            'Returned a wrong default source_item for a product assigned elsewhere'
        );
    }
}
