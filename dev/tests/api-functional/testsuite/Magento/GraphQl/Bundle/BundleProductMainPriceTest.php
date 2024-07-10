<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class BundleProductMainPriceTest extends GraphQlAbstract
{
    public function getQuery()
    {
        $productSku = 'fixed_bundle_product_with_special_price';
        return <<<QUERY
{
   products(filter:{ sku:{eq:"{$productSku}"}})
   {
    items {
      url_key
      sku
         price_range {
             minimum_price {
                    final_price {
                      value
                      currency
                    }
              discount {
                percent_off
                amount_off
              }
              regular_price {
                value
                currency
              } }
                  maximum_price {
                    final_price {
                      value
                      currency
                    }
                    regular_price {
                      value
                      currency
                    }
                    discount {
                      percent_off
                      amount_off
                    }
                  } }
   ... on BundleProduct {
          price_details{
              main_price
              main_final_price
              discount_percentage
          }
          dynamic_sku
          dynamic_price
          dynamic_weight
          price_view
          ship_bundle_items
          items {
            uid
            title
            required
            type
            position
            sku
            options {
              uid
              quantity
              position
              is_default
              price
              price_type
              can_change_quantity
              label
              product {
                uid
                name
                sku
                price_range {
                  minimum_price {
                    final_price {
                      value
                    }
                      regular_price {
                value
              } }
                  maximum_price {
                    final_price {
                      value
                      currency
                    }
                      regular_price {
                value
                        currency
              }
                  }

                }
                __typename
              }
            }
          }
  }
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/fixed_bundle_product_with_special_price.php
     * @return void
     */
    public function testBundleProductPriceDetails(): void
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);
        $product = $response['products']['items'][0];
        $this->assertArrayHasKey('price_details', $product);
        $priceDetails = $product['price_details'];
        $this->assertArrayHasKey('main_price', $priceDetails);
        $this->assertArrayHasKey('main_final_price', $priceDetails);
        $this->assertArrayHasKey('discount_percentage', $priceDetails);
        $this->assertEquals(50.0, $priceDetails['main_price']);
        $this->assertEquals(40.0, $priceDetails['main_final_price']);
        $this->assertEquals(20, $priceDetails['discount_percentage']);
    }
}
