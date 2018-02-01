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
use Magento\InventoryApi\Api\IsProductInStockInterface;

class IsProductInStockOnDefaultStockTest extends TestCase
{
    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @var DefaultStockProvider
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProvider::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param string $sku
     * @param bool $expectedValue
     *
     * @return void
     * @dataProvider executeWithDifferentQtyDataProvider
     */
    public function testExecuteWithDifferentQty(string $sku, bool $expectedValue)
    {
        $isInStock = $this->isProductInStock->execute($sku, $this->defaultStockProvider->getId());
        self::assertEquals($expectedValue, $isInStock);
    }

    /**
     * @return array
     */
    public function executeWithDifferentQtyDataProvider(): array
    {
        return [
            ['SKU-1', true],
            ['SKU-2', true],
            ['SKU-3', false],
        ];
    }
}
