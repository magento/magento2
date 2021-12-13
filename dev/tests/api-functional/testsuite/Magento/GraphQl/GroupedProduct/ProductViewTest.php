<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GroupedProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductViewTest extends GraphQlAbstract
{

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_in_multiple_websites.php
     */
    public function testGroupedProductAssignedToOneWebsite()
    {
        $headerMapFirstStore['Store'] = 'default';
        $headerMapSecondStore['Store'] = 'fixture_second_store';
        $productSku = 'grouped-product';
        $query = $this->getQuery($productSku);
        $responseForFirstWebsite = $this->graphQlQuery($query, [], '', $headerMapFirstStore);
        $responseForSecondWebsite = $this->graphQlQuery($query, [], '', $headerMapSecondStore);
        self::assertEmpty($responseForFirstWebsite['products']['items']);
        $groupedProductLinks = [
            [
                'qty' => 1,
                'position' => 1,
                'product' => [
                    'sku' => 'simple',
                    'name' => 'Simple Product',
                    'type_id' => 'simple',
                    'url_key' => 'simple-product'
                ]
            ],
            [
                'qty' => 2,
                'position' => 2,
                'product' => [
                    'sku' => 'virtual-product',
                    'name' => 'Virtual Product',
                    'type_id' => 'virtual',
                    'url_key' => 'virtual-product'
                ]
            ]
        ];
        $this->assertGroupedProductItems($groupedProductLinks, $responseForSecondWebsite['products']['items'][0]);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_items_in_multiple_websites.php
     */
    public function testGroupedProductItemsAssignedToDifferentWebsites()
    {
        $headerMapFirstStore['Store'] = 'default';
        $headerMapSecondStore['Store'] = 'fixture_second_store';
        $productSku = 'grouped-product';
        $query = $this->getQuery($productSku);
        $responseForFirstWebsite = $this->graphQlQuery($query, [], '', $headerMapFirstStore);
        $responseForSecondWebsite = $this->graphQlQuery($query, [], '', $headerMapSecondStore);
        $firstWebsiteGroupedProductLinks = [
            [
                'qty' => 1,
                'position' => 1,
                'product' => [
                    'sku' => 'simple',
                    'name' => 'Simple Product',
                    'type_id' => 'simple',
                    'url_key' => 'simple-product'
                ]
            ]
        ];
        $secondWebsiteGroupedProductLinks = [
            [
                'qty' => 2,
                'position' => 2,
                'product' => [
                    'sku' => 'virtual-product',
                    'name' => 'Virtual Product',
                    'type_id' => 'virtual',
                    'url_key' => 'virtual-product'
                ]
            ]
        ];

        $this->assertGroupedProductItems(
            $firstWebsiteGroupedProductLinks,
            $responseForFirstWebsite['products']['items'][0]
        );
        $this->assertGroupedProductItems(
            $secondWebsiteGroupedProductLinks,
            $responseForSecondWebsite['products']['items'][0]
        );
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return <<<QUERY
{
  products(filter: {sku: {eq: "{$sku}"}}) {
    items {
      id
      name
      sku
      type_id
      ... on GroupedProduct {
        items{
          qty
          position
          product{
            sku
            name
            type_id
            url_key
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @param array $groupedProductLinks
     * @param $actualResponse
     */
    private function assertGroupedProductItems(array $groupedProductLinks, $actualResponse)
    {
        self::assertNotEmpty(
            $actualResponse['items'],
            "Precondition failed: 'grouped product items' must not be empty"
        );
        self::assertCount(count($groupedProductLinks), $actualResponse['items']);
        foreach ($actualResponse['items'] as $itemIndex => $bundleItems) {
            self::assertEquals($groupedProductLinks[$itemIndex], $bundleItems);
        }
    }
}
