<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for move category scenario
 *
 * Preconditions:
 *   Fixture with some categories in different levels and assigned products created
 * Steps:
 *  Send Request:
 * {
 *   categoryList(filters: {ids: {in: ["$parentCategoryId"]}}) {
 *     name
 *     path
 *     product_count
 *     children {
 *       name
 *       path
 *       product_count
 *     }
 *   }
 * }
 *
 * Expected response:
 * {
 *   "data": {
 *     "categoryList": [
 *       {
 *         "name": "Category 1",
 *         "path": "1/2/3",
 *         "product_count": 3,
 *         "children": [
 *           {
 *             "name": "Category 1.1",
 *             "path": "1/2/3/4",
 *             "product_count": 2
 *           },
 *           {
 *             "name": "Category 12",
 *             "path": "1/2/3/12",
 *             "product_count": 1
 *           },
 *           {
 *             "name": "Category 1.2",
 *             "path": "1/2/3/13",
 *             "product_count": 2
 *           }
 *         ]
 *       }
 *     ]
 *   }
 * }
 */
class CategoryMoveTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verify that category move will affect on GraphQL response
     *
     * @param string $parentCategoryId
     * @param string $movingCategory
     * @param string $childCategoryId
     * @param array $expectedData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider categoryDataProvider
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCategoryMove(
        string $parentCategoryId,
        string $movingCategory,
        string $childCategoryId,
        array $expectedData
    ): void {
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        /** @var Category $category */
        $category = $categoryRepository->get($movingCategory);
        $category->move($parentCategoryId, $childCategoryId);
        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$parentCategoryId"]}}) {
  name
  path,
  product_count
  children {
      name
      path
      product_count
  }
 }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '');

        // check are there any items in the return data
        self::assertNotNull($response['categoryList'], 'category must not be null');

        // check entire response
        $this->assertResponseFields($response['categoryList'][0], $expectedData);
    }

    /**
     * Data provider for category move
     *
     * @return array
     */
    public function categoryDataProvider(): array
    {
        return [
            [
                'parent_category_id' => '3',
                'moving_category_id' => '12',
                'child_category_id' => '12',
                'expected_data' => [
                    'name' => 'Category 1',
                    'path' => '1/2/3',
                    'product_count' => '3',
                    'children' => [
                        [
                            'name' => 'Category 1.1',
                            'path' => '1/2/3/4',
                            'product_count' => '2'
                        ],
                        [
                            'name' => 'Category 12',
                            'path' => '1/2/3/12',
                            'product_count' => '1'
                        ],
                        [
                            'name' => 'Category 1.2',
                            'path' => '1/2/3/13',
                            'product_count' => '2'
                        ]
                    ]
                ]
            ]
        ];
    }
}
