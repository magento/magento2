<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Test\Fixture\AssignCategories as AssignCategoriesFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test of search by category ID aggregation.
 */
class ProductSearchCategoryAggregationsTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Uid */
    private $uid;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->uid = $this->objectManager->get(Uid::class);
    }
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

    #[
        DataFixture(ProductFixture::class, as: 'prod1'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1'], as: 'cat1'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2', 'is_active' => false], as: 'cat2'),
        DataFixture(AssignCategoriesFixture::class, ['categories' => ['$cat1$', '$cat2$'], 'product' => '$prod1$']),
    ]
    public function testAggregationDisabledCategory(): void
    {
        $cat1 = $this->fixtures->get('cat1');
        $cat2 = $this->fixtures->get('cat2');
        $filterValue = "{category_id: {in: [\"{$cat1->getId()}\",\"{$cat2->getId()}\"]}}";
        $categoryAggregation = $this->aggregationCategoryTesting($filterValue, 'false');
        $expectedCategories = [$cat1->getId() => $cat1->getName()];
        $this->assertEquals($expectedCategories, $categoryAggregation);
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
            $categoryAggregationIdsLabel[$this->uid->decode($option['value'])] = $option['label'];
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

    /**
     * Test the categories that appear in aggregation Layered Navigation > Display Category Filter => Yes (default).
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @throws \Exception
     */
    public function testFetchCategoriesWhenDisplayCategoryEnabled(): void
    {
        $result = $this->aggregationWithDisplayCategorySetting();
        $aggregationAttributeCode = [];
        foreach ($result['products']['aggregations'] as $aggregation) {
            $this->assertArrayHasKey('attribute_code', $aggregation);
            $aggregationAttributeCode[] = $aggregation['attribute_code'];
        }
        $this->assertTrue(in_array('category_uid', $aggregationAttributeCode));
    }

    /**
     * Test the categories not in aggregation when Layered Navigation > Display Category Filter => No.
     *
     * @magentoConfigFixture catalog/layered_navigation/display_category 0
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @throws \Exception
     */
    public function testDontFetchCategoriesWhenDisplayCategoryDisabled(): void
    {
        $result = $this->aggregationWithDisplayCategorySetting();
        $aggregationAttributeCode = [];
        foreach ($result['products']['aggregations'] as $aggregation) {
            $this->assertArrayHasKey('attribute_code', $aggregation);
            $aggregationAttributeCode[] = $aggregation['attribute_code'];
        }
        $this->assertFalse(in_array('category_uid', $aggregationAttributeCode));
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function aggregationWithDisplayCategorySetting(): array
    {
        $query = $this->getGraphQlQueryProductSearch();
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);
        return $result;
    }

    /**
     * Get graphQl query.
     *
     * @return string
     */
    private function getGraphQlQueryProductSearch(): string
    {
        return <<<QUERY
{
  products(
    search: "simple"
    pageSize: 20
    currentPage: 1
    sort: {  }
  ) {
    items {
      sku
      canonical_url
      categories{
        name
        path
      }
}
    aggregations (filter: {category: {includeDirectChildrenOnly: true}}) {
      attribute_code
      count
      label
      options {
        label
        value
      }
    }
  }
}
QUERY;
    }
}
