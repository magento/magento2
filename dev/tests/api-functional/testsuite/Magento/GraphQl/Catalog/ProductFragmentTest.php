<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for simple product fragment.
 */
class ProductFragmentTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @throws Exception
     */
    public function testSimpleProductNamedFragment(): void
    {
        $sku = 'simple';
        $name = 'Simple Product';
        $price = 10;

        $query = <<<QUERY
query GetProduct {
  products(filter: { sku: { eq: "$sku" } }) {
    items {
      sku
      ...BasicProductInformation
    }
  }
}

fragment BasicProductInformation on ProductInterface {
  sku
  name
  price_range{
    minimum_price{
      final_price{
        value
      }
    }
  }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $actualProductData = $result['products']['items'][0];
        $this->assertNotEmpty($actualProductData);
        $this->assertEquals($name, $actualProductData['name']);
        $this->assertEquals($price, $actualProductData['price_range']['minimum_price']['final_price']['value']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @throws Exception
     */
    public function testSimpleProductInlineFragment(): void
    {
        $sku = 'simple';
        $name = 'Simple Product';
        $price = 10;

        $query = <<<QUERY
query GetProduct {
  products(filter: { sku: { eq: "$sku" } }) {
    items {
      sku
      ... on ProductInterface {
        name
        price_range{
          minimum_price{
            final_price{
              value
            }
          }
        }
      }
    }
  }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $actualProductData = $result['products']['items'][0];
        $this->assertNotEmpty($actualProductData);
        $this->assertEquals($name, $actualProductData['name']);
        $this->assertEquals($price, $actualProductData['price_range']['minimum_price']['final_price']['value']);
    }
}
