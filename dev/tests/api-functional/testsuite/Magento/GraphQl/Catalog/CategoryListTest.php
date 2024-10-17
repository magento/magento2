<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test CategoryList GraphQl query
 */
class CategoryListTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @dataProvider filterSingleCategoryDataProvider
     * @param string $field
     * @param string $condition
     * @param string $value
     * @param array $expectedResult
     */
    public function testFilterSingleCategoryByField($field, $condition, $value, $expectedResult)
    {
        $query = <<<QUERY
{
    categoryList(filters: { $field : { $condition : "$value" } }){
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $this->assertResponseFields($result['categoryList'][0], $expectedResult);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @dataProvider filterMultipleCategoriesDataProvider
     * @param $field
     * @param $condition
     * @param $value
     * @param $expectedResult
     */
    public function testFilterMultipleCategoriesByField($field, $condition, $value, $expectedResult)
    {
        $query = <<<QUERY
{
    categoryList(filters: { $field : { $condition : $value } }){
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(count($expectedResult), $result['categoryList']);
        foreach ($expectedResult as $i => $expected) {
            $this->assertResponseFields($result['categoryList'][$i], $expected);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryByMultipleFields()
    {
        $query = <<<QUERY
{
    categoryList(filters: {ids: {in: ["6","7","8","9","10"]}, name: {match: "Movable"}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(3, $result['categoryList']);

        $expectedCategories = [7 => 'Movable', 9 => 'Movable Position 1', 10 => 'Movable Position 2'];
        $actualCategories = array_column($result['categoryList'], 'name', 'id');
        $this->assertEquals($expectedCategories, $actualCategories);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterWithInactiveCategory()
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_key: {in: ["inactive", "category-2"]}}){
        id
        name
        url_key
        url_path
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $actualCategories = array_column($result['categoryList'], 'url_key', 'id');
        $this->assertContains('category-2', $actualCategories);
        $this->assertNotContains('inactive', $actualCategories);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testQueryChildCategoriesWithProducts()
    {
        $query =  <<<QUERY
{
    categoryList(filters: {ids: {in: ["3"]}}){
        id
        name
        url_key
        url_path
        description
        products{
          total_count
          items{
            name
            sku
          }
        }
        children{
          name
          url_key
          description
          products{
            total_count
            items{
              name
              sku
            }
          }
          children{
            name
          }
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $baseCategory = $result['categoryList'][0];

        $this->assertEquals('Category 1', $baseCategory['name']);
        $this->assertArrayHasKey('products', $baseCategory);
        //Check base category products
        $expectedBaseCategoryProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => 'simple-4', 'name' => 'Simple Product Three'],
            ['sku' => '12345', 'name' => 'Simple Product Two']
        ];
        $this->assertCategoryProducts($baseCategory, $expectedBaseCategoryProducts);
        //Check base category children
        $expectedBaseCategoryChildren = [
            ['name' => 'Category 1.1', 'description' => 'Category 1.1 description.'],
            ['name' => 'Category 1.2', 'description' => 'Its a description of Test Category 1.2']
        ];
        $this->assertCategoryChildren($baseCategory, $expectedBaseCategoryChildren);

        //Check first child category
        $firstChildCategory = $baseCategory['children'][0];
        $this->assertEquals('Category 1.1', $firstChildCategory['name']);
        $this->assertEquals('Category 1.1 description.', $firstChildCategory['description']);
        $firstChildCategoryExpectedProducts = [
            ['sku' => '12345', 'name' => 'Simple Product Two'],
            ['sku' => 'simple', 'name' => 'Simple Product'],
        ];
        $this->assertCategoryProducts($firstChildCategory, $firstChildCategoryExpectedProducts);
        $firstChildCategoryChildren = [['name' =>'Category 1.1.1']];
        $this->assertCategoryChildren($firstChildCategory, $firstChildCategoryChildren);
        //Check second child category
        $secondChildCategory = $baseCategory['children'][1];
        $this->assertEquals('Category 1.2', $secondChildCategory['name']);
        $this->assertEquals('Its a description of Test Category 1.2', $secondChildCategory['description']);
        $firstChildCategoryExpectedProducts = [
            ['sku' => 'simple-4', 'name' => 'Simple Product Three'],
            ['sku' => 'simple', 'name' => 'Simple Product']
        ];
        $this->assertCategoryProducts($secondChildCategory, $firstChildCategoryExpectedProducts);
        $firstChildCategoryChildren = [];
        $this->assertCategoryChildren($secondChildCategory, $firstChildCategoryChildren);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories_disabled.php
     */
    public function testQueryCategoryWithDisabledChildren()
    {
        $query =  <<<QUERY
{
    categoryList(filters: {ids: {in: ["3"]}}){
        id
        name
        image
        url_key
        url_path
        description
        products{
          total_count
          items{
            name
            sku
          }
        }
        children{
          name
          image
          url_key
          description
          products{
            total_count
            items{
              name
              sku
            }
          }
          children{
            name
            image
            children{
              name
              image
            }
          }
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $baseCategory = $result['categoryList'][0];

        $this->assertEquals('Category 1', $baseCategory['name']);
        $this->assertArrayHasKey('products', $baseCategory);
        //Check base category products
        $expectedBaseCategoryProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => 'simple-4', 'name' => 'Simple Product Three'],
            ['sku' => '12345', 'name' => 'Simple Product Two']
        ];
        $this->assertCategoryProducts($baseCategory, $expectedBaseCategoryProducts);
        //Check base category children
        $expectedBaseCategoryChildren = [
            ['name' => 'Category 1.2', 'description' => 'Its a description of Test Category 1.2']
        ];
        $this->assertCategoryChildren($baseCategory, $expectedBaseCategoryChildren);

        //Check first child category
        $firstChildCategory = $baseCategory['children'][0];
        $this->assertEquals('Category 1.2', $firstChildCategory['name']);
        $this->assertEquals('Its a description of Test Category 1.2', $firstChildCategory['description']);

        $firstChildCategoryExpectedProducts = [
            ['sku' => 'simple-4', 'name' => 'Simple Product Three'],
            ['sku' => 'simple', 'name' => 'Simple Product']
        ];
        $this->assertCategoryProducts($firstChildCategory, $firstChildCategoryExpectedProducts);
        $firstChildCategoryChildren = [];
        $this->assertCategoryChildren($firstChildCategory, $firstChildCategoryChildren);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testNoResultsFound()
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_key: {in: ["inactive", "does-not-exist"]}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('categoryList', $result);
        $this->assertEquals([], $result['categoryList']);
    }

    /**
     * When no filters are supplied, the root category is returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testEmptyFiltersReturnRootCategory()
    {
        $query = <<<QUERY
{
    categoryList{
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeRootCategoryId = $storeManager->getStore()->getRootCategoryId();

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('categoryList', $result);
        $this->assertEquals('Default Category', $result['categoryList'][0]['name']);
        $this->assertEquals($storeRootCategoryId, $result['categoryList'][0]['id']);
        $this->assertEquals(base64_encode($storeRootCategoryId), $result['categoryList'][0]['uid']);
    }

    /**
     * Filtering with match value less than minimum query should return empty result
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testMinimumMatchQueryLength()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid match filter. Minimum length is 3.');

        $query = <<<QUERY
{
    categoryList(filters: {name: {match: "mo"}}){
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Test category image full name is returned
     *
     * @magentoApiDataFixture Magento/Catalog/_files/catalog_category_with_long_image_name.php
     */
    public function testCategoryImageName()
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->get(CategoryCollection::class);
        $categoryModel = $categoryCollection
            ->addAttributeToSelect('image')
            ->addAttributeToFilter('name', ['eq' => 'Parent Image Category'])
            ->getFirstItem();
        $categoryId = $categoryModel->getId();

        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
  id
  name
  image
 }
}
QUERY;
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeBaseUrl = $storeManager->getStore()->getBaseUrl('media');

        $expected = "catalog/category/magento_long_image_name_magento_long_image_name_magento_long_image_name.jpg";
        $expectedImageUrl = rtrim($storeBaseUrl, '/') . '/' . $expected;

        $response = $this->graphQlQuery($query);
        $categoryList = $response['categoryList'];
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['categoryList']);
        $expectedImageUrl = str_replace('index.php/', '', $expectedImageUrl);
        $categoryList[0]['image'] = str_replace('index.php/', '', $categoryList[0]['image']);
        $this->assertEquals('Parent Image Category', $categoryList[0]['name']);
        $this->assertEquals($expectedImageUrl, $categoryList[0]['image']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathTopLevelCategory()
    {
        $urlPath = 'category-1';
        $query = <<<QUERY
{
    categoryList(filters: {url_path: {eq: "$urlPath"}}){
        id
        name
        url_key
        url_path
        path
        position
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categoryList = $response['categoryList'];
        $this->assertCount(1, $categoryList);
        $this->assertEquals($urlPath, $categoryList[0]['url_path']);
        $this->assertEquals('Category 1', $categoryList[0]['name']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathNestedCategory()
    {
        $urlPath = 'category-1/category-1-1/category-1-1-1';
        $query = <<<QUERY
{
    categoryList(filters: {url_path: {eq: "$urlPath"}}){
        id
        name
        url_key
        url_path
        path
        position
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categoryList = $response['categoryList'];
        $this->assertCount(1, $categoryList);
        $this->assertEquals($urlPath, $categoryList[0]['url_path']);
        $this->assertEquals('Category 1.1.1', $categoryList[0]['name']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathMultipleCategories()
    {
        $urlPaths = ['category-1/category-1-1', 'inactive', 'movable-position-2'];
        $urlPathsString = '"' . implode('", "', $urlPaths) . '"';
        $query = <<<QUERY
{
    categoryList(filters: {url_path: {in: [$urlPathsString]}}){
        id
        name
        url_key
        url_path
        path
        position
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categoryList = $response['categoryList'];
        $this->assertCount(2, $categoryList);
        $this->assertEquals($urlPaths[0], $categoryList[0]['url_path']);
        $this->assertEquals('Category 1.1', $categoryList[0]['name']);
        $this->assertEquals($urlPaths[2], $categoryList[1]['url_path']);
        $this->assertEquals('Movable Position 2', $categoryList[1]['name']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathNoResults()
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_path: {in: ["not-a-category url path"]}}){
        id
        name
        url_key
        url_path
        path
        position
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categoryList = $response['categoryList'];
        $this->assertCount(0, $categoryList);
    }

    /**
     * @return array
     */
    public static function filterSingleCategoryDataProvider(): array
    {
        return [
            [
                'ids',
                'eq',
                '4',
                [
                    'id' => '4',
                    'uid' => base64_encode('4'),
                    'name' => 'Category 1.1',
                    'url_key' => 'category-1-1',
                    'url_path' => 'category-1/category-1-1',
                    'children_count' => '0',
                    'path' => '1/2/3/4',
                    'position' => '1'
                ]
            ],
            [
                'category_uid',
                'eq',
                base64_encode('4'),
                [
                    'id' => '4',
                    'uid' => base64_encode('4'),
                    'name' => 'Category 1.1',
                    'url_key' => 'category-1-1',
                    'url_path' => 'category-1/category-1-1',
                    'children_count' => '0',
                    'path' => '1/2/3/4',
                    'position' => '1'
                ]
            ],
            [
                'name',
                'match',
                'Movable Position 2',
                [
                    'id' => '10',
                    'uid' => base64_encode('10'),
                    'name' => 'Movable Position 2',
                    'url_key' => 'movable-position-2',
                    'url_path' => 'movable-position-2',
                    'children_count' => '0',
                    'path' => '1/2/10',
                    'position' => '6'
                ]
            ],
            [
                'url_key',
                'eq',
                'category-1-1-1',
                [
                    'id' => '5',
                    'name' => 'Category 1.1.1',
                    'url_key' => 'category-1-1-1',
                    'url_path' => 'category-1/category-1-1/category-1-1-1',
                    'children_count' => '0',
                    'path' => '1/2/3/4/5',
                    'position' => '1'
                ]
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function filterMultipleCategoriesDataProvider(): array
    {
        return[
            //Filter by multiple IDs
            [
                'ids',
                'in',
                '["4", "9", "10"]',
                [
                    [
                        'id' => '4',
                        'uid' => base64_encode('4'),
                        'name' => 'Category 1.1',
                        'url_key' => 'category-1-1',
                        'url_path' => 'category-1/category-1-1',
                        'children_count' => '0',
                        'path' => '1/2/3/4',
                        'position' => '1'
                    ],
                    [
                        'id' => '9',
                        'uid' => base64_encode('9'),
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'uid' => base64_encode('10'),
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ]
                ]
            ],
            //Filter by multiple UIDs
            [
                'category_uid',
                'in',
                '["' . base64_encode('4') . '", "' . base64_encode('9') . '", "' . base64_encode('10') . '"]',
                [
                    [
                        'id' => '4',
                        'uid' => base64_encode('4'),
                        'name' => 'Category 1.1',
                        'url_key' => 'category-1-1',
                        'url_path' => 'category-1/category-1-1',
                        'children_count' => '0',
                        'path' => '1/2/3/4',
                        'position' => '1'
                    ],
                    [
                        'id' => '9',
                        'uid' => base64_encode('9'),
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'uid' => base64_encode('10'),
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ]
                ]
            ],
            //Filter by multiple url keys
            [
                'url_key',
                'in',
                '["category-1-2", "movable"]',
                [
                    [
                        'id' => '13',
                        'uid' => base64_encode('13'),
                        'name' => 'Category 1.2',
                        'url_key' => 'category-1-2',
                        'url_path' => 'category-1/category-1-2',
                        'children_count' => '0',
                        'path' => '1/2/3/13',
                        'position' => '2'
                    ],
                    [
                        'id' => '7',
                        'uid' => base64_encode('7'),
                        'name' => 'Movable',
                        'url_key' => 'movable',
                        'url_path' => 'movable',
                        'children_count' => '0',
                        'path' => '1/2/7',
                        'position' => '3'
                    ]
                ]
            ],
            //Filter by matching multiple names
            [
                'name',
                'match',
                '"Position"',
                [
                    [
                        'id' => '9',
                        'uid' => base64_encode('9'),
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'uid' => base64_encode('10'),
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ],
                    [
                        'id' => '11',
                        'uid' => base64_encode('11'),
                        'name' => 'Movable Position 3',
                        'url_key' => 'movable-position-3',
                        'url_path' => 'movable-position-3',
                        'children_count' => '0',
                        'path' => '1/2/11',
                        'position' => '7'
                    ]
                ]
            ]
        ];
    }

    /**
     * Check category products
     *
     * @param array $category
     * @param array $expectedProducts
     */
    private function assertCategoryProducts(array $category, array $expectedProducts)
    {
        $this->assertEquals(count($expectedProducts), $category['products']['total_count']);
        $this->assertCount(count($expectedProducts), $category['products']['items']);
        $this->assertResponseFields($category['products']['items'], $expectedProducts);
    }

    /**
     * Check category child categories
     *
     * @param array $category
     * @param array $expectedChildren
     */
    private function assertCategoryChildren(array $category, array $expectedChildren)
    {
        $this->assertArrayHasKey('children', $category);
        $this->assertCount(count($expectedChildren), $category['children']);
        foreach ($expectedChildren as $i => $expectedChild) {
            $this->assertResponseFields($category['children'][$i], $expectedChild);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryInlineFragment()
    {
        $query = <<<QUERY
{
    categoryList(filters: {ids: {eq: "6"}}){
        ... on CategoryTree {
            id
            uid
            name
            url_key
            url_path
            children_count
            path
            position
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $this->assertEquals($result['categoryList'][0]['name'], 'Category 2');
        $this->assertEquals($result['categoryList'][0]['uid'], base64_encode('6'));
        $this->assertEquals($result['categoryList'][0]['url_path'], 'category-2');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryNamedFragment()
    {
        $query = <<<QUERY
{
    categoryList(filters: {ids: {eq: "6"}}){
        ...Cat
    }
}

fragment Cat on CategoryTree {
    id
    uid
    name
    url_key
    url_path
    children_count
    path
    position
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $this->assertEquals($result['categoryList'][0]['name'], 'Category 2');
        $this->assertEquals($result['categoryList'][0]['uid'], base64_encode('6'));
        $this->assertEquals($result['categoryList'][0]['url_path'], 'category-2');
    }

    /**
     * Test when there is recursive category node fragment
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryRecursiveFragment() : void
    {
        $query = <<<'QUERY'
query GetCategoryTree($filters: CategoryFilterInput!) {
    categoryList(filters: $filters) {
        ...recursiveCategoryNode
    }
}

fragment recursiveCategoryNode on CategoryTree {
  ...categoryNode
  children {
    ...categoryNode
  }
}

fragment categoryNode on CategoryTree {
  id
}
QUERY;
        $variables = [
            'filters' => [
                'ids' => [
                    'eq' => '2',
                ],
            ],
        ];
        $result = $this->graphQlQuery($query, $variables);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
        $this->assertCount(2, $result['categoryList'][0]);
        $this->assertArrayHasKey('id', $result['categoryList'][0]);
        $this->assertArrayHasKey('children', $result['categoryList'][0]);
        $this->assertEquals($result['categoryList'][0]['id'], '2');
        $this->assertCount(7, $result['categoryList'][0]['children']);
        $this->assertCount(1, $result['categoryList'][0]['children'][0]);
        $this->assertArrayHasKey('id', $result['categoryList'][0]['children'][0]);
        $this->assertEquals($result['categoryList'][0]['children'][0]['id'], '3');
    }

    /**
     * Test category list is filtered based on store in header
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoApiDataFixture Magento/Store/_files/store_with_second_root_category.php
     */
    public function testFilterStoreRootCategory() : void
    {
        $query = <<<'QUERY'
{
categoryList(filters: {name: {match: "Category"}}) {
    uid
    level
    name
    breadcrumbs {
        category_uid
        category_name
        category_level
        category_url_key
    }
}
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(7, $result['categoryList']);

        $result = $this->graphQlQuery($query, [], '', ['store' => 'test_store_1']);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categoryList']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testQueryParentCategoriesProductCount()
    {
        $query = <<<QUERY
{
    categoryList(filters: {ids: {eq: "3"}}){
        id
        name
        product_count
        level
        children{
          name
          product_count
          level
          children{
            name
            product_count
            level
            children{
                name
                product_count
                level
                children {
                    name
                    product_count
                    level
                }
            }
          }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categoryList', $response);
        $baseCategory = $response['categoryList'][0];
        $this->assertEquals(3, $baseCategory['product_count']);
    }

    /**
     * Get categoryList query for page.
     *
     * @param int $page
     * @return string
     */
    private function getQueryForPage(int $page)
    {
        return <<<QUERY
{
    categoryList(
        filters: {parent_id: {in: ["2"]}}
        pageSize: 1
        currentPage: {$page}
    ){
        id
        name
        level
        children{
          name
          level
          children{
            name
            level
            children{
                name
                level
                children {
                    name
                    level
                }
            }
          }
        }
    }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCategoryListPaginationLimitsNotAppliedToChildren()
    {
        $response = $this->graphQlQuery($this->getQueryForPage(1));
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categoryList', $response);
        $this->assertCount(1, $response['categoryList']);
        $baseCategory = $response['categoryList'][0];
        $this->assertEquals('Category 1', $baseCategory['name']);
        $this->assertCount(2, $baseCategory['children']);
        $this->assertEquals('Category 1.1', $baseCategory['children'][0]['name']);
        $this->assertEquals('Category 1.2', $baseCategory['children'][1]['name']);
        $this->assertEquals('Category 1.1.1', $baseCategory['children'][0]['children'][0]['name']);

        $response = $this->graphQlQuery($this->getQueryForPage(2));
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categoryList', $response);
        $this->assertCount(1, $response['categoryList']);
        $baseCategory = $response['categoryList'][0];
        $this->assertEquals('Category 2', $baseCategory['name']);
        $this->assertCount(0, $baseCategory['children']);

        $response = $this->graphQlQuery($this->getQueryForPage(3));
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categoryList', $response);
        $this->assertCount(1, $response['categoryList']);
        $baseCategory = $response['categoryList'][0];
        $this->assertEquals('Movable', $baseCategory['name']);
        $this->assertCount(0, $baseCategory['children']);
    }
}
