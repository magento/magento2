<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Covers breadcrumbs support by GraphQl
 */
class BreadcrumbsTest extends GraphQlAbstract
{
    /**
     * Verify the fields of CMS Block selected by identifiers.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     * @return void
     */
    public function testGetBreadcrumbs(): void
    {
        $categoryCollection = ObjectManager::getInstance()->get(CollectionFactory::class)->create();
        $categoryCollection->addAttributeToFilter('name', ['eq' => 'Category 1.1.1']);
        $selectedCategoryId = (int)$categoryCollection->getFirstItem()->getId();
        $query =
            <<<QUERY
{
  category(id: $selectedCategoryId) {
    name
    breadcrumbs {
      category_id
      category_name
      category_level
      category_url_key
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('category', $response);
        $this->assertArrayHasKey('breadcrumbs', $response['category']);
        $this->assertBaseFields($selectedCategoryId, $response);
    }

    /**
     * Verify the fields of CMS Block selected by identifiers.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     * @return void
     */
    public function testGetBreadcrumbsForNonExistingCategory(): void
    {
        $query =
            <<<QUERY
{
  category(id: 0) {
    name
    breadcrumbs {
      category_id
      category_name
      category_level
      category_url_key
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('category', $response);
        $this->assertNull(
            $response['category'],
            'Value of "category" field must be NULL if requested category doesn\'t exist'
        );
        $this->assertCount(
            1,
            $response,
            'There should be only "category" field if requested category doesn\'t exist '
        );
    }

    /**
     * Asserts the equality of the response fields to the fields given in assertion map.
     * Assert that values of the fields are different from NULL
     *
     * @param array $actualResponse
     * @param array $assertionMap
     * @return void
     */
    private function assertResponseFields(array $actualResponse, array $assertionMap): void
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $this->assertEquals(
                $expectedValue[$key],
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }

    /**
     * Get breadcrumbs for given category.
     *
     * @param Category $category
     * @return array
     */
    private function getBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [];
        $rootId = Bootstrap::getObjectManager()->get(StoreManagerInterface::class)
            ->getStore()
            ->getRootCategoryId();
        foreach ($category->getParentCategories() as $parentCategory) {
            if ($parentCategory->getId() !== $rootId) {
                $breadcrumbs[] = [
                    'category_id' => $parentCategory->getId(),
                    'category_name' => $parentCategory->getName(),
                    'category_level' => $parentCategory->getLevel(),
                    'category_url_key' => $parentCategory->getUrlKey(),
                ];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Asserts base fields
     *
     * @param int $categoryId
     * @param array $actualResponse
     * @return void
     */
    private function assertBaseFields(int $categoryId, array $actualResponse): void
    {
        $category = Bootstrap::getObjectManager()->create(Category::class)->load($categoryId);
        $assertionMap = [
            [
                'response_field' => 'category',
                'expected_value' =>
                    [
                        [
                            'name'        => $category->getName(),
                            'breadcrumbs' => $this->getBreadcrumbs($category->getParentCategory()),
                        ],

                    ],
            ],
        ];

        /**
         * @param array $actualResponse
         * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
         *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
         */
        $this->assertResponseFields($actualResponse, $assertionMap);
    }
}
