<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test of search by category ID aggregation.
 */
class ProductSearchCategoryAggregationsTest extends GraphQlAbstract
{
    /**
     * Test category_id aggregation on filter by "eq" category ID condition.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testAggregationEqCategory()
    {
        $filterValue = '{category_id: {eq: "2"}}';
        $categoryAggregation = $this->aggregationCategoryTesting($filterValue, "true");
        $expectedSubcategorie = $this->getSubcategoriesOfCategoryTwo();
        $this->assertEquals($expectedSubcategorie, $categoryAggregation);
    }

    /**
     * Test to check aggregation with the store header
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoApiDataFixture Magento/Store/_files/store_with_second_root_category.php
     * @magentoApiDataFixture Magento/Store/_files/assign_products_to_categories_and_websites.php
     * @return void
     */
    public function testAggregationWithStoreFiltration()
    {
        $query = $this->getAggregationQuery();
        $result = $this->graphQlQuery($query);
        $categoryAggregation = $this->getCategoryAggregation($result);
        $this->assertNotEmpty($categoryAggregation);
        $result = $this->graphQlQuery($query, [], '', ['store' => 'test_store_1']);
        $categoryAggregation = $this->getCategoryAggregation($result);
        $this->assertEmpty($categoryAggregation);
    }

    /**
     * Extract category aggregation from the result
     *
     * @param array $result
     * @return array|null
     */
    private function getCategoryAggregation(array $result) : ?array
    {
        return array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'category_uid';
            }
        );
    }

    /**
     * Test category_id aggregation on filter by "in" category ID condition.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testAggregationInCategory()
    {
        $filterValue = '{category_id: {in: ["3","2"]}}';
        $categoryAggregation = $this->aggregationCategoryTesting($filterValue, "true");
        $expectedSubcategorie = $this->getSubcategoriesOfCategoryThree() + $this->getSubcategoriesOfCategoryTwo();
        $this->assertEquals($expectedSubcategorie, $categoryAggregation);
    }

    /**
     * @param string $filterValue
     *
     * @return array
     */
    private function aggregationCategoryTesting(string $filterValue, string $includeDirectChildrenOnly): array
    {
        $query = $this->getGraphQlQuery($filterValue, $includeDirectChildrenOnly);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);
        $categoryAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'category_uid';
            }
        );
        $this->assertNotEmpty($categoryAggregation);
        $categoryAggregation = reset($categoryAggregation);
        $this->assertEquals('Category', $categoryAggregation['label']);
        $categoryAggregationIdsLabel = [];
        foreach ($categoryAggregation['options'] as $option) {
            $this->assertNotEmpty($option['value']);
            $this->assertNotEmpty($option['label']);
            $categoryAggregationIdsLabel[(int)$option['value']] = $option['label'];
        }
        return $categoryAggregationIdsLabel;
    }

    /**
     * Category ID 2, category_id aggregation options.
     *
     * @return array<string,string>
     */
    private function getSubcategoriesOfCategoryTwo(): array
    {
        return [
            3 => 'Category 1',
            10 => 'Movable Position 2',
            11 => 'Movable Position 3',
            12 => 'Category 12'
        ];
    }

    /**
     * Category ID 3, category_id aggregation options.
     *
     * @return array<string,string>
     */
    private function getSubcategoriesOfCategoryThree(): array
    {
        return [
            4 => 'Category 1.1',
            13 => 'Category 1.2'
        ];
    }

    private function getAggregationQuery() : string
    {
        return <<<QUERY
query {
  products(filter: { category_id: { eq: "3" } }) {
    total_count

    aggregations {
      attribute_code

      label

      count

      options {
        count

        label

        value
      }
    }

    items {
      name

      sku

      price_range {
        minimum_price {
          regular_price {
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
    }

    /**
     * Get graphQl query.
     *
     * @param string $categoryList
     * @param string $includeDirectChildrenOnly
     * @return string
     */
    private function getGraphQlQuery(string $categoryList, string $includeDirectChildrenOnly): string
    {
        return <<<QUERY
{
  products(filter: {$categoryList}) {
      total_count
       items { sku }
    aggregations (filter: { category: {includeDirectChildrenOnly: {$includeDirectChildrenOnly}}}) {
      attribute_code
      count
      label
      options {
        count
        label
        value
      }
    }
  }
}
QUERY;
    }
}
