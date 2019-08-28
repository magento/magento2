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
use Magento\Framework\Config\Data;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

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
        CacheCleaner::cleanAll();
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
     *  Layered navigation for Configurable products with out of stock options
     * Two configurable products each having two variations and one of the child products of one Configurable set to OOS
     *
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testLayeredNavigationWithConfigurableChildrenOutOfStock()
    {
        CacheCleaner::cleanAll();
        $attributeCode = 'test_configurable';
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $firstOption = $options[0]->getValue();
        $secondOption = $options[1]->getValue();
        $query = $this->getQueryProductsWithCustomAttribute($attributeCode, $firstOption);
        $response = $this->graphQlQuery($query);

        // Out of two children, only one child product of 1st Configurable product with option1 is OOS
        $this->assertEquals(1, $response['products']['total_count']);

        // Custom attribute filter layer data
        $this->assertResponseFields(
            $response['products']['filters'][1],
            [
                'name' => $attribute->getDefaultFrontendLabel(),
                'request_var'=> $attribute->getAttributeCode(),
                'filter_items_count'=> 2,
                'filter_items' => [
                    [
                        'label' => 'Option 1',
                        'items_count' => 1,
                        'value_string' => $firstOption,
                        '__typename' =>'LayerFilterItem'
                    ],
                    [
                        'label' => 'Option 2',
                        'items_count' => 1,
                        'value_string' => $secondOption,
                        '__typename' =>'LayerFilterItem'
                    ]
                ],
            ]
        );

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $outOfStockChildProduct = $productRepository->get('simple_30');
        // All child variations with this attribute are now set to Out of Stock
        $outOfStockChildProduct->setStockData(
            ['use_config_manage_stock' => 1,
                'qty' => 0,
                'is_qty_decimal' => 0,
                'is_in_stock' => 0]
        );
        $productRepository->save($outOfStockChildProduct);
        $query = $this->getQueryProductsWithCustomAttribute($attributeCode, $firstOption);
        $response = $this->graphQlQuery($query);
        $this->assertEquals(0, $response['products']['total_count']);
        $this->assertEmpty($response['products']['items']);
        $this->assertEmpty($response['products']['filters']);
    }

    /**
     * Filter products using custom attribute of input type select(dropdown) and filterTypeInput eq
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAdvancedSearchByOneCustomAttribute()
    {
        CacheCleaner::cleanAll();
        $attributeCode = 'second_test_configurable';
        $optionValue = $this->getDefaultAttributeOptionValue($attributeCode);
        $query = <<<QUERY
{
  products(filter:{                   
                   $attributeCode: {eq: "{$optionValue}"}
                   }
                   pageSize: 3
                   currentPage: 1
       )
  {
  total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    } 
}
QUERY;

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple');
        $product2 = $productRepository->get('12345');
        $product3 = $productRepository->get('simple-4');
        $filteredProducts = [$product1, $product2, $product3 ];
        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertTrue(count($response['products']['filters']) > 0, 'Product filters is not empty');
        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($itemIndex = 0; $itemIndex < count($filteredProducts); $itemIndex++) {
            $this->assertNotEmpty($productItemsInResponse[$itemIndex]);
            //validate that correct products are returned
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                [ 'name' => $filteredProducts[$itemIndex]->getName(),
                    'sku' => $filteredProducts[$itemIndex]->getSku()
                ]
            );
        }

        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'second_test_configurable');

        // Validate custom attribute filter layer data
        $this->assertResponseFields(
            $response['products']['filters'][2],
            [
                'name' => $attribute->getDefaultFrontendLabel(),
                'request_var'=> $attribute->getAttributeCode(),
                'filter_items_count'=> 1,
                'filter_items' => [
                    [
                        'label' => 'Option 3',
                         'items_count' => 3,
                         'value_string' => $optionValue,
                         '__typename' =>'LayerFilterItem'
                     ],
                 ],
            ]
        );
    }
    /**
     * Filter products using custom attribute of input type select(dropdown) and filterTypeInput eq
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_with_multiselect_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsByMultiSelectCustomAttribute()
    {
        CacheCleaner::cleanAll();
        $attributeCode = 'multiselect_attribute';
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($i = 0; $i < count($options); $i++) {
            $optionValues[] = $options[$i]->getValue();
        }
        $query = <<<QUERY
{
  products(filter:{                   
                   $attributeCode: {in:["{$optionValues[0]}", "{$optionValues[1]}", "{$optionValues[2]}"]} 
                   }
                   pageSize: 3
                   currentPage: 1
       )
  {
  total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    } 
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['filters']);
    }

    /**
     * Get the option value for the custom attribute to be used in the graphql query
     *
     * @param string $attributeCode
     * @return string
     */
    private function getDefaultAttributeOptionValue(string $attributeCode) : string
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $defaultOptionValue = $options[0]->getValue();
        return $defaultOptionValue;
    }

    /**
     * Full text search for Product and then filter the results by custom attribute
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFullTextSearchForProductAndFilterByCustomAttribute()
    {
        CacheCleaner::cleanAll();
        $optionValue = $this->getDefaultAttributeOptionValue('second_test_configurable');

        $query = <<<QUERY
{
  products(search:"Simple",
          filter:{
          second_test_configurable: {eq: "{$optionValue}"}
          },
         pageSize: 3
         currentPage: 1
       )
  {
   total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    }
 
}
QUERY;
        $response = $this->graphQlQuery($query);
        //Verify total count of the products returned
        $this->assertEquals(3, $response['products']['total_count']);
        $expectedFilterLayers =
            [
                ['name' => 'Price',
                    'request_var'=> 'price'
                ],
                ['name' => 'Category',
                    'request_var'=> 'category_id'
                ],
                ['name' => 'Second Test Configurable',
                  'request_var'=> 'second_test_configurable'
                ],
            ];
        $layers = array_map(null, $expectedFilterLayers, $response['products']['filters']);

        //Verify all the three layers : Price, Category and Custom attribute layers are created
        foreach ($layers as $layerIndex => $layerFilterData) {
            $this->assertNotEmpty($layerFilterData);
            $this->assertEquals(
                $layers[$layerIndex][0]['name'],
                $response['products']['filters'][$layerIndex]['name'],
                'Layer name does not match'
            );
            $this->assertEquals(
                $layers[$layerIndex][0]['request_var'],
                $response['products']['filters'][$layerIndex]['request_var'],
                'request_var does not match'
            );
        }

       // Validate the price filter layer data from the response
        $this->assertResponseFields(
            $response['products']['filters'][0],
            [
                'name' => 'Price',
                'request_var'=> 'price',
                'filter_items_count'=> 2,
                'filter_items' => [
                    [
                        'label' => '10-20',
                        'items_count' => 2,
                        'value_string' => '10_20',
                         '__typename' =>'LayerFilterItem'
                     ],
                     [
                         'label' => '40-*',
                         'items_count' => 1,
                         'value_string' => '40_*',
                          '__typename' =>'LayerFilterItem'
                      ],
                 ],
            ]
        );
    }

    /**
     *  Filter by single category and custom attribute
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByCategoryIdAndCustomAttribute()
    {
        $categoryId = 13;
        $optionValue = $this->getDefaultAttributeOptionValue('second_test_configurable');
        $query = <<<QUERY
{
  products(filter:{
                   category_id : {eq:"{$categoryId}"}
                   second_test_configurable: {eq: "{$optionValue}"}
                   },
                   pageSize: 3
                   currentPage: 1
       )
  {
  total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    } 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        $actualCategoryFilterItems = $response['products']['filters'][1]['filter_items'];
        //Validate the number of categories/sub-categories that contain the products with the custom attribute
        $this->assertCount(6, $actualCategoryFilterItems);

        $expectedCategoryFilterItems =
            [
                [ 'label' => 'Category 1',
                  'items_count'=> 2
                ],
                [ 'label' => 'Category 1.1',
                  'items_count'=> 1
                ],
                [ 'label' => 'Movable Position 2',
                  'items_count'=> 1
                ],
                [ 'label' => 'Movable Position 3',
                  'items_count'=> 1
                ],
                [ 'label' => 'Category 12',
                  'items_count'=> 1
                ],
                [ 'label' => 'Category 1.2',
                  'items_count'=> 2
                ],
            ];
        $categoryFilterItems = array_map(null, $expectedCategoryFilterItems, $actualCategoryFilterItems);

//Validate the categories and sub-categories data in the filter layer
        foreach ($categoryFilterItems as $index => $categoryFilterData) {
            $this->assertNotEmpty($categoryFilterData);
            $this->assertEquals(
                $categoryFilterItems[$index][0]['label'],
                $actualCategoryFilterItems[$index]['label'],
                'Category is incorrect'
            );
            $this->assertEquals(
                $categoryFilterItems[$index][0]['items_count'],
                $actualCategoryFilterItems[$index]['items_count'],
                'Products count in the category is incorrect'
            );
        }
    }
    /**
     *
     * @return string
     */
    private function getQueryProductsWithCustomAttribute($attributeCode, $optionValue) : string
    {
        return <<<QUERY
{
  products(filter:{                   
                   $attributeCode: {eq: "{$optionValue}"}
                   }
                   pageSize: 3
                   currentPage: 1
       )
  {
  total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    } 
}
QUERY;
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
                'name' => 'Price',
                'filter_items_count' => 2,
                'request_var' => 'price',
                'filter_items' => [
                    [
                        'label' => '*-10',
                        'value_string' => '*_10',
                        'items_count' => 1,
                    ],
                    [
                        'label' => '10-*',
                        'value_string' => '10_*',
                        'items_count' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Category',
                'filter_items_count' => 1,
                'request_var' => 'category_id',
                'filter_items' => [
                    [
                        'label' => 'Category 1',
                        'value_string' => '333',
                        'items_count' => 2,
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
            ]
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
            price:{from: "5", to: "50"}
            sku:{like:"simple%"}
            name:{like:"Simple%"}
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
          price:{from:"5.59"}
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
        $this->expectExceptionMessage(
            'GraphQL response contains errors: currentPage value 2 specified is greater ' .
            'than the 1 page(s) available'
        );
        $this->graphQlQuery($query);
    }

    /**
     * Filtering for products and sorting using multiple sort parameters
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryProductsInCurrentPageSortedByMultipleSortParameters()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{to :"50"}            
            sku:{like:"simple%"}
            name:{like:"simple%"}
             
        }
         pageSize:4
         currentPage:1
         sort:
         {
          price:ASC
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
     * Verify the items is correct after sorting their name in ASC order
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testQueryProductsSortedByNameASC()
    {

        $query
            = <<<QUERY
{
    products(
        filter:
        {
            sku: {
                like:"simple%"
            }
        }
         pageSize:1
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
        name
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
        $product = $productRepository->get('simple2');

        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertEquals(['page_size' => 1, 'current_page' => 2], $response['products']['page_info']);
        $this->assertEquals(
            [['sku' => $product->getSku(), 'name' => $product->getName()]],
            $response['products']['items']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilteringForProductsFromMultipleCategories()
    {
        $query
            = <<<QUERY
{
   products(filter:{     
          category_id :{in:["4","5","12"]}
         })
 {
    items
     {
       sku
      name
      }
       total_count
  filters{
    request_var
    name
    filter_items_count
    filter_items{
      value_string
      label
    }
  }
     }
}

QUERY;

        $response = $this->graphQlQuery($query);
        /** @var ProductRepositoryInterface $productRepository */
        $this->assertEquals(3, $response['products']['total_count']);
    }

    /**
     * Filter products by single category
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     * @return void
     */
    public function testFilterProductsBySingleCategoryId()
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
     * Sorting the search results by relevance (DESC => most relevant)
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_relevance_sorting.php
     * @return void
     */
    public function testFilterProductsAndSortByRelevance()
    {
        $search_term ="red white blue grey socks";
        $query
            = <<<QUERY
{
  products(
        search:"{$search_term}"
        
        sort:{relevance:DESC}
        pageSize: 5
        currentPage: 1
       )
  {
    total_count
    items 
     {
      name
      sku
      }
    page_info{
      current_page
      page_size
      total_pages
    }
    filters{
      name
      request_var
      filter_items_count 
      filter_items{
        label
        items_count
        value_string
        __typename
      }
       
    }    
      
    }
 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
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
           sku:{like:"simple%"}
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testFilterProductsWithinASpecificPriceRangeSortedByPriceDESC()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $prod1 = $productRepository->get('simple2');
        $prod2 = $productRepository->get('simple1');
        $filteredProducts = [$prod1, $prod2];
        /** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
        $categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
        foreach ($filteredProducts as $product) {
            $categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                [333]
            );
        }

        $query
            =<<<QUERY
{
    products(
        filter:
        {
            price:{from:"5" to: "20"}

        }
         pageSize:4
         currentPage:1
         sort:
         {
          price:DESC
         }
    )
    {
      items
       {
         attribute_set_id
         sku
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
        filters
        {
            request_var
            name
            filter_items_count
        }
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
        $this->assertProductItemsWithPriceCheck($filteredProducts, $response);
        //verify that by default Price and category are the only layers available
        $filterNames = ['Price', 'Category'];
        $this->assertCount(2, $response['products']['filters'], 'Filter count does not match');
        for ($i = 0; $i < count($response['products']['filters']); $i++) {
            $this->assertEquals($filterNames[$i], $response['products']['filters'][$i]['name']);
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
        price:{from:"50"}
        sku:{like:"simple%"}
        name:{like:"simple%"}
        
    }
    pageSize:2
    currentPage:1
    sort:
   {
    position:ASC
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
            price:{to:"10"}
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
        $this->expectExceptionMessage(
            'GraphQL response contains errors: currentPage value 2 specified is greater ' .
            'than the 1 page(s) available.'
        );
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
        $this->expectExceptionMessage(
            'GraphQL response contains errors: \'search\' or \'filter\' input argument is ' .
            'required.'
        );
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
     * Verify that invalid current page return an error
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     * @expectedException \Exception
     * @expectedExceptionMessage currentPage value must be greater than 0
     */
    public function testInvalidCurrentPage()
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
    currentPage: 0
  ) {
    items {
      sku
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Verify that invalid page size returns an error.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     * @expectedException \Exception
     * @expectedExceptionMessage pageSize value must be greater than 0
     */
    public function testInvalidPageSize()
    {
        $query = <<<QUERY
{
  products (
    filter: {
      sku: {
        like:"simple%"
      }
    }
    pageSize: 0
    currentPage: 1
  ) {
    items {
      sku
    }
  }
}
QUERY;
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
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
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

    private function assertProductItemsWithPriceCheck(array $filteredProducts, array $actualResponse)
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
                        ],
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
}
