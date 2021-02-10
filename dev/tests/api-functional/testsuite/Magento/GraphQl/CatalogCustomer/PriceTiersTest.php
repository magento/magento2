<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogCustomer;

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class PriceTiersTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAllGroups()
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);

        $response = $this->graphQlQuery($query);

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertCount(5, $itemTiers);
        $this->assertEquals(8, $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(5, $this->getValueForQuantity(3, $itemTiers));
        $this->assertEquals(6, $this->getValueForQuantity(3.2, $itemTiers));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_with_tier_prices_for_multiple_groups.php
     */
    public function testLoggedInCustomer()
    {
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertCount(3, $itemTiers);
        $this->assertEquals(9.25, $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(8.25, $this->getValueForQuantity(3, $itemTiers));
        $this->assertEquals(7.25, $this->getValueForQuantity(5, $itemTiers));
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store_with_second_currency.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_with_tier_prices_for_multiple_groups.php
     */
    public function testSecondStoreViewWithCurrencyRate()
    {
        $storeViewCode = 'fixture_second_store';
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $rate = $storeRepository->get($storeViewCode)->getCurrentCurrencyRate();
        $productSku = 'simple';
        $query = $this->getProductSearchQuery($productSku);
        $headers = array_merge(
            $this->getCustomerAuthenticationHeader->execute('customer@example.com', 'password'),
            $this->getHeaderStore($storeViewCode)
        );

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $headers
        );

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertCount(3, $itemTiers);
        $this->assertEquals(round(9.25 * $rate, 2), $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(round(8.25 * $rate, 2), $this->getValueForQuantity(3, $itemTiers));
        $this->assertEquals(round(7.25 * $rate, 2), $this->getValueForQuantity(5, $itemTiers));
    }

    /**
     * Get the tier price value for the given product quantity
     *
     * @param float $quantity
     * @param array $tiers
     * @return float
     */
    private function getValueForQuantity(float $quantity, array $tiers): float
    {
        $filteredResult = array_values(array_filter($tiers, function ($tier) use ($quantity) {
            if ((float)$tier['quantity'] == $quantity) {
                return $tier;
            }
        }));

        return (float)$filteredResult[0]['final_price']['value'];
    }

    /**
     * Get a query which user filter for product sku and returns price_tiers
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
      price_tiers {
     	final_price {
          currency
          value
        }
        discount {
          amount_off
          percent_off
        }
        quantity
      }
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
