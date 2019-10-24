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
use Magento\Eav\Model\Config;
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
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class ProductSearchTest extends GraphQlAbstract
{
    /**
     * Verify that layered navigation filters and aggregations are correct for product query
     *
     * Filter products by an array of skus
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
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
                in:["simple1", "simple2"]
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
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(
            'filters',
            $response['products'],
            'Filters are missing in product query result.'
        );

        $expectedFilters = $this->getExpectedFiltersDataSet();
        $actualFilters = $response['products']['filters'];
        // presort expected and actual results as different search engines have different orders
        usort($expectedFilters, [$this, 'compareFilterNames']);
        usort($actualFilters, [$this, 'compareFilterNames']);

        $this->assertFilters(
            ['products' => ['filters' => $actualFilters]],
            $expectedFilters,
            'Returned filters data set does not match the expected value'
        );
    }

    /**
     * Compare arrays by value in 'name' field.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    private function compareFilterNames(array $a, array $b)
    {
        return strcmp($a['name'], $b['name']);
    }

    /**
     *  Layered navigation for Configurable products with out of stock options
     * Two configurable products each having two variations and one of the child products of one Configurable set to OOS
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testLayeredNavigationForConfigurableProducts()
    {
        CacheCleaner::cleanAll();
        $attributeCode = 'test_configurable';

        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $firstOption = $options[0]->getValue();
        $secondOption = $options[1]->getValue();
        $query = $this->getQueryProductsWithArrayOfCustomAttributes($attributeCode, $firstOption, $secondOption);
        $this->reIndexAndCleanCache();
        $response = $this->graphQlQuery($query);

        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['aggregations']);
        $this->assertNotEmpty($response['products']['filters'], 'Filters is empty');
        $this->assertCount(2, $response['products']['aggregations'], 'Aggregation count does not match');

        // Custom attribute filter layer data
        $this->assertResponseFields(
            $response['products']['aggregations'][1],
            [
                'attribute_code' => $attribute->getAttributeCode(),
                'label'=> $attribute->getDefaultFrontendLabel(),
                'count'=> 2,
                'options' => [
                    [
                        'label' => 'Option 1',
                        'value' => $firstOption,
                        'count' =>'2'
                    ],
                    [
                        'label' => 'Option 2',
                        'value' => $secondOption,
                        'count' =>'2'
                    ]
                ],
            ]
        );
    }

    /**
     *
     * @return string
     */
    private function getQueryProductsWithArrayOfCustomAttributes($attributeCode, $firstOption, $secondOption) : string
    {
        return <<<QUERY
{
  products(filter:{                   
                   $attributeCode: {in:["{$firstOption}", "{$secondOption}"]}
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
    aggregations{
        attribute_code
        count
        label
        options{
           label
           value
           count
    }
  }
      
    } 
}
QUERY;
    }

    /**
     * Filter products by custom attribute of dropdown type and filterTypeInput eq
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsByDropDownCustomAttribute()
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
     aggregations{
        attribute_code
        count
        label
        options
        {
          label
          count
          value
        }
      }
      
    } 
}
QUERY;

        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple');
        $product2 = $productRepository->get('12345');
        $product3 = $productRepository->get('simple-4');
        $filteredProducts = [$product1, $product2, $product3 ];
        $countOfFilteredProducts = count($filteredProducts);
        $this->reIndexAndCleanCache();
        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count'], 'Number of products returned is incorrect');
        $this->assertTrue(count($response['products']['filters']) > 0, 'Product filters is not empty');
        $this->assertCount(3, $response['products']['aggregations'], 'Incorrect count of aggregations');

        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        for ($itemIndex = 0; $itemIndex < $countOfFilteredProducts; $itemIndex++) {
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
        $eavConfig = $objectManager->get(Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'second_test_configurable');
        // Validate custom attribute filter layer data from aggregations
        $this->assertResponseFields(
            $response['products']['aggregations'][2],
            [
                'attribute_code' => $attribute->getAttributeCode(),
                'count'=> 1,
                'label'=> $attribute->getDefaultFrontendLabel(),
                'options' => [
                    [
                        'label' => 'Option 3',
                         'count' => 3,
                         'value' => $optionValue
                     ],
                 ],
            ]
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reIndexAndCleanCache() : void
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex", $out);
        CacheCleaner::cleanAll();
    }

    /**
     * Filter products using an array of  multi select custom attributes
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_with_multiselect_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsByMultiSelectCustomAttributes()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->reIndexAndCleanCache();
        $attributeCode = 'multiselect_attribute';
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $countOptions = count($options);
        $optionValues = [];
        for ($i = 0; $i < $countOptions; $i++) {
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
       aggregations{
        attribute_code
        count
        label
        options
        {
          label
          value
        
        }
      }
      
    } 
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['filters']);
        $this->assertNotEmpty($response['products']['aggregations']);
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
     * Full text search for Products and then filter the results by custom attribute ( sort is by defaulty by relevance)
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSearchAndFilterByCustomAttribute()
    {
        $this->reIndexAndCleanCache();
        $attribute_code = 'second_test_configurable';
        $optionValue = $this->getDefaultAttributeOptionValue($attribute_code);

        $query = <<<QUERY
{
  products(search:"Simple",
          filter:{
          $attribute_code: {in:["{$optionValue}"]}
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
    aggregations
    {
        attribute_code
        count
        label
        options
        {
          count
          label
          value
        }
    }   
      
    }
 
}
QUERY;
        $response = $this->graphQlQuery($query);
        //Verify total count of the products returned
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertArrayHasKey('filters', $response['products']);
        $this->assertCount(3, $response['products']['aggregations']);
        $expectedFilterLayers =
            [
                ['name' => 'Category',
                 'request_var'=> 'cat'
                ],
                ['name' => 'Second Test Configurable',
                 'request_var'=> 'second_test_configurable'
                ]
            ];
        $layers = array_map(null, $expectedFilterLayers, $response['products']['filters']);

        //Verify all the three layers from filters : Price, Category and Custom attribute layers
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

       // Validate the price layer of aggregations from the response
        $this->assertResponseFields(
            $response['products']['aggregations'][0],
            [
                'attribute_code' => 'price',
                'count'=> 2,
                'label'=> 'Price',
                'options' => [
                    [
                        'count' => 2,
                        'label' => '10-20',
                        'value' => '10_20',

                     ],
                    [
                        'count' => 1,
                        'label' => '40-*',
                        'value' => '40_*',

                    ],
                 ],
            ]
        );
        // Validate the custom attribute layer of aggregations from the response
        $this->assertResponseFields(
            $response['products']['aggregations'][2],
            [
                'attribute_code' => $attribute_code,
                'count'=> 1,
                'label'=> 'Second Test Configurable',
                'options' => [
                    [
                        'count' => 3,
                        'label' => 'Option 3',
                        'value' => $optionValue,

                    ]

                ],
            ]
        );
        // 7 categories including the subcategories to which the items belong to , are returned
        $this->assertCount(7, $response['products']['aggregations'][1]['options']);
        unset($response['products']['aggregations'][1]['options']);
        $this->assertResponseFields(
            $response['products']['aggregations'][1],
            [
                 'attribute_code' => 'category_id',
                 'count'=> 7,
                 'label'=> 'Category'
            ]
        );
    }

    /**
     *  Filter by category and custom attribute
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByCategoryIdAndCustomAttribute()
    {
        $this->reIndexAndCleanCache();
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
     aggregations
    {
        attribute_code
        count
        label
        options
        {
          count
          label
          value
        }
    }
  } 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple');
        $product2 = $productRepository->get('simple-4');
        $filteredProducts = [$product1, $product2];
        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
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
        $this->assertNotEmpty($response['products']['filters'], 'filters is empty');
        $this->assertNotEmpty($response['products']['aggregations'], 'Aggregations should not be empty');
        $this->assertCount(3, $response['products']['aggregations']);

        $actualCategoriesFromResponse = $response['products']['aggregations'][1]['options'];

        //Validate the number of categories/sub-categories that contain the products with the custom attribute
        $this->assertCount(6, $actualCategoriesFromResponse);

        $expectedCategoryInAggregrations =
            [
                [
                  'count' =>  2,
                  'label' => 'Category 1',
                  'value'=> '3'
                ],
                [
                    'count'=> 1,
                    'label' => 'Category 1.1',
                    'value'=> '4'

                ],
                [
                    'count'=> 1,
                    'label' => 'Movable Position 2',
                    'value'=> '10'

                ],
                [
                    'count'=> 1,
                    'label' => 'Movable Position 3',
                    'value'=> '11'
                ],
                [
                    'count'=> 1,
                    'label' => 'Category 12',
                    'value'=> '12'

                ],
                [
                    'count'=> 2,
                    'label' => 'Category 1.2',
                    'value'=> '13'
                ],
            ];
        // presort expected and actual results as different search engines have different orders
        usort($expectedCategoryInAggregrations, [$this, 'compareLabels']);
        usort($actualCategoriesFromResponse, [$this, 'compareLabels']);
        $categoryInAggregations = array_map(null, $expectedCategoryInAggregrations, $actualCategoriesFromResponse);

        //Validate the categories and sub-categories data in the filter layer
        foreach ($categoryInAggregations as $index => $categoryAggregationsData) {
            $this->assertNotEmpty($categoryAggregationsData);
            $this->assertEquals(
                $categoryInAggregations[$index][0]['label'],
                $actualCategoriesFromResponse[$index]['label'],
                'Category is incorrect'
            );
            $this->assertEquals(
                $categoryInAggregations[$index][0]['count'],
                $actualCategoriesFromResponse[$index]['count'],
                'Products count in the category is incorrect'
            );
        }
    }

    /**
     * Compare arrays by value in 'label' field.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    private function compareLabels(array $a, array $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    /**
     *  Filter by exact match of product url key
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterBySingleProductUrlKey()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get('simple-4');
        $urlKey = $product->getUrlKey();

        $query = <<<QUERY
{
  products(filter:{
                   url_key:{eq:"{$urlKey}"}
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
      url_key
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
     aggregations
    {
        attribute_code
        count
        label
        options
        {
          count
          label
          value
        }
    }
  } 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, $response['products']['total_count'], 'More than 1 product found');
        $this->assertCount(2, $response['products']['aggregations']);
        $this->assertResponseFields(
            $response['products']['items'][0],
            [
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'url_key'=> $product->getUrlKey()
            ]
        );
        $this->assertEquals('Price', $response['products']['aggregations'][0]['label']);
        $this->assertEquals('Category', $response['products']['aggregations'][1]['label']);
        //Disable the product
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $productRepository->save($product);
        $query2 = <<<QUERY
{
  products(filter:{
                   url_key:{eq:"{$urlKey}"}
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
      url_key
      }
    
    filters{
      name
      request_var
      filter_items_count      
    }
     aggregations
    {
        attribute_code
        count
        label
        options
        {
          count
          label
          value
        }
    }
  } 
}
QUERY;
        $response = $this->graphQlQuery($query2);
        $this->assertEquals(0, $response['products']['total_count'], 'Total count should be zero');
        $this->assertEmpty($response['products']['items']);
        $this->assertEmpty($response['products']['aggregations']);
    }

    /**
     *  Filter by multiple product url keys
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByMultipleProductUrlKeys()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product1 = $productRepository->get('simple');
        $product2 = $productRepository->get('12345');
        $product3 = $productRepository->get('simple-4');
        $filteredProducts = [$product1, $product2, $product3];
        $urlKey =[];
        foreach ($filteredProducts as $product) {
            $urlKey[] = $product->getUrlKey();
        }

        $query = <<<QUERY
{
  products(filter:{
                   url_key:{in:["{$urlKey[0]}", "{$urlKey[1]}", "{$urlKey[2]}"]}
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
      url_key
      }
    page_info{
      current_page
      page_size
      
    }
    filters{
      name
      request_var
      filter_items_count      
    }
     aggregations
    {
        attribute_code
        count
        label
        options
        {
          count
          label
          value
        }
    }
  } 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count'], 'Total count is incorrect');
        $this->assertCount(2, $response['products']['aggregations']);

        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($itemIndex = 0; $itemIndex < count($filteredProducts); $itemIndex++) {
            $this->assertNotEmpty($productItemsInResponse[$itemIndex]);
            //validate that correct products are returned
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                [ 'name' => $filteredProducts[$itemIndex]->getName(),
                  'sku' => $filteredProducts[$itemIndex]->getSku(),
                  'url_key'=> $filteredProducts[$itemIndex]->getUrlKey()
                ]
            );
        }
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
        $this->assertTrue(is_array(($response['products']['filters'])), 'Product filters is not array');
        $this->assertTrue(count($response['products']['filters']) > 0, 'Product filters is empty');
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
     * Verify product filtering using price range AND matching skus AND name sorted in DESC order
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterWithinSpecificPriceRangeSortedByNameDesc()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{from: "5", to: "50"}
            sku:{in:["simple1", "simple2"]}
            name:{match:"Simple"}
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function testSearchWithFilterWithPageSizeEqualTotalCount()
    {
        $this->reIndexAndCleanCache();
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByMultipleFilterFieldsSortedByMultipleSortFields()
    {
        $query
            = <<<QUERY
{
    products(
        filter:
        {
            price:{to :"50"}            
            sku:{in:["simple1", "simple2"]}
            name:{match:"Simple"}
             
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
     * Filtering products by fuzzy name match
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_relevance_sorting.php
     */
    public function testFilterProductsForExactMatchingName()
    {

        $query
            = <<<QUERY
{
    products(
        filter:
        {
            name: {
                match:"shorts"
            }
        }
         pageSize:2
         currentPage:1
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
          aggregations{
        attribute_code
        count
        label
        options{
          label
          value
          count
        }
      }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('grey_shorts');
        $product2 = $productRepository->get('white_shorts');
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);
        $this->assertEquals(['page_size' => 2, 'current_page' => 1], $response['products']['page_info']);
        $this->assertEquals(
            [
                ['sku' => $product1->getSku(), 'name' => $product1->getName()],
                ['sku' => $product2->getSku(), 'name' => $product2->getName()]
            ],
            $response['products']['items']
        );
        $this->assertArrayHasKey('aggregations', $response['products']);
        $this->assertCount(2, $response['products']['aggregations']);
        $expectedAggregations =[
            [
                'attribute_code' => 'price',
                'count' => 2,
                'label' => 'Price',
                'options' => [
                    [
                        'label' => '10-20',
                        'value' => '10_20',
                        'count' => 1,
                    ],
                    [
                        'label' => '20-*',
                        'value' => '20_*',
                        'count' => 1,
                    ]
                ]
            ],
            [
                'attribute_code' => 'category_id',
                'count' => 1,
                'label' => 'Category',
                'options' => [
                    [
                        'label' => 'Colorful Category',
                        'value' => '330',
                        'count' => 2,
                    ],
                ],
            ]
        ];
        $this->assertEquals($expectedAggregations, $response['products']['aggregations']);
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
         $this->assertEquals(2, $response['products']['total_count'], 'Incorrect count of products returned');
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
     * Search for products for a fuzzy match and checks if all matching results returned including
     * results based on matching keywords from description
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_relevance_sorting.php
     * @return void
     */
    public function testSearchAndSortByRelevance()
    {
        $this->reIndexAndCleanCache();
        $search_term ="blue";
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
     aggregations{
        attribute_code
        count
        label
        options{
          label
          value
          count
        }
      } 
    }
 
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['filters'], 'Filters should have the Category layer');
        $this->assertEquals('Colorful Category', $response['products']['filters'][0]['filter_items'][0]['label']);
        $this->assertCount(2, $response['products']['aggregations']);
        $productsInResponse = ['Blue briefs','Navy Blue Striped Shoes','Grey shorts'];
        /** @var \Magento\Config\Model\Config $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Config\Model\Config::class);
        if (strpos($config->getConfigDataValue('catalog/search/engine'), 'elasticsearch') !== false) {
            $this->markTestIncomplete('MC-20716');
        }
        $count = count($response['products']['items']);
        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($productsInResponse[$i], $response['products']['items'][$i]['name']);
        }
    }

    /**
     * Filtering for product with sku "equals" a specific value
     * If pageSize and current page are not requested, default values are returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByExactSkuAndSortByPriceDesc()
    {
        $query
            = <<<QUERY
{
  products(
        filter:
        {
           sku:{eq:"simple1"}
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

        $filteredProducts = [$visibleProduct1];
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, $response['products']['total_count']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(20, $response['products']['page_info']['page_size']);
        $this->assertEquals(1, $response['products']['page_info']['current_page']);
    }
    /**
     * Fuzzy search filtered for price and sorted by price and name
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_relevance_sorting.php
     */
    public function testProductBasicFullTextSearchQuery()
    {
        $this->reIndexAndCleanCache();
        $textToSearch = 'blue';
        $query
            =<<<QUERY
{
    products(
      search: "{$textToSearch}"
      filter:{
                price:{to:"50"}
             }
            sort:{
            price:DESC
            name:ASC
            }
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
          filters{
        filter_items {
          items_count
          label
          value_string
        }        
      }
      aggregations{
        attribute_code
        count
        label
        options{
          count
          label
          value
        }
      }
      }
}
QUERY;
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $prod1 = $productRepository->get('blue_briefs');
        $prod2 = $productRepository->get('grey_shorts');
        $prod3 = $productRepository->get('navy-striped-shoes');
        $response = $this->graphQlQuery($query);
        $this->assertEquals(3, $response['products']['total_count']);

        $filteredProducts = [$prod1, $prod2, $prod3];
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
                                'value' => $filteredProducts[$itemIndex]->getPrice(),
                                'currency' => 'USD'
                            ]
                        ]
                    ]
                ]
            );
        }
    }

    /**
     * Filter products purely in a given price range
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testFilterWithinASpecificPriceRangeSortedByPriceDESC()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        $prod1 = $productRepository->get('simple1');
        $prod2 = $productRepository->get('simple2');
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
          price:ASC
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
        $filterNames = ['Category', 'Price'];
        $this->assertCount(2, $response['products']['filters'], 'Filter count does not match');
        $productCount = count($response['products']['filters']);
        for ($i = 0; $i < $productCount; $i++) {
            $this->assertEquals($filterNames[$i], $response['products']['filters'][$i]['name']);
        }
    }

    /**
     * No items are returned if the conditions are not met
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
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
        
        description:{match:"Description"}
        
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
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
     * No filter or search arguments used
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
            sku:{eq:"simple_visible_in_stock"}
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
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
        eq:"simple1"
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
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
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
        eq:"simple2"
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
        $count = count($filteredProducts);
        for ($itemIndex = 0; $itemIndex < $count; $itemIndex++) {
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
