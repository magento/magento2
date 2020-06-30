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

class SpecialPriceTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
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
        /** @var string $productSku */
        $productSku = 'simple';
        /** @var string $query */
        $query = $this->getProductSearchQuery($productSku);

        $response = $this->graphQlQuery($query);

        /** @var float $specialPrice */
        $specialPrice = (float)$response['products']['items'][0]['special_price'];
        $this->assertEquals(5.99, $specialPrice);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_special_price.php
     */
    public function testSpecialPriceWithCurrencyRate()
    {
        /** @var string $storeViewCode */
        $storeViewCode = 'fixture_second_store';
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        /** @var float $rate */
        $rate = $storeRepository->get($storeViewCode)->getCurrentCurrencyRate();
        /** @var string $productSku */
        $productSku = 'simple';
        /** @var string $query */
        $query = $this->getProductSearchQuery($productSku);
        /** @var array $headers */
        $headers = $this->getHeaderStore($storeViewCode);

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $headers
        );

        /** @var float $specialPrice */
        $specialPrice = (float)$response['products']['items'][0]['special_price'];
        $this->assertEquals(round(5.99 * $rate, 2), $specialPrice);
    }

    /**
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQuery(string $productSku): string
    {
        return <<<QUERY
{
  products(search: "{$productSku}") {
    items {
      special_price
    }
  }
}
QUERY;
    }

    /**
     * @param string $storeViewCode
     * @return array
     */
    private function getHeaderStore(string $storeViewCode): array
    {
        return ['Store' => $storeViewCode];
    }
}
