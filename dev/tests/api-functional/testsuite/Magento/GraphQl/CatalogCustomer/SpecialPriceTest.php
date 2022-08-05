<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogCustomer;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class SpecialPriceTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_special_price.php
     */
    public function testSpecialPrice()
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);

        $response = $this->graphQlQuery($query);

        $specialPrice = (float)$response['products']['items'][0]['special_price'];
        $this->assertEquals(5.99, $specialPrice);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_special_price.php
     */
    public function testSpecialPriceWithCurrencyRate()
    {
        $storeViewCode = 'fixture_second_store';
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $rate = $storeRepository->get($storeViewCode)->getCurrentCurrencyRate();
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $headers = $this->getHeaderStore($storeViewCode);

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $headers
        );

        $specialPrice = (float)$response['products']['items'][0]['special_price'];
        $this->assertEquals(round(5.99 * $rate, 2), $specialPrice);
    }

    /**
     * Get a query which user filter for product sku and returns special_price
     *
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQuery(string $productSku): string
    {
        return <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      special_price
    }
  }
}
QUERY;
    }

    /**
     * Get array that would be used in request header
     *
     * @param string $storeViewCode
     * @return array
     */
    private function getHeaderStore(string $storeViewCode): array
    {
        return ['Store' => $storeViewCode];
    }
}
