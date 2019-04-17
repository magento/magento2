<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;
use Magento\TestFramework\ObjectManager;

/**
 * Tests cache debug headers and cache tag validation for a deep nested category and product query
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeepNestedCategoriesAndProductsTest extends AbstractGraphqlCacheTest
{
    /** @var \Magento\GraphQl\Controller\GraphQl */
    private $graphql;

    /** @var Http */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->get(Http::class);
    }

    /**
     * Test cache tags and debug header for deep nested queries involving category and products
     *
     * @magentoCache all enabled
     * @magentoDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     *
     */
    public function testDispatchForCacheHeadersOnDeepNestedQueries(): void
    {
        $categoryId ='333';
        $query
            = <<<QUERY
        {
  category(id: $categoryId) {
    products {
      items {
        attribute_set_id
        country_of_manufacture
        created_at
        description {
            html
        }
        gift_message_available
        id
        categories {
          name
          url_path
          available_sort_by
          level
          products {
            items {
              name
              id
            }
          }
        }
              }
    }
  }
}
QUERY;
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $categoryIds = [];
        $category = $categoryRepository->get('333');

        $productIdsFromCategory = $category->getProductCollection()->getAllIds();
        foreach ($productIdsFromCategory as $productId) {
            $categoryIds = array_merge($categoryIds, $productRepository->getById($productId)->getCategoryIds());
        }

        $categoryIds = array_merge($categoryIds, ['333']);
        foreach ($categoryIds as $categoryId) {
            $category = $categoryRepository->get($categoryId);
            $productIdsFromCategory= array_merge(
                $productIdsFromCategory,
                $category->getProductCollection()->getAllIds()
            );
        }

        $uniqueProductIds = array_unique($productIdsFromCategory);
        $uniqueCategoryIds = array_unique($categoryIds);
        $expectedCacheTags = ['cat_c', 'cat_p', 'FPC'];
        foreach ($uniqueProductIds as $productId) {
            $expectedCacheTags = array_merge($expectedCacheTags, ['cat_p_'.$productId]);
        }
        foreach ($uniqueCategoryIds as $categoryId) {
            $expectedCacheTags = array_merge($expectedCacheTags, ['cat_c_'.$categoryId]);
        }

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->graphql->dispatch($this->request);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->objectManager->get(\Magento\Framework\App\Response\Http::class);
        $result->renderResult($response);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEmpty(
            array_merge(
                array_diff($expectedCacheTags, $actualCacheTags),
                array_diff($actualCacheTags, $expectedCacheTags)
            )
        );
    }
}