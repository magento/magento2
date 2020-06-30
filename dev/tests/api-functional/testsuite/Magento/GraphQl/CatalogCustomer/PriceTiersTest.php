<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogCustomer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class PriceTiersTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAllGroups()
    {
        /** @var string $productSku */
        $productSku = 'simple';
        /** @var string $query */
        $query = $this->getProductSearchQuery($productSku);

        $response = $this->graphQlQuery($query);

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertEquals(5, sizeof($itemTiers));
        $this->assertEquals(8, $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(5, $this->getValueForQuantity(3, $itemTiers));
        $this->assertEquals(6, $this->getValueForQuantity(3.2, $itemTiers));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testLoggedInCustomer()
    {
        /** @var string $productSku */
        $productSku = 'simple';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $tierPriceData =[
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 9
            ],
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 3,
                'value'=> 8.25
            ],
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 5,
                'value'=> 7
            ],
            [
                'customer_group_id' => 2,
                'percentage_value'=> null,
                'qty'=> 3,
                'value'=> 8
            ]
        ];

        $this->saveTierPrices($product, $tierPriceData);
        /** @var string $query */

        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderAuthorization('customer@example.com', 'password')
        );

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertEquals(3, sizeof($itemTiers));
        $this->assertEquals(9, $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(8.25, $this->getValueForQuantity(3, $itemTiers));
        $this->assertEquals(7, $this->getValueForQuantity(5, $itemTiers));
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store_with_second_currency.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSecondStoreViewWithCurrencyRate()
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
        $headers = array_merge(
            $this->getHeaderAuthorization('customer@example.com', 'password'),
            $this->getHeaderStore($storeViewCode)
        );

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $headers
        );

        $itemTiers = $response['products']['items'][0]['price_tiers'];
        $this->assertEquals(2, sizeof($itemTiers));
        $this->assertEquals(round(8 * $rate, 2), $this->getValueForQuantity(2, $itemTiers));
        $this->assertEquals(round(5 * $rate, 2), $this->getValueForQuantity(5, $itemTiers));
    }

    /**
     * @param float $quantity
     * @param array $tiers
     * @return float
     */
    private function getValueForQuantity(float $quantity, array $tiers)
    {
        $filteredResult = array_values(array_filter($tiers, function($tier) use ($quantity) {
            if ((float)$tier['quantity'] == $quantity) {
                return $tier;
            }
        }));

        return (float)$filteredResult[0]['final_price']['value'];
    }

    /**
     * @param ProductInterface $product
     * @param array $tierPriceData
     */
    private function saveTierPrices(ProductInterface $product, array $tierPriceData)
    {
        /** @var array $tierPrices */
        $tierPrices = [];
        /** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
        $tierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);

        foreach ($tierPriceData as $tierPrice) {
            $tierPrices[] = $tierPriceFactory->create(
                [
                    'data' => $tierPrice
                ]
            );
        }

        $product->setTierPrices($tierPrices);
        $product->save();
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
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderAuthorization(string $username, string $password): array
    {
        $customerToken = $this->objectManager->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
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
