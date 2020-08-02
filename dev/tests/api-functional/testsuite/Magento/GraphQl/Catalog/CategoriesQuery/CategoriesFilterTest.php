<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\CategoriesQuery;

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test categories query filtering works as expected
 */
class CategoriesFilterTest extends GraphQlAbstract
{
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
    categories(filters: { $field : { $condition : "$value" } }){
        items{
            id
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
        $this->assertCount(1, $result['categories']['items']);
        $this->assertResponseFields($result['categories']['items'][0], $expectedResult);
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
    categories(filters: { $field : { $condition : $value } }){
        items{
            id
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
        $this->assertCount(count($expectedResult), $result['categories']['items']);
        foreach ($expectedResult as $i => $expected) {
            $this->assertResponseFields($result['categories']['items'][$i], $expected);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterCategoryByMultipleFields()
    {
        $query = <<<QUERY
{
    categories(filters: {ids: {in: ["6","7","8","9","10"]}, name: {match: "Movable"}}){
        total_count
        items{
            id
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
        $this->assertCount(3, $result['categories']['items']);
        $this->assertEquals(3, $result['categories']['total_count']);

        $expectedCategories = [7 => 'Movable', 9 => 'Movable Position 1', 10 => 'Movable Position 2'];
        $actualCategories = array_column($result['categories']['items'], 'name', 'id');
        $this->assertEquals($expectedCategories, $actualCategories);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterWithInactiveCategory()
    {
        $query = <<<QUERY
{
    categories(filters: {url_key: {in: ["inactive", "category-2"]}}){
        items{
            id
            name
            url_key
            url_path
        }
    }
}
QUERY;
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categories']['items']);
        $actualCategories = array_column($result['categories']['items'], 'url_key', 'id');
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
    categories(filters: {ids: {in: ["3"]}}){
        items{
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
}
QUERY;
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categories']['items']);
        $baseCategory = $result['categories']['items'][0];

        $this->assertEquals('Category 1', $baseCategory['name']);
        $this->assertArrayHasKey('products', $baseCategory);
        //Check base category products
        $expectedBaseCategoryProducts = [
            ['sku' => 'simple', 'name' => 'Simple Product'],
            ['sku' => 'simple-4', 'name' => 'Simple Product Three'],
            ['sku' => '12345', 'name' => 'Simple Product Two'],
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
            ['sku' => 'simple', 'name' => 'Simple Product']
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
    categories(filters: {ids: {in: ["3"]}}){
        items{
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
}
QUERY;
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['categories']['items']);
        $baseCategory = $result['categories']['items'][0];

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
    categories(filters: {url_key: {in: ["inactive", "does-not-exist"]}}){
        items{
            id
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
        $this->assertArrayHasKey('categories', $result);
        $this->assertEquals([], $result['categories']['items']);
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
    categories{
        items{
            id
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
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $storeRootCategoryId = $storeManager->getStore()->getRootCategoryId();

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertEquals('Default Category', $result['categories']['items'][0]['name']);
        $this->assertEquals($storeRootCategoryId, $result['categories']['items'][0]['id']);
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
    categories(filters: {name: {match: "mo"}}){
        items{
            id
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
        $categoryCollection = Bootstrap::getObjectManager()->get(CategoryCollection::class);
        $categoryModel = $categoryCollection
            ->addAttributeToSelect('image')
            ->addAttributeToFilter('name', ['eq' => 'Parent Image Category'])
            ->getFirstItem();
        $categoryId = $categoryModel->getId();

        $query = <<<QUERY
{
    categories(filters: {ids: {in: ["$categoryId"]}}) {
        items{
            id
            name
            image
        }
    }
}
QUERY;
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $storeBaseUrl = $storeManager->getStore()->getBaseUrl('media');

        $expected = "catalog/category/magento_long_image_name_magento_long_image_name_magento_long_image_name.jpg";
        $expectedImageUrl = rtrim($storeBaseUrl, '/') . '/' . $expected;

        $response = $this->graphQlQuery($query);
        $categories = $response['categories'];
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['categories']['items']);
        $expectedImageUrl = str_replace('index.php/', '', $expectedImageUrl);
        $categories['items'][0]['image'] = str_replace('index.php/', '', $categories['items'][0]['image']);
        $this->assertEquals('Parent Image Category', $categories['items'][0]['name']);
        $this->assertEquals($expectedImageUrl, $categories['items'][0]['image']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathTopLevelCategory()
    {
        $urlPath = 'category-1';
        $query = <<<QUERY
{
    categories(filters: {url_path: {eq: "$urlPath"}}){
        items{
            id
            name
            url_key
            url_path
            path
            position
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categories = $response['categories'];
        $this->assertCount(1, $categories);
        $this->assertEquals($urlPath, $categories['items'][0]['url_path']);
        $this->assertEquals('Category 1', $categories['items'][0]['name']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathNestedCategory()
    {
        $urlPath = 'category-1/category-1-1/category-1-1-1';
        $query = <<<QUERY
{
    categories(filters: {url_path: {eq: "$urlPath"}}){
        items{
            id
            name
            url_key
            url_path
            path
            position
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categories = $response['categories'];
        $this->assertCount(1, $categories);
        $this->assertEquals($urlPath, $categories['items'][0]['url_path']);
        $this->assertEquals('Category 1.1.1', $categories['items'][0]['name']);
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
    categories(filters: {url_path: {in: [$urlPathsString]}}){
        items{
            id
            name
            url_key
            url_path
            path
            position
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categories = $response['categories'];
        $this->assertCount(2, $categories['items']);
        $this->assertEquals($urlPaths[0], $categories['items'][0]['url_path']);
        $this->assertEquals('Category 1.1', $categories['items'][0]['name']);
        $this->assertEquals($urlPaths[2], $categories['items'][1]['url_path']);
        $this->assertEquals('Movable Position 2', $categories['items'][1]['name']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testFilterByUrlPathNoResults()
    {
        $query = <<<QUERY
{
    categories(filters: {url_path: {in: ["not-a-category url path"]}}){
        items{
            id
            name
            url_key
            url_path
            path
            position
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $categories = $response['categories'];
        $this->assertCount(0, $categories['items']);
    }

    /**
     * @return array
     */
    public function filterSingleCategoryDataProvider(): array
    {
        return [
            [
                'ids',
                'eq',
                '4',
                [
                    'id' => '4',
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
    public function filterMultipleCategoriesDataProvider(): array
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
                        'name' => 'Category 1.1',
                        'url_key' => 'category-1-1',
                        'url_path' => 'category-1/category-1-1',
                        'children_count' => '0',
                        'path' => '1/2/3/4',
                        'position' => '1'
                    ],
                    [
                        'id' => '9',
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
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
                        'name' => 'Category 1.2',
                        'url_key' => 'category-1-2',
                        'url_path' => 'category-1/category-1-2',
                        'children_count' => '0',
                        'path' => '1/2/3/13',
                        'position' => '2'
                    ],
                    [
                        'id' => '7',
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
                        'name' => 'Movable Position 1',
                        'url_key' => 'movable-position-1',
                        'url_path' => 'movable-position-1',
                        'children_count' => '0',
                        'path' => '1/2/9',
                        'position' => '5'
                    ],
                    [
                        'id' => '10',
                        'name' => 'Movable Position 2',
                        'url_key' => 'movable-position-2',
                        'url_path' => 'movable-position-2',
                        'children_count' => '0',
                        'path' => '1/2/10',
                        'position' => '6'
                    ],
                    [
                        'id' => '11',
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
}
