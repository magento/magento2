<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\DataObject;

/**
 * Test for product min/max allowed quantity for shopping cart
 */
class MinMaxSaleQtyTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test min/max allowed item quantity for shopping cart
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 100.00, 'stock_item' => ['qty' => 1000]], 'product')
    ]
    public function testMinMaxSaleQty(): void
    {
        $product = $this->fixtures->get('product');
        $sku = $product->getSku();
        $query = $this->getQuery($sku);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            1,
            $responseDataObject->getData('products/items/0/min_sale_qty')
        );
        self::assertGreaterThanOrEqual(
            $responseDataObject->getData('products/items/0/min_sale_qty'),
            $responseDataObject->getData('products/items/0/max_sale_qty')
        );
    }

    /**
     * query to return product with product.min_sale_qty and product.max_sale_qty
     *
     * @param string $productSku
     * @return string
     */
    private function getQuery(string $productSku): string
    {
        return <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    min_sale_qty,
                    max_sale_qty
                }
            }
        }
QUERY;
    }
}
