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
        $expectedSubcategorie = $this->getSubcategoriesOfCategoryTwo();
        $this->assertEquals($expectedSubcategorie, $categoryAggregation);
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
                return $a['attribute_code'] == 'category_id';
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
