<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\InventoryCatalog\Model\DefaultStockProvider;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductInStockTestOnDefaultStock extends TestCase
{
    /**
     * @var GetIsSalable
     */
    private $getIsSalable;

    /**
     * @var DefaultStockProvider
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getIsSalable = Bootstrap::getObjectManager()->get(GetIsSalable::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProvider::class);

        parent::setUp();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param string $sku
     * @param bool $expectedIsSalable
     *
     * @return void
     * @dataProvider isSalableWithDifferentQtyDataProvider
     */
    public function testIsSalableWithDifferentQty(string $sku, bool $expectedIsSalable)
    {
        $isSalable = $this->getIsSalable->execute($sku, $this->defaultStockProvider->getId());
        self::assertEquals($expectedIsSalable, $isSalable);
    }

    /**
     * @return array
     */
    public function isSalableWithDifferentQtyDataProvider(): array
    {
        return [
            ['SKU-1', true],
            ['SKU-2', true],
            ['SKU-3', false],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param string $sku
     * @param bool $expectedIsSalable
     *
     * @return void
     * @dataProvider isSalableWithManageStockFalseDataProvider
     */
    public function testIsSalableWithManageStockFalse(string $sku, bool $expectedIsSalable)
    {
        $isSalable = $this->getIsSalable->execute($sku, $this->defaultStockProvider->getId());
        self::assertEquals($expectedIsSalable, $isSalable);
    }

    /**
     * @return array
     */
    public function isSalableWithManageStockFalseDataProvider(): array
    {
        return [
            ['SKU-1', true],
            ['SKU-2', true],
            ['SKU-3', true],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     *
     * @param string $sku
     * @param bool $expectedIsSalable
     *
     * @return void
     * @dataProvider isSalableWithMinQtyDataProvider
     */
    public function testIsSalableWithMinQty(string $sku, bool $expectedIsSalable)
    {
        $isSalable = $this->getIsSalable->execute($sku, $this->defaultStockProvider->getId());
        self::assertEquals($expectedIsSalable, $isSalable);
    }

    /**
     * @return array
     */
    public function isSalableWithMinQtyDataProvider(): array
    {
        return [
            ['SKU-1', true],
            ['SKU-2', false],
            ['SKU-3', false],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param string $sku
     * @param bool $expectedIsSalable
     *
     * @return void
     * @dataProvider isSalableWithManageStockFalseAndMinQty
     */
    public function testIsSalableWithManageStockFalseAndMinQty(string $sku, bool $expectedIsSalable)
    {
        $isSalable = $this->getIsSalable->execute($sku, $this->defaultStockProvider->getId());
        self::assertEquals($expectedIsSalable, $isSalable);
    }

    /**
     * @return array
     */
    public function isSalableWithManageStockFalseAndMinQty(): array
    {
        return [
            ['SKU-1', true],
            ['SKU-2', true],
            ['SKU-3', true],
        ];
    }
}
