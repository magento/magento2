<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Product;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for product categories
 */
class ProductCategoriesTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_two_root_categories.php
     */
    public function testProductCategoriesInDefaultStore(): void
    {
        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => 'default']
        );

        $product = current($response['products']['items']);
        $categories = $product['categories'];

        self::assertCount(1, $categories);
        self::assertEquals('Category 1', $categories[0]['name']);
        self::assertEquals('category-1', $categories[0]['url_path']);
        self::assertEquals('category-1', $categories[0]['url_path']);
        self::assertNull($categories[0]['breadcrumbs']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_two_root_categories.php
     */
    public function testProductCategoriesInNonDefaultStore(): void
    {
        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => 'test_store_1']
        );

        $product = current($response['products']['items']);
        $categories = $product['categories'];

        self::assertCount(2, $categories);
        self::assertEquals('Second Root Subcategory', $categories[0]['name']);
        self::assertEquals('second-root-subcategory', $categories[0]['url_path']);
        self::assertNull($categories[0]['breadcrumbs']);
        self::assertEquals('Second Root Subcategory', $categories[1]['name']);
        self::assertEquals('second-root-subcategory/second-root-subsubcategory', $categories[1]['url_path']);
        self::assertNotNull($categories[1]['breadcrumbs']);
        self::assertEquals('Second Root Subcategory', $categories[1]['category_name']);
        self::assertEquals('Second Root Subcategory', $categories[1]['category_name']);
        self::assertEquals(2, $categories[1]['category_level']);
    }

    /**
     * Get query
     *
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return  <<<QUERY
{
  products(filter: { sku: { eq: "{$sku}"} }){
   	items {
      categories {
        name
        id
        url_path
        breadcrumbs {
          category_id
          category_name
          category_level
        }
      }
    }
  }
}
QUERY;
    }
}
