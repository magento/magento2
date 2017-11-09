<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
        find:
        {
          and:
          {
            price:{gt: "5", lt: "50"}
            or:
            {
              sku:{like:"simple%"}
              name:{like:"Simple%"}              
             }
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
         price
         name
         weight
         status
         type_id
         visibility
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
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
    }

    /**
     * Requesting for items that are visible in Catalog, Search or Both matching SKU or NAME
     * and price < $60 with a special price and weight = 1 sorted by price in DESC
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
        find:
        {
          and:
          {
            special_price:{neq:"null"}
            price:{lt:"60"}
           or:
            {
              sku:{like:"%simple%"}
              name:{like:"%configurable%"}
            }
             or:
             {
              visibility:{in:["2", "3","4"]}
              weight:{eq:"1"}              
             }
          }                    
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
           price
           name
           weight
           status
           type_id
           visibility
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
        $filteredProducts = [$product2, $product1];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('total_count', $response['products']);
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
        find:
        {
          and:
          {
            price:{gt: "5", lt: "50"}
            or:
            {
              sku:{like:"simple%"}
              name:{like:"simple%"}              
             }
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
           price
           name
           weight
           status
           type_id
           visibility
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
        find:
        {
          and:
          {
            price:{gt: "5", lt: "50"}
            or:
            {
              sku:{eq:"simple1"}
              name:{like:"configurable%"}              
             }
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
        price
        name
        status
        type_id
           weight
           visibility
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
     * Sorting by visibility and price in the DESC order from the filtered items with default pageSize
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQuerySortByVisibilityAndPriceDESCWithDefaultPageSize()
    {
        $query
            = <<<QUERY
{
  products(
        find:
        {
          and:
          {
            price:{gt: "5", lt: "60"}
            or:
            {
              sku:{like:"%simple%"}
              name:{like:"%Configurable%"}              
             }
            or:
            {
              weight:{gt:"0"}
           	}
          }
        }
         sort:
         {
          visibility:DESC
          price:DESC
         }
     ) 
    {
      items
      {
        sku
        price
        name
        weight
        status
        type_id
        visibility
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

        $visibleProduct1 = $productRepository->get('simple1');
        $visibleProduct2 = $productRepository->get('simple2');
        $visibleProduct3 = $productRepository->get('simple_42');
        $visibleProduct4 = $productRepository->get('simple_41');
        $visibleProduct5 = $productRepository->get('simple_32');
        $visibleProduct6 = $productRepository->get('simple_31');
        $filteredProducts = [$visibleProduct1, $visibleProduct2,
                             $visibleProduct3, $visibleProduct4, $visibleProduct5, $visibleProduct6];

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
    find:
    {
      and:
      {
        special_price:{lt:"15"}
        price:{lt:"50"}
        visibility:{eq:"2"}
       or:
        {
          sku:{like:"simple%"}
          name:{like:"%simple%"}
        }           
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
       price
       name
       weight
       status
       type_id
       visibility
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
        find:
        {
          and:
          {
            price:{eq:"10"}

           }
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
        price
        name
        status
        type_id
           weight
           visibility
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
        $this->expectExceptionMessage('GraphQL response contains errors: The value' . ' ' .
            'specified in the currentPage attribute is greater than the number of pages available (1).');
        $this->graphQlQuery($query);
    }

    /**
     * Asserts the different fields of items returned after search query is executed
     * @param $filteredProducts
     * @param $actualResponse
     */
    private function assertProductItems($filteredProducts, $actualResponse)
    {
        $productItemsInResponse = array_map(null, $actualResponse['products']['items'], $filteredProducts);

        foreach ($productItemsInResponse as $itemIndex => $itemArray) {
            $this->assertNotEmpty($itemArray);
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                ['attribute_set_id' => $filteredProducts[$itemIndex]->getAttributeSetId(),
                 'sku' => $filteredProducts[$itemIndex]->getSku(),
                 'name' => $filteredProducts[$itemIndex]->getName(),
                 'price' => $filteredProducts[$itemIndex]->getPrice(),
                 'status' =>$filteredProducts[$itemIndex]->getStatus(),
                 'type_id' =>$filteredProducts[$itemIndex]->getTypeId(),
                 'visibility' =>$filteredProducts[$itemIndex]->getVisibility(),
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
