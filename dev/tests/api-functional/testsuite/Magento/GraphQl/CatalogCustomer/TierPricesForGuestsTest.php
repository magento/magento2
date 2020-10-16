<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogCustomer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;

class TierPricesForGuestsTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForGeneralGroup()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $tierPriceData =[
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 8
            ]
        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);

        $expectedResponse = [];
        $this->assertEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForGeneralAndAllCustomerGroups()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $tierPriceData = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 6
            ],
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 8
            ]
        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertArrayHasKey('tier_prices', $response['products']['items'][0]);

        $expectedResponse = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 6
            ]
        ];
        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForNotLoggedInGroupOnly()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        $tierPriceData = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 4,
                'value'=> 6
            ]
        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertArrayHasKey('tier_prices', $response['products']['items'][0]);

        $expectedResponse = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 4,
                'value'=> 6
            ]
        ];
        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
        $this->assertCount(1, $response['products']['items'][0]['tier_prices']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForNotLoggedInAndGeneralGroups()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        $tierPriceData = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 6,
                'value'=> 6.5
            ],
            [
                'customer_group_id' => 1,
                'percentage_value'=> null,
                'qty'=> 5,
                'value'=> 6
            ]
        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertArrayHasKey('tier_prices', $response['products']['items'][0]);

        $expectedResponse = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 6,
                'value'=> 6.5
            ]
        ];
        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
        $this->assertCount(1, $response['products']['items'][0]['tier_prices']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForAllCustomerGroupsOnly()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        $tierPriceData = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 6,
                'value'=> 5
            ],

        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertArrayHasKey('tier_prices', $response['products']['items'][0]);

        $expectedResponse = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 6,
                'value'=> 5
            ]
        ];
        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
        $this->assertCount(1, $response['products']['items'][0]['tier_prices']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testTierPricesForAllCustomerGroupsAndNotLoggedInGroup()
    {
        $productSku = 'simple';
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        $tierPriceData = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 8
            ],
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 4,
                'value'=> 6.5
            ],
        ];

        $this->saveTierPrices($product, $tierPriceData);
        $query = $this->getProductSearchQuery($productSku);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['tier_prices']);
        $this->assertArrayHasKey('tier_prices', $response['products']['items'][0]);
        $expectedResponse = [
            [
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'percentage_value'=> null,
                'qty'=> 2,
                'value'=> 8
            ],
            [
                'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'percentage_value'=> null,
                'qty'=> 4,
                'value'=> 6.5
            ]
        ];

        $this->assertResponseFields($response['products']['items'][0]['tier_prices'], $expectedResponse);
        $this->assertCount(2, $response['products']['items'][0]['tier_prices']);
    }

    /**
     * @param ProductInterface $product
     * @param array $tierPriceData
     */
    private function saveTierPrices($product, $tierPriceData)
    {
        $tierPrices = [];
        /** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
        $tierPriceFactory = $this->objectManager
                ->get(\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class);
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
     * @param string $maskedQuoteId
     * @return string
     */
    private function getProductSearchQuery(string $productSku): string
    {
        return <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
    items {
      sku
      name
      tier_prices {
        customer_group_id
        percentage_value
        qty
        value
      }
    }
  }
}
QUERY;
    }
}
