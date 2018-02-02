<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Model\Product;

class ProductSearchTest extends GraphQlAbstract
{
    /**
     * Verify that items between the price range of 5 and 50 are returned after sorting name in DESC
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsWithinSpecificPriceRangeSortedByNameDesc()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{gt: "5", lt: "50"}
            or:
            {
              sku:{like:"simple%"}
              name:{like:"Simple%"}              
             }
        }
         pageSize:4
         currentPage:1
         sort:
         {
          name:DESC
         } 
    ) 
    {
      items
       {
         sku
         price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
         }
         name
         ... on PhysicalProductInterface {
            weight
         }
         type_id
         attribute_set_id
       }    
        total_count
        page_info
        {
          page_size
          current_page
        }        
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple1');
        $product2 = $productRepository->get('simple2');
        $product3 = $productRepository->get('simple3');
        $filteredProducts = [$product3, $product2, $product1];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('total_count', $response['products']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
    }

    /**
     * Test a visible product with matching sku or name with special price
     *
     * Requesting for items that has a special price and price < $60, that are visible in Catalog, Search or Both which
     * either has a sku like “simple” or name like “configurable”sorted by price in DESC
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterVisibleProductsWithMatchingSkuOrNameWithSpecialPrice()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
          special_price:{neq:"null"}
          price:{lt:"60"}
          or:
          {
           sku:{like:"%simple%"}
           name:{like:"%configurable%"}
          }           
           weight:{eq:"1"} 
        }
        pageSize:6
        currentPage:1
        sort:
       {
        price:DESC
       } 
    )    
    {
        items
         {
           sku
           price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
           }
           name
           ... on PhysicalProductInterface {
            weight
           }
           type_id           
           attribute_set_id
         }    
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple1');
        $product2 = $productRepository->get('simple2');
        $filteredProducts = [$product2, $product1];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('total_count', $response['products']);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
    }

    /**
     * pageSize = total_count and current page = 2
     * expected - error is thrown
     * Actual - empty array
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function testSearchWithFilterWithPageSizeEqualTotalCount()
    {
        $query
            = <<<QUERY
{
    products(
     search : "simple"
        filter:
        {
          special_price:{neq:"null"}
          price:{lt:"60"}
          or:
          {
           sku:{like:"%simple%"}
           name:{like:"%configurable%"}
          }           
           weight:{eq:"1"} 
        }
        pageSize:2
        currentPage:2
        sort:
       {
        price:DESC
       } 
    )    
    {
        items
         {
           sku
           price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
           }
           name
           ... on PhysicalProductInterface {
            weight
           }
           type_id           
           attribute_set_id
         }    
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: currentPage value 1 specified is greater ' .
            'than the number of pages available.');
        $this->graphQlQuery($query);
    }

    /**
     * The query returns a total_count of 2 records; setting the pageSize = 1 and currentPage2
     * Expected result is to get the second product on the list on the second page
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testSearchWithFilterPageSizeLessThanCurrentPage()
    {
        $this->markTestSkipped('This is test is skipped due to MAGETWO-85680');
        $query
            = <<<QUERY
{
    products(
     search : "simple"
        filter:
        {
          special_price:{neq:"null"}
          price:{lt:"60"}
          or:
          {
           sku:{like:"%simple%"}
           name:{like:"%configurable%"}
          }
           weight:{eq:"1"} 
        }
        pageSize:1
        currentPage:2
        sort:
       {
        price:DESC
       } 
    )    
    {
        items
         {
           sku
           price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
           }
           name
           ... on PhysicalProductInterface {
            weight
           }
           type_id           
           attribute_set_id
         }    
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        // when pagSize =1 and currentPage = 2, it should have simple2 on first page and simple1 on 2nd page
        // since sorting is done on price in the DESC order
        $product = $productRepository->get('simple1');
        $filteredProducts = [$product];

        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
    }

    /**
     * Requesting for items that match a specific SKU or NAME within a certain price range sorted by Price in ASC order
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryProductsInCurrentPageSortedByPriceASC()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{gt: "5", lt: "50"}
            or:
            {
              sku:{like:"simple%"}
              name:{like:"simple%"}              
             }    
        }
         pageSize:4
         currentPage:1
         sort:
         {
          price:ASC
         } 
    ) 
    {
        items
         {
           sku
           price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
           }
           name
           ... on PhysicalProductInterface {
            weight
           }
           type_id           
           attribute_set_id
         }    
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $childProduct1 = $productRepository->get('simple1');
        $childProduct2 = $productRepository->get('simple2');
        $childProduct3 = $productRepository->get('simple_31');
        $childProduct4 = $productRepository->get('simple_32');
        $filteredChildProducts = [$childProduct1, $childProduct2, $childProduct3, $childProduct4];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('total_count', $response['products']);
        $this->assertEquals(6, $response['products']['total_count']);
        $this->assertProductItems($filteredChildProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
        $this->assertEquals(1, $response['products']['page_info']['current_page']);
    }

    /**
     * Verify the items in the second page is correct after sorting their name in ASC order
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsInNextPageSortedByNameASC()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{gt: "5", lt: "50"}
            or:
            {
                sku:{eq:"simple1"}
                name:{like:"configurable%"}
            }
        }
         pageSize:4
         currentPage:2
         sort:
         {
          name:ASC
         } 
    ) 
    {
      items
      {
        sku
        price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
        }
        name
        type_id
        ... on PhysicalProductInterface {
            weight
           }
           attribute_set_id
         }    
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple1');
        $filteredProducts = [$product];

        $response = $this->graphQlQuery($query);
        $this->assertEquals(5, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
        $this->assertEquals(2, $response['products']['page_info']['current_page']);
    }

    /**
     * Sorting by price in the DESC order from the filtered items with default pageSize
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQuerySortByPriceDESCWithDefaultPageSize()
    {
        $query
            = <<<QUERY
{
  products(
        filter:
        {
            price:{gt: "5", lt: "60"}
            or:
            {
              sku:{like:"%simple%"}
              name:{like:"%Configurable%"}
            }
        }
         sort:
         {
          
          price:DESC
         }
     ) 
    {
      items
      {
        sku
        price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
        }
        name
        ... on PhysicalProductInterface {
            weight
        }
        type_id
        attribute_set_id       
      }           
        total_count
        page_info
        {
          page_size
          current_page
        }        
    }
}
QUERY;
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $visibleProduct1 = $productRepository->get('simple1');
        $visibleProduct2 = $productRepository->get('simple2');
        $visibleProduct3 = $productRepository->get('simple_42');
        $visibleProduct4 = $productRepository->get('simple_41');
        $visibleProduct5 = $productRepository->get('simple_32');
        $visibleProduct6 = $productRepository->get('simple_31');
        $filteredProducts = [$visibleProduct3, $visibleProduct4,
                             $visibleProduct5, $visibleProduct6, $visibleProduct2, $visibleProduct1];

        $response = $this->graphQlQuery($query);
        $this->assertEquals(6, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(20, $response['products']['page_info']['page_size']);
        $this->assertEquals(1, $response['products']['page_info']['current_page']);
    }

    /**
     * No items are returned if the conditions are not met
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryFilterNoMatchingItems()
    {
        $query
            = <<<QUERY
{
products(
    filter:
    {
        special_price:{lt:"15"}
        price:{lt:"50"}
        weight:{gt:"4"}
        or:
        {
            sku:{like:"simple%"}
            name:{like:"%simple%"}
        }           
    }
    pageSize:2
    currentPage:1
    sort:
   {
    sku:ASC
   } 
)    
{
    items
     {
       sku
       price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
       }
       name
       ... on PhysicalProductInterface {
        weight
       }
       type_id
       attribute_set_id
     }    
    total_count
    page_info
    {
      page_size
      current_page
    }
}
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(0, $response['products']['total_count']);
        $this->assertEmpty($response['products']['items'], "No items should be returned.");
    }

    /**
     * Asserts that exception is thrown when current page > totalCount of items returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryPageOutOfBoundException()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{eq:"10"}
        }
         pageSize:2
         currentPage:2
         sort:
         {
          name:ASC
         }
    )
    {
      items
      {
        sku
        price {
            minimalPrice {
                amount {
                    value
                    currency
                }
            }
        }
        name
        type_id
        ... on PhysicalProductInterface {
           weight
         }
           attribute_set_id
         }
        total_count
        page_info
        {
          page_size
          current_page
        }
    }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: currentPage value 1 specified is greater ' .
            'than the number of pages available.');
        $this->graphQlQuery($query);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryWithNoSearchOrFilterArgumentException()
    {
        $query
            = <<<QUERY
{
  products(pageSize:1)
  {
       items{
           id
           attribute_set_id    
           created_at
           name
           sku
           type_id        
           updated_at
           ... on PhysicalProductInterface {
               weight
           }
           category_ids                
       }
   }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: \'search\' or \'filter\' input argument is ' .
            'required.');
        $this->graphQlQuery($query);
    }

    /**
     * Asserts the different fields of items returned after search query is executed
     *
     * @param Product[] $filteredProducts
     * @param array $actualResponse
     */
    private function assertProductItems(array $filteredProducts, array $actualResponse)
    {
        $productItemsInResponse = array_map(null, $actualResponse['products']['items'], $filteredProducts);

        foreach ($productItemsInResponse as $itemIndex => $itemArray) {
            $this->assertNotEmpty($itemArray);
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                ['attribute_set_id' => $filteredProducts[$itemIndex]->getAttributeSetId(),
                 'sku' => $filteredProducts[$itemIndex]->getSku(),
                 'name' => $filteredProducts[$itemIndex]->getName(),
                 'price' => [
                     'minimalPrice' => [
                         'amount' => [
                             'value' => $filteredProducts[$itemIndex]->getFinalPrice(),
                             'currency' => 'USD'
                         ]
                     ]
                 ],
                 'type_id' =>$filteredProducts[$itemIndex]->getTypeId(),
                 'weight' => $filteredProducts[$itemIndex]->getWeight()
                ]
            );
        }
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields(array $actualResponse, array $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $this->assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
