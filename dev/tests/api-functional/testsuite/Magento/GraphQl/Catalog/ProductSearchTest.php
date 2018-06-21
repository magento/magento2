<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryLinkManagement;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductSearchTest extends GraphQlAbstract
{
    /**
     * Verify that layered navigation filters are returned for product query
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterLn()
    {
        $query = <<<QUERY
{
    products (
        filter: {
            sku: {
                like:"simple%"
            }
        }
        pageSize: 4
        currentPage: 1
        sort: {
            name: DESC
        }
    )
    {
        items {
            sku
        }
        filters {
            name
            filter_items_count
            request_var
            filter_items {
                label
                value_string
                items_count
                ... on SwatchLayerFilterItemInterface {
                    swatch_data {
                        type
                        value
                    }
                }
            }
        }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(
            'filters',
            $response['products'],
            'Filters are missing in product query result.'
        );
        $this->assertFilters(
            $response,
            $this->getExpectedFiltersDataSet(),
            'Returned filters data set does not match the expected value'
        );
    }

    /**
     * Get array with expected data for layered navigation filters
     *
     * @return array
     */
    private function getExpectedFiltersDataSet()
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');
        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        // Fetching option ID is required for continuous debug as of autoincrement IDs.
        return [
            [
                'name' => 'Category',
                'filter_items_count' => 1,
                'request_var' => 'cat',
                'filter_items' => [
                    [
                        'label' => 'Category 1',
                        'value_string' => '333',
                        'items_count' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Test Configurable',
                'filter_items_count' => 1,
                'request_var' => 'test_configurable',
                'filter_items' => [
                    [
                        'label' => 'Option 1',
                        'value_string' => $options[1]->getValue(),
                        'items_count' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Price',
                'filter_items_count' => 2,
                'request_var' => 'price',
                'filter_items' => [
                    [
                        'label' => '<span class="price">$0.00</span> - <span class="price">$9.99</span>',
                        'value_string' => '-10',
                        'items_count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$10.00</span> and above',
                        'value_string' => '10-',
                        'items_count' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * Assert filters data.
     *
     * @param array $response
     * @param array $expectedFilters
     * @param string $message
     */
    private function assertFilters($response, $expectedFilters, $message = '')
    {
        $this->assertArrayHasKey('filters', $response['products'], 'Product has filters');
        $this->assertTrue(is_array(($response['products']['filters'])), 'Product filters is array');
        $this->assertTrue(count($response['products']['filters']) > 0, 'Product filters is not empty');
        foreach ($expectedFilters as $expectedFilter) {
            $found = false;
            foreach ($response['products']['filters'] as $responseFilter) {
                if ($responseFilter['name'] == $expectedFilter['name']
                    && $responseFilter['request_var'] == $expectedFilter['request_var']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->fail($message);
            }
        }
    }

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
        $filteredProducts = [$product2, $product1];

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
        $this->assertEquals(1, $response['products']['total_count']);
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
        sort_fields 
        {
          default
          options 
          {
            value
            label
          }
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
        $filteredChildProducts = [$childProduct1, $childProduct2];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('total_count', $response['products']);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertProductItems($filteredChildProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
        $this->assertEquals(1, $response['products']['page_info']['current_page']);
        $this->assertArrayHasKey('sort_fields', $response['products']);
        $this->assertArrayHasKey('options', $response['products']['sort_fields']);
        $this->assertArrayHasKey('default', $response['products']['sort_fields']);
        $this->assertEquals('position', $response['products']['sort_fields']['default']);
        $this->assertArrayHasKey('value', $response['products']['sort_fields']['options'][0]);
        $this->assertArrayHasKey('label', $response['products']['sort_fields']['options'][0]);
        $this->assertEquals('position', $response['products']['sort_fields']['options'][0]['value']);
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
        $this->assertEquals(1, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     */
    public function testFilteringForProductInMultipleCategories()
    {
        $productSku = 'simple333';
        $query
            = <<<QUERY
{
   products(filter:{sku:{eq:"{$productSku}"}})
 {
   items{
     id
     sku
     name
     attribute_set_id
     categories {
        id
     }
   }
 }
}

QUERY;

        $response = $this->graphQlQuery($query);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple333');
        $categoryIds  = $product->getCategoryIds();
        foreach ($categoryIds as $index => $value) {
            $categoryIds[$index] = [ 'id' => (int)$value];
        }
        $this->assertNotEmpty($response['products']['items'][0]['categories'], "Categories must not be empty");
        $this->assertNotNull($response['products']['items'][0]['categories'], "categories must not be null");
        $this->assertEquals($categoryIds, $response['products']['items'][0]['categories']);
        /** @var MetadataPool $metaData */
        $metaData = ObjectManager::getInstance()->get(MetadataPool::class);
        $linkField = $metaData->getMetadata(ProductInterface::class)->getLinkField();
        $assertionMap = [

            ['response_field' => 'id', 'expected_value' => $product->getData($linkField)],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()]
        ];
        $this->assertResponseFields($response['products']['items'][0], $assertionMap);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     * @return void
     */
    public function testFilterProductsByCategoryIds()
    {
        $queryCategoryId = 333;
        $query
            = <<<QUERY
{
  products(
        filter:
        {
            category_id:{eq:"{$queryCategoryId}"}
        }
    pageSize:2
            
     )
    {
      items
      {
       sku
       name
       type_id
       categories{
          name
          id
          path
          children_count
          product_count
        }
      }
       total_count
        
    }
}

QUERY;

        $response = $this->graphQlQuery($query);

        /** @var CategoryLinkManagement $productLinks */
        $productLinks = ObjectManager::getInstance()->get(CategoryLinkManagement::class);
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);

        $links = $productLinks->getAssignedProducts($queryCategoryId);
        foreach ($response['products']['items'] as $itemIndex => $itemData) {
            $this->assertNotEmpty($itemData);
            $this->assertEquals($response['products']['items'][$itemIndex]['sku'], $links[$itemIndex]->getSku());
            /** @var ProductRepositoryInterface $productRepository */
            $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
            /** @var ProductInterface $product */
            $product = $productRepository->get($links[$itemIndex]->getSku());
            $this->assertEquals($response['products']['items'][$itemIndex]['name'], $product->getName());
            $this->assertEquals($response['products']['items'][$itemIndex]['type_id'], $product->getTypeId());
            $categoryIds  = $product->getCategoryIds();
            foreach ($categoryIds as $index => $value) {
                $categoryIds[$index] = (int)$value;
            }
            $categoryInResponse = array_map(
                null,
                $categoryIds,
                $response['products']['items'][$itemIndex]['categories']
            );
            foreach ($categoryInResponse as $key => $categoryData) {
                $this->assertNotEmpty($categoryData);
                /** @var CategoryInterface | Category $category */
                $category = $categoryRepository->get($categoryInResponse[$key][0]);
                $this->assertResponseFields(
                    $categoryInResponse[$key][1],
                    [
                        'name' => $category->getName(),
                        'id' => $category->getId(),
                        'path' => $category->getPath(),
                        'children_count' => $category->getChildrenCount(),
                        'product_count' => $category->getProductCount(),
                    ]
                );
            }
        }
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
        $filteredProducts = [$visibleProduct2, $visibleProduct1];
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(20, $response['products']['page_info']['page_size']);
        $this->assertEquals(1, $response['products']['page_info']['current_page']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testProductQueryUsingFromAndToFilterInput()
    {
        $query
            = <<<QUERY
{
    products(
        filter: { 
            price:{
                from:"5" to:"20"
            } 
        }
        sort: {
            sku: DESC
        }
    ) {
        total_count
        items {
            attribute_set_id
            sku
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
                maximalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }     
            type_id
            ...on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple1');
        $product2 = $productRepository->get('simple2');
        $filteredProducts = [$product2, $product1];

        $this->assertProductItemsWithMaximalAndMinimalPriceCheck($filteredProducts, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testProductBasicFullTextSearchQuery()
    {
        $textToSearch = 'Simple';
        $query
            =<<<QUERY
{
    products(
      search: "{$textToSearch}"
    )
    {
        total_count
        items {
          name
          sku
          price {
            minimalPrice {
              amount {
                value
                currency
              }
            }
          }
        }
        page_info {
          page_size
          current_page
        }
      }
}
QUERY;
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $prod1 = $productRepository->get('simple1');

        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, $response['products']['total_count']);

        $filteredProducts = [$prod1];
        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        foreach ($productItemsInResponse as $itemIndex => $itemArray) {
            $this->assertNotEmpty($itemArray);
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                [
                    'sku' => $filteredProducts[$itemIndex]->getSku(),
                    'name' => $filteredProducts[$itemIndex]->getName(),
                    'price' => [
                        'minimalPrice' => [
                            'amount' => [
                                'value' => $filteredProducts[$itemIndex]->getSpecialPrice(),
                                'currency' => 'USD'
                            ]
                        ]
                    ]
                ]
            );
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testProductsThatMatchWithPricesFromList()
    {
        $query
            =<<<QUERY
            {
    products(
        filter:
        {
            price:{in:["10","20"]}

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
         attribute_set_id
         sku
         price {
            regularPrice {
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
        $this->assertEquals(2, $response['products']['total_count']);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $prod1 = $productRepository->get('simple2');
        $prod2 = $productRepository->get('simple1');
        $filteredProducts = [$prod1, $prod2];
        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        foreach ($productItemsInResponse as $itemIndex => $itemArray) {
            $this->assertNotEmpty($itemArray);
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                ['attribute_set_id' => $filteredProducts[$itemIndex]->getAttributeSetId(),
                    'sku' => $filteredProducts[$itemIndex]->getSku(),
                    'name' => $filteredProducts[$itemIndex]->getName(),
                    'price' => [
                        'regularPrice' => [
                            'amount' => [
                                'value' => $filteredProducts[$itemIndex]->getPrice(),
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products_with_few_out_of_stock.php
     */
    public function testFilterProductsThatAreOutOfStockWithConfigSettings()
    {
        $query
            =<<<QUERY
{
  products(
        filter:
        {
            sku:{like:"simple%"}
        }
    pageSize:20
            
     )
    {
      items
      {
       sku
       name
      }
       total_count
        
    }
}
QUERY;
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        ObjectManager::getInstance()->get(\Magento\Framework\App\Cache::class)
            ->clean(\Magento\Framework\App\Config::CACHE_TAG);
        $response = $this->graphQlQuery($query);
        $responseObject = new DataObject($response);
        self::assertEquals(
            'simple_visible_in_stock',
            $responseObject->getData('products/items/0/sku')
        );
        self::assertEquals(
            'Simple Product Visible and InStock',
            $responseObject->getData('products/items/0/name')
        );
        $this->assertEquals(1, $response['products']['total_count']);
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

        for ($itemIndex = 0; $itemIndex < count($filteredProducts); $itemIndex++) {
            $this->assertNotEmpty($productItemsInResponse[$itemIndex]);
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

    private function assertProductItemsWithMaximalAndMinimalPriceCheck(array $filteredProducts, array $actualResponse)
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
                                'value' => $filteredProducts[$itemIndex]->getSpecialPrice(),
                                'currency' => 'USD'
                            ]
                        ],
                        'maximalPrice' => [
                            'amount' => [
                                'value' => $filteredProducts[$itemIndex]->getSpecialPrice(),
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
