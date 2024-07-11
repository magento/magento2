<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test category read after children category was deleted
 *
 * Preconditions:
 *   Fixture with categories tree created
 * Steps:
 *  - Delete child category
 *  - Get category tree
 *  - Verify that tree doesn't contain deleted category
 */
class ReadCategoryAfterDeleteTest extends GraphQlAbstract
{
    /**
     * Verify that after delete children category data category tree returns correct values for given category
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     * @dataProvider categoriesDeleteDataProvider()
     * @param int $categoryToDelete
     * @param array $expectedResult
     * @return void
     * @throws \Exception
     */
    public function testCategoryDelete($categoryToDelete, $expectedResult): void
    {
        $this->deleteCategory($categoryToDelete);

        $query = $this->getQuery(400);
        $response = $this->graphQlQuery($query, [], '', ['store' => 'default']);
        $this->assertResponseFields($response, $expectedResult);
    }

    /**
     * Return GraphQL query string by categoryId
     *
     * @param int $categoryId
     * @return string
     */
    private function getQuery(int $categoryId): string
    {
        return <<<QUERY
{
    categoryList(filters: {ids: {in: ["$categoryId"]}}) {
        id
        name
        children_count
        children {
            id
            name
            children_count
        }
    }
}
QUERY;
    }

    /**
     * @param int $categoryId
     * @return void
     */
    private function deleteCategory(int $categoryId): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $category = $objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->load($categoryId);
        if ($category->getId()) {
            $category->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @return array
     */
    public static function categoriesDeleteDataProvider(): array
    {
        return [
            [
                'categoryToDelete' => 402,
                'expectedResult' => [
                    'categoryList' => [
                        [
                            'id' => 400,
                            'name' => 'Category 1',
                            'children_count' => 1,
                            'children' => [
                                [
                                    'id' => 401,
                                    'name' => 'Category 1.1',
                                    'children_count' => 0,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
