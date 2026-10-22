<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Categories of several products resolved in one request are assigned to the right product
 */
class ProductCategoriesBatchTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(CategoryFixture::class, as: 'cat_a'),
        DataFixture(CategoryFixture::class, as: 'cat_b'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-cat-1', 'category_ids' => ['$cat_a.id$']], 'p1'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'batch-cat-2', 'category_ids' => ['$cat_a.id$', '$cat_b.id$']],
            'p2'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'batch-cat-3', 'category_ids' => ['$cat_b.id$']], 'p3'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-cat-4'], 'p4'),
    ]
    public function testEachProductReturnsOnlyItsOwnCategories(): void
    {
        $query = <<<QUERY
{
  products(filter: {sku: {in: ["batch-cat-1", "batch-cat-2", "batch-cat-3", "batch-cat-4"]}}, pageSize: 10) {
    items {
      sku
      categories {
        id
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $categoryIds = [];
        foreach ($response['products']['items'] as $item) {
            $ids = array_column($item['categories'], 'id');
            sort($ids);
            $categoryIds[$item['sku']] = $ids;
        }
        ksort($categoryIds);

        $catA = (int)$this->fixtures->get('cat_a')->getId();
        $catB = (int)$this->fixtures->get('cat_b')->getId();
        $this->assertSame(
            [
                'batch-cat-1' => [$catA],
                'batch-cat-2' => [$catA, $catB],
                'batch-cat-3' => [$catB],
                'batch-cat-4' => [],
            ],
            $categoryIds
        );
    }
}
