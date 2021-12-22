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
use Magento\Catalog\Model\Indexer\Product\Category\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config as eavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class ProductSearchTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var eavConfig
     */
    private $eavConfig;

    /**
     * @var GetCategoryByName
     */
    private $getCategoryByName;

    /**
     * @var Collection
     */
    private $categoryCollection;

    /**
     * @var Processor
     */
    private $indexer;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->eavConfig = $this->objectManager->get(eavConfig::class);
        $this->getCategoryByName = $this->objectManager->get(GetCategoryByName::class);
        $this->categoryCollection = $this->objectManager->get(Collection::class);
        $this->indexer = $this->objectManager->get(Processor::class);
        $this->categoryLinkManagement = $this->objectManager->get(CategoryLinkManagementInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->config = $this->objectManager->get(Config::class);
        $this->cache = $this->objectManager->get(Cache::class);
    }

    /**
     * Verify that filters for non-existing category are empty
     *
     * @throws \Exception
     */
    public function testFilterForNonExistingCategory()
    {
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "99999999"}}) {
    filters {
      name
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

        $this->assertEmpty(
            $response['products']['filters'],
            'Returned filters data set does not empty'
        );
    }

    /**
     * Verify that filters id and uid can't be used at the same time
     */
    public function testUidAndIdUsageErrorOnProductFilteringCategory()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('`category_id` and `category_uid` can\'t be used at the same time');
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "99999999"}, category_uid: {eq: "OTk5OTk5OTk="}}) {
    filters {
      name
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Verify that layered navigation filters and aggregations are correct for product query
     *
     * Filter products by an array of skus
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
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @magentoApiDataFixture Magento/Indexer/_files/reindex_all_invalid.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testLayeredNavigationForConfigurableProducts()
    {
        $attributeCode = 'test_configurable';
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $firstOption = $options[0]->getValue();
        $secondOption = $options[1]->getValue();
        $query = $this->getQueryProductsWithArrayOfCustomAttributes($attributeCode, $firstOption, $secondOption);
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
                'label' => $attribute->getDefaultFrontendLabel(),
                'count' => 2,
                'position' => 0,
                'options' => [
                    [
                        'label' => 'Option 1',
                        'value' => $firstOption,
                        'count' => '2'
                    ],
                    [
                        'label' => 'Option 2',
                        'value' => $secondOption,
                        'count' => '2'
                    ]
                ],
            ]
        );
    }

    /**
     *
     * @return string
     */
    private function getQueryProductsWithArrayOfCustomAttributes($attributeCode, $firstOption, $secondOption): string
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
        position
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
     * @magentoApiDataFixture Magento/Indexer/_files/reindex_all_invalid.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsByDropDownCustomAttribute()
    {
        CacheCleaner::clean(['eav']);
        $attributeCode = 'second_test_configurable';
        $optionValue = $this->getDefaultAttributeOptionValue($attributeCode);
        $query = <<<QUERY
{
  products(
      filter:{ $attributeCode: {eq: "{$optionValue}"} }
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
        position
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

        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('12345');
        $product3 = $this->productRepository->get('simple-4');
        $filteredProducts = [$product3, $product2, $product1];
        $countOfFilteredProducts = count($filteredProducts);
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
                [
                    'name' => $filteredProducts[$itemIndex]->getName(),
                    'sku' => $filteredProducts[$itemIndex]->getSku()
                ]
            );
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'second_test_configurable');
        // Validate custom attribute filter layer data from aggregations
        $this->assertResponseFields(
            $response['products']['aggregations'][2],
            [
                'attribute_code' => $attribute->getAttributeCode(),
                'count' => 1,
                'label' => $attribute->getDefaultFrontendLabel(),
                'position' => $attribute->getPosition(),
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
     * Filter products using an array of  multi select custom attributes
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_with_multiselect_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterProductsByMultiSelectCustomAttributes()
    {
        $attributeCode = 'multiselect_attribute';
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
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
        $this->assertArrayNotHasKey('errors', $response, 'Response has errors.');
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['filters']);
        $this->assertNotEmpty($response['products']['aggregations']);
        $this->assertCount(2, $response['products']['aggregations']);
    }

    /**
     * Get the option value for the custom attribute to be used in the graphql query
     *
     * @param string $attributeCode
     * @return string
     */
    private function getDefaultAttributeOptionValue(string $attributeCode): string
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $defaultOptionValue = $options[0]->getValue();
        return $defaultOptionValue;
    }

    /**
     * Full text search for Products and then filter the results by custom attribute (default sort is relevance)
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @magentoApiDataFixture Magento/Indexer/_files/reindex_all_invalid.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSearchAndFilterByCustomAttribute()
    {
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
        position
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
                [
                    'name' => 'Category',
                    'request_var' => 'cat'
                ],
                [
                    'name' => 'Second Test Configurable',
                    'request_var' => 'second_test_configurable'
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
                'count' => 2,
                'label' => 'Price',
                'options' => [
                    [
                        'count' => 2,
                        'label' => '10-20',
                        'value' => '10_20',

                    ],
                    [
                        'count' => 1,
                        'label' => '40-50',
                        'value' => '40_50',

                    ],
                ],
            ]
        );
        // Validate the custom attribute layer of aggregations from the response
        $this->assertResponseFields(
            $response['products']['aggregations'][2],
            [
                'attribute_code' => $attribute_code,
                'count' => 1,
                'label' => 'Second Test Configurable',
                'position' => 1,
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
                'count' => 7,
                'label' => 'Category'
            ]
        );
    }

    /**
     *  Filter by category and custom attribute
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_custom_attribute.php
     * @magentoApiDataFixture Magento/Indexer/_files/reindex_all_invalid.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterByCategoryIdAndCustomAttribute()
    {
        $category = $this->getCategoryByName->execute('Category 1.2');
        $optionValue = $this->getDefaultAttributeOptionValue('second_test_configurable');
        $categoryUid = base64_encode($category->getId());
        $query = <<<QUERY
{
  products(filter:{
                   category_uid : {eq:"{$categoryUid}"}
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
        position
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
        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('simple-4');
        $filteredProducts = [$product2, $product1];
        $productItemsInResponse = array_map(null, $response['products']['items'], $filteredProducts);
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($itemIndex = 0; $itemIndex < count($filteredProducts); $itemIndex++) {
            $this->assertNotEmpty($productItemsInResponse[$itemIndex]);
            //validate that correct products are returned
            $this->assertResponseFields(
                $productItemsInResponse[$itemIndex][0],
                [
                    'name' => $filteredProducts[$itemIndex]->getName(),
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

        $expectedCategoryInAggregations =
            [
                [
                    'count' => 2,
                    'label' => 'Category 1',
                    'value' => '3'
                ],
                [
                    'count' => 1,
                    'label' => 'Category 1.1',
                    'value' => '4'

                ],
                [
                    'count' => 1,
                    'label' => 'Movable Position 2',
                    'value' => '10'

                ],
                [
                    'count' => 1,
                    'label' => 'Movable Position 3',
                    'value' => '11'
                ],
                [
                    'count' => 1,
                    'label' => 'Category 12',
                    'value' => '12'

                ],
                [
                    'count' => 2,
                    'label' => 'Category 1.2',
                    'value' => '13'
                ],
            ];
        // presort expected and actual results as different search engines have different orders
        usort($expectedCategoryInAggregations, [$this, 'compareLabels']);
        usort($actualCategoriesFromResponse, [$this, 'compareLabels']);
        $categoryInAggregations = array_map(null, $expectedCategoryInAggregations, $actualCategoriesFromResponse);

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
        /** @var Product $product */
        $product = $this->productRepository->get('simple-4');
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
                'url_key' => $product->getUrlKey()
            ]
        );
        $this->assertEquals('Price', $response['products']['aggregations'][0]['label']);
        $this->assertEquals('Category', $response['products']['aggregations'][1]['label']);
        //Disable the product
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $this->productRepository->save($product);
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
        /** @var Product $product */
        $product1 = $this->productRepository->get('simple');
        $product2 = $this->productRepository->get('12345');
        $product3 = $this->productRepository->get('simple-4');
        $filteredProducts = [$product3, $product2, $product1];
        $urlKey = [];
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
                [
                    'name' => $filteredProducts[$itemIndex]->getName(),
                    'sku' => $filteredProducts[$itemIndex]->getSku(),
                    'url_key' => $filteredProducts[$itemIndex]->getUrlKey()
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
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');
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
        $this->assertIsArray(($response['products']['filters']), 'Product filters is not array');
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
        $product1 = $this->productRepository->get('simple1');
        $product2 = $this->productRepository->get('simple2');
        $filteredProducts = [$product2, $product1];

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('total_count', $response['products']);
        $this->assertProductItems($filteredProducts, $response);
        $this->assertEquals(4, $response['products']['page_info']['page_size']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testSortByPosition()
    {
        // Get category ID for filtering
        $category = $this->categoryCollection->addFieldToFilter('name', 'Category 999')->getFirstItem();
        $categoryId = $category->getId();

        $queryAsc = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {position: ASC}) {
    total_count
    items {
      sku
      name
    }
  }
}
QUERY;
        $resultAsc = $this->graphQlQuery($queryAsc);
        $this->assertArrayNotHasKey('errors', $resultAsc);
        $productsAsc = array_column($resultAsc['products']['items'], 'sku');
        $expectedProductsAsc = ['simple1002', 'simple1001', 'simple1000'];
        // position equal and secondary sort by entity_id DESC
        $this->assertEquals($expectedProductsAsc, $productsAsc);

        $queryDesc = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {position: DESC}) {
    total_count
    items {
      sku
      name
    }
  }
}
QUERY;
        $resultDesc = $this->graphQlQuery($queryDesc);
        $this->assertArrayNotHasKey('errors', $resultDesc);
        $productsDesc = array_column($resultDesc['products']['items'], 'sku');
        // position equal and secondary sort by entity_id DESC
        $this->assertEquals($expectedProductsAsc, $productsDesc);

        //revert position
        $productPositions = $category->getProductsPosition();
        $count = 1;
        foreach ($productPositions as $productId => $position) {
            $productPositions[$productId] = $count;
            $count++;
        }
        ksort($productPositions);

        $category->setPostedProducts($productPositions);
        $category->save();

        // Reindex products from the result to invalidate query cache.
        $this->indexer->reindexList(array_keys($productPositions));

        $queryDesc = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {position: ASC}) {
    total_count
    items {
      sku
      name
    }
  }
}
QUERY;
        $resultDesc = $this->graphQlQuery($queryDesc);
        $this->assertArrayNotHasKey('errors', $resultDesc);
        $productsDesc = array_column($resultDesc['products']['items'], 'sku');
        // position NOT equal and oldest entity first
        $this->assertEquals(array_reverse($expectedProductsAsc), $productsDesc);
    }

    /**
     * Test products with the same relevance reverse position with ASC and DESC sorting
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_with_three_products.php
     */
    public function testSortByEqualRelevanceAndAscDescReversePosition()
    {
        $category = $this->categoryCollection->addFieldToFilter('name', 'Category 999')->getFirstItem();
        $categoryId = (int) $category->getId();

        $expectedProductsAsc = ['simple1000', 'simple1001', 'simple1002'];
        $queryAsc = $this->getCategoryFilterRelevanceQuery($categoryId, 'ASC');
        $resultAsc = $this->graphQlQuery($queryAsc);
        $this->assertArrayNotHasKey('errors', $resultAsc);
        $productsAsc = array_column($resultAsc['products']['items'], 'sku');
        $this->assertEquals($expectedProductsAsc, $productsAsc);

        $expectedProductsDesc = array_reverse($expectedProductsAsc);
        $queryDesc = $this->getCategoryFilterRelevanceQuery($categoryId, 'DESC');
        $resultDesc = $this->graphQlQuery($queryDesc);
        $this->assertArrayNotHasKey('errors', $resultDesc);
        $productsDesc = array_column($resultDesc['products']['items'], 'sku');
        $this->assertEquals($expectedProductsDesc, $productsDesc);
    }

    /**
     * Query for category filter relevance
     *
     * @param int $categoryId
     * @param string $direction
     * @return string
     */
    protected function getCategoryFilterRelevanceQuery(int $categoryId, string $direction): string
    {
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "$categoryId"}}, sort: {relevance: $direction}) {
    total_count
    items {
      sku
      name
    }
  }
}
QUERY;

        return $query;
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
        $childProduct1 = $this->productRepository->get('simple1');
        $childProduct2 = $this->productRepository->get('simple2');
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
        $product1 = $this->productRepository->get('grey_shorts');
        $product2 = $this->productRepository->get('white_shorts');
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
        $expectedAggregations = [
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
                        'label' => '20-30',
                        'value' => '20_30',
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
        $categoriesIds = ["4","5","12"];
        $query
            = <<<QUERY
{
   products(filter:{
          category_id :{in:["{$categoriesIds[0]}","{$categoriesIds[1]}","{$categoriesIds[2]}"]}
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
        $this->assertEquals(3, $response['products']['total_count']);
        $actualProducts = [];
        foreach ($categoriesIds as $categoriesId) {
            $links = $this->categoryLinkManagement->getAssignedProducts($categoriesId);
            $links = array_reverse($links);
            foreach ($links as $linkProduct) {
                $product = $this->productRepository->get($linkProduct->getSku());
                $actualProducts[$linkProduct->getSku()] = $product->getName();
            }
        }
        $expectedProducts = array_column($response['products']['items'], "name", "sku");
        $this->assertEquals($expectedProducts, $actualProducts);
    }

    /**
     * Filter products by single category
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     * @return void
     * @dataProvider filterProductsBySingleCategoryIdDataProvider
     */
    public function testFilterProductsBySingleCategoryId(string $fieldName, string $queryCategoryId)
    {
        if (is_numeric($queryCategoryId)) {
            $queryCategoryId = (int) $queryCategoryId;
        }
        $query
            = <<<QUERY
{
  products(
        filter:
        {
            {$fieldName}:{eq:"{$queryCategoryId}"}
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
          uid
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
        $links = $this->categoryLinkManagement->getAssignedProducts(
            is_numeric($queryCategoryId) ? $queryCategoryId : base64_decode($queryCategoryId)
        );
        $links = array_reverse($links);
        foreach ($response['products']['items'] as $itemIndex => $itemData) {
            $this->assertNotEmpty($itemData);
            $this->assertEquals($response['products']['items'][$itemIndex]['sku'], $links[$itemIndex]->getSku());
            /** @var ProductInterface $product */
            $product = $this->productRepository->get($links[$itemIndex]->getSku());
            $this->assertEquals($response['products']['items'][$itemIndex]['name'], $product->getName());
            $this->assertEquals($response['products']['items'][$itemIndex]['type_id'], $product->getTypeId());
            $categoryIds = $product->getCategoryIds();
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
                $category = $this->categoryRepository->get($categoryInResponse[$key][0]);
                $this->assertResponseFields(
                    $categoryInResponse[$key][1],
                    [
                        'name' => $category->getName(),
                        'id' => $category->getId(),
                        'uid' => base64_encode($category->getId()),
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
     * Sorting by relevance may return different results depending on the ES.
     * To check that sorting works, we compare results with ASC and DESC relevance sorting
     *
     * Search for products for a fuzzy match and checks if all matching results returned including
     * results based on matching keywords from description
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_relevance_sorting.php
     * @return void
     *
     * @throws \Exception
     */
    public function testSearchAndSortByRelevance()
    {
        $search_term = "blue";
        $query
            = <<<QUERY
{
  products(
        search:"{$search_term}"
        sort:{relevance:%s}
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
        $responseDesc = $this->graphQlQuery(sprintf($query, 'DESC'));
        $responseAsc = $this->graphQlQuery(sprintf($query, 'ASC'));
        $this->assertEquals(3, $responseDesc['products']['total_count']);
        $this->assertNotEmpty($responseDesc['products']['filters'], 'Filters should have the Category layer');
        $this->assertEquals('Colorful Category', $responseDesc['products']['filters'][0]['filter_items'][0]['label']);
        $this->assertCount(2, $responseDesc['products']['aggregations']);
        $expectedProductsInResponse = ['Blue briefs', 'Navy Blue Striped Shoes', 'Grey shorts'];
        $namesDesc = array_column($responseDesc['products']['items'], 'name');
        $this->assertEqualsCanonicalizing($expectedProductsInResponse, $namesDesc);
        $this->assertEquals($namesDesc, array_reverse(array_column($responseAsc['products']['items'], 'name')));
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
        $visibleProduct1 = $this->productRepository->get('simple1');

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
        $textToSearch = 'blue';
        $query
            = <<<QUERY
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
        $prod1 = $this->productRepository->get('blue_briefs');
        $prod2 = $this->productRepository->get('grey_shorts');
        $prod3 = $this->productRepository->get('navy-striped-shoes');
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
     * Partial search filtered for price and sorted by price and name
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testProductPartialNameFullTextSearchQuery()
    {
        $textToSearch = 'Sim';
        $query
            = <<<QUERY
{
    products(
      search: "{$textToSearch}"
      filter:{
                price:{to:"25"}
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
        $prod1 = $this->productRepository->get('simple1');
        $prod2 = $this->productRepository->get('simple2');
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);

        $filteredProducts = [$prod1, $prod2];
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
     * Partial search on sku filtered for price and sorted by price and sku
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products_with_different_sku_and_name.php
     */
    public function testProductPartialSkuFullTextSearchQuery()
    {
        $textToSearch = 'prd';
        $query
            = <<<QUERY
{
    products(
      search: "{$textToSearch}"
      filter:{
                price:{to:"25"}
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
        $prod1 = $this->productRepository->get('prd1sku');
        $prod2 = $this->productRepository->get('prd2-sku2');
        $response = $this->graphQlQuery($query);
        $this->assertEquals(2, $response['products']['total_count']);

        $filteredProducts = [$prod1, $prod2];
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
     * Partial search on hyphenated sku filtered for price and sorted by price and sku
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products_with_different_sku_and_name.php
     */
    public function testProductPartialSkuHyphenatedFullTextSearchQuery()
    {
        $prod2 = $this->productRepository->get('prd2-sku2');
        $textToSearch = 'sku2';
        $query
            = <<<QUERY
{
    products(
      search: "{$textToSearch}"
      filter:{
                price:{to:"25"}
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

        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, $response['products']['total_count']);

        $filteredProducts = [$prod2];
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
     * Filter products purely in a given price range
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testFilterWithinASpecificPriceRangeSortedByPriceDESC()
    {
        $prod1 = $this->productRepository->get('simple1');
        $prod2 = $this->productRepository->get('simple2');
        $filteredProducts = [$prod1, $prod2];
        /** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
        foreach ($filteredProducts as $product) {
            $this->categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                [333]
            );
        }

        $query
            = <<<QUERY
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
           name
           sku
           type_id
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
            = <<<QUERY
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
        $this->config->saveConfig(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->cache->clean(\Magento\Framework\App\Config::CACHE_TAG);
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
     */
    public function testInvalidCurrentPage()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage value must be greater than 0');

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
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testInvalidPageSize()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('pageSize value must be greater than 0');

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
                [
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
                    'type_id' => $filteredProducts[$itemIndex]->getTypeId(),
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
                [
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
                    'type_id' => $filteredProducts[$itemIndex]->getTypeId(),
                    'weight' => $filteredProducts[$itemIndex]->getWeight()
                ]
            );
        }
    }

    /**
     * Data provider for product single category filtering
     *
     * @return array[][]
     */
    public function filterProductsBySingleCategoryIdDataProvider(): array
    {
        return [
            [
                'fieldName' => 'category_id',
                'categoryId' => '333',
            ],
            [
                'fieldName' => 'category_uid',
                'categoryId' => base64_encode('333'),
            ],
        ];
    }
}
