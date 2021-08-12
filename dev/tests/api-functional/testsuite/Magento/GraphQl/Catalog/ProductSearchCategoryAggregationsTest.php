<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Exception;
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
        $this->assertEquals(4, $categoryAggregation['count']);
        $expectedOptions = $this->getCategoryTwoOptions();
        $this->assertEquals($expectedOptions, $categoryAggregation['options']);
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
        $this->assertEquals(6, $categoryAggregation['count']);
        $expectedOptions = array_merge($this->getCategoryThreeOptions(), $this->getCategoryTwoOptions());
        $this->assertEquals($expectedOptions, $categoryAggregation['options']);
    }

    /**
     * @param string $filterValue
     *
     * @return array
     */
    private function aggregationCategoryTesting(string $filterValue, string $includeSubcategoriesOnly): array
    {
        $query = $this->getGraphQlQuery($filterValue, $includeSubcategoriesOnly);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);
        $categoryAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'category_id';
            }
        );
        $this->assertNotEmpty($categoryAggregation);
        $categoryAggregation = reset($categoryAggregation);
        $this->assertEquals('Category', $categoryAggregation['label']);
        return $categoryAggregation;
    }

    /**
     * Category ID 2, category_id aggregation options.
     *
     * @return array<string,string>
     */
    private function getCategoryTwoOptions(): array
    {
        return [
            ['label' => 'Category 1', 'value'=> '3', 'count' => '3'],
            ['label' => 'Movable Position 2', 'value'=> '10', 'count' => '1'],
            ['label' => 'Movable Position 3', 'value'=> '11', 'count' => '1'],
            ['label' => 'Category 12', 'value'=> '12', 'count' => '1']
        ];
    }

    /**
     * Category ID 3, category_id aggregation options.
     *
     * @return array<string,string>
     */
    private function getCategoryThreeOptions(): array
    {
        return [
            ['label' => 'Category 1.1', 'value'=> '4', 'count' => '2'],
            ['label' => 'Category 1.2', 'value'=> '13', 'count' => '2']
        ];
    }

    /**
     * Get graphQl query.
     *
     * @param string $categoryList
     * @param string $includeSubcategoriesOnly
     * @return string
     */
    private function getGraphQlQuery(string $categoryList, string $includeSubcategoriesOnly): string
    {
        return <<<QUERY
{
  products(filter: {$categoryList}) {
      total_count
       items { sku }
    aggregations (filter: {includeSubcategoriesOnly: {$includeSubcategoriesOnly}}) {
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
