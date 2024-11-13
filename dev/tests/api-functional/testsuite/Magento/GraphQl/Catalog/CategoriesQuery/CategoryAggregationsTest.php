<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\CategoriesQuery;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test to return category aggregations
 */
class CategoryAggregationsTest extends GraphQlAbstract
{
    /**
     * Test to return category aggregations in sorting by position
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attributes.php
     */
    public function testCategoryAggregationSorting(): void
    {
        $categoryId = 3334;
        $query = <<<QUERY
{
  products(filter: {category_id: {eq: "{$categoryId}"}}) {
    aggregations{
      label
      attribute_code
      count
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
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('aggregations', $response['products']);

        $customAggregation = array_values(array_filter(
            $response['products']['aggregations'],
            function ($a) {
                return in_array($a['attribute_code'], ['test_attribute_1', 'test_attribute_2']);
            }
        ));
        $this->assertCount(2, $customAggregation);
        $this->assertEquals('test_attribute_2', $customAggregation[0]['attribute_code']);
        $this->assertEquals('test_attribute_1', $customAggregation[1]['attribute_code']);

        /**
         * Check sorting options
         */
        $optionsAttribute1 = $customAggregation[0]['options'];
        $this->assertCount(3, $optionsAttribute1);
        $this->assertEquals('Option 1', $optionsAttribute1[0]['label']);
        $this->assertEquals('Option 2', $optionsAttribute1[1]['label']);
        $this->assertEquals('Option 3', $optionsAttribute1[2]['label']);
        $this->assertEquals(1, $optionsAttribute1[0]['count']);
        $this->assertEquals(2, $optionsAttribute1[1]['count']);
        $this->assertEquals(3, $optionsAttribute1[2]['count']);
    }

    /**
     * Test to return category aggregations filtered by category_uid
     */
    #[
        DataFixture(CategoryFixture::class, ['name' => 'Category 1', 'parent_id' => '2'], 'c1'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1-1', 'parent_id' => '$c1.id$'], 'c11'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1-1-1', 'parent_id' => '$c11.id$'], 'c111'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product1',
                'category_ids' => ['$c111.id$']
            ],
        ),
    ]
    public function testCategoryAggregationFilteredByCategoryUid(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $categoryId = $fixtures->get('c1')->getId();
        $categoryUid = base64_encode((string) $categoryId);
        $query = <<<QUERY
{
  products(filter: {category_uid: {eq: "{$categoryUid}"}}) {
    aggregations{
      label
      attribute_code
      count
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
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('aggregations', $response['products']);
    }
}
