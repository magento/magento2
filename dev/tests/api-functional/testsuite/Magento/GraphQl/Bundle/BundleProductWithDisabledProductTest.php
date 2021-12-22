<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Exception;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * class BundleProductWithDisabledProductTest
 *
 * Test Bundle product with disabled product and verify graphQl response
 */
class BundleProductWithDisabledProductTest extends GraphQlAbstract
{
    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->compareArraysRecursively = $objectManager->create(CompareArraysRecursively::class);
    }

    /**
     * Test Bundle product with disabled product test.
     *
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_with_disabled_product_options.php
     *
     * @throws Exception
     */
    public function testBundleProductWithMultipleOptionsWithDisabledProduct(): void
    {
        $categorySku = 'c1';
        $query
            = <<<QUERY
{
    categoryList(filters: {url_path: {eq: "{$categorySku}"}}) {
    children_count
    id
    url_path
    url_key
    id
    products {
      total_count
      items {
        ... on BundleProduct {
          id
          categories {
            id
            name
            description
          }
          dynamic_price
          price_range {
            minimum_price {
              regular_price {
                value
                currency
              }
            }
            maximum_price {
              regular_price {
                value
                currency
              }
            }
          }
          sku
          name
          short_description {
            html
          }
          description {
            html
          }
          stock_status
          __typename
          url_key
          items {
            position
            uid
            option_id
            options {
              uid
              label
              id
              price
              quantity
              product {
                ... on VirtualProduct {
                  sku
                  stock_status
                  name
                }
                price_range {
                  minimum_price {
                    regular_price {
                      value
                      currency
                    }
                  }
                  maximum_price {
                    regular_price {
                      value
                      currency
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertBundleProduct($response);
    }

    /**
     * Assert bundle product response.
     *
     * @param array $response
     */
    private function assertBundleProduct(array $response): void
    {
        $this->assertNotEmpty(
            $response['categoryList'][0]['products']['items'],
            'Precondition failed: "items" must not be empty'
        );
        $productItems = end($response['categoryList'][0]['products']['items'])['items'];
        $this->assertEquals(3, count($productItems[0]['options']));
        $this->assertEquals('virtual1', $productItems[0]['options'][0]['product']['sku']);
        $this->assertEquals('virtual2', $productItems[0]['options'][1]['product']['sku']);
        $this->assertEquals('virtual3', $productItems[0]['options'][2]['product']['sku']);
        $this->assertEquals(3, count($productItems[1]['options']));
        $this->assertEquals('virtual1', $productItems[1]['options'][0]['product']['sku']);
        $this->assertEquals('virtual2', $productItems[1]['options'][1]['product']['sku']);
        $this->assertEquals('virtual3', $productItems[1]['options'][2]['product']['sku']);
    }
}
