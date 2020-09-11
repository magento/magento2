<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable product queries work correctly
 */
class ConfigurableProductQueryTest extends GraphQlAbstract
{

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testNonVisibleVariationsNotReturned()
    {
        $categoryId = '2';
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "{$categoryId}"}}) {
    items {
      __typename
      sku
      name
      url_key
      price {
        regularPrice {
          amount {
            currency
            value
          }
        }
      }
      media_gallery_entries {
        media_type
        label
        position
        file
        id
        types
      }
      description {
        html
      }
    }
  }
}
QUERY;

        $result = $this->graphQlQuery($query);
        $products = $result['products']['items'];
        $this->assertCount(1, $products);
        $this->assertEquals('ConfigurableProduct', $products[0]['__typename']);
        $this->assertEquals('configurable', $products[0]['sku']);
        $this->assertArrayHasKey('media_gallery_entries', $products[0]);
    }
}
