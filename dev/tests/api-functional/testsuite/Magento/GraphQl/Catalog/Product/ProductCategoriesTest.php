<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for product categories
 */
class ProductCategoriesTest extends GraphQlAbstract
{
    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Category","parent_id":"1","position":"2"} as:c1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subcategory","parent_id":"$c1.id$","level":"2"} as:c2
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subsubcategory","parent_id":"$c2.id$","level":"2"} as:c3
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"name":"Simple Product In Stock","sku":"in-stock-product","category_ids":["2","333","$c1.id$","$c2.id$","$c3.id$"]}
     * @magentoDataFixture Magento\Store\Test\Fixture\Website with:{"code":"test","name":"Test Website","default_group_id":"1"} as:w2
     * @magentoDataFixture Magento\Store\Test\Fixture\Group with:{"code":"test_store_group_1","name":"Test Store Group","website_id":"$w2.id$","root_category_id":"$c1.id$"} as:s2
     * @magentoDataFixture Magento\Store\Test\Fixture\Store with:{"code":"test_store_1","name":"Test Store","website_id":"$w2.id$","store_group_id":"$s2.id$"}
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * phpcs:enable Generic.Files.LineLength.TooLong
     */
    public function testProductCategoriesInDefaultStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
        $defaultWebsiteId = $websiteRepository->get('base')->getId();
        $secondWebsiteId = $websiteRepository->get('test')->getId();

        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var $product ProductInterface */
        $product = $productRepository->get('in-stock-product');
        $product
            ->setUrlKey('in-stock-product')
            ->setWebsiteIds([$defaultWebsiteId, $secondWebsiteId]);
        $productRepository->save($product);

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
        self::assertNull($categories[0]['breadcrumbs']);
    }

    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Category","parent_id":"1","position":"2"} as:c1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subcategory","parent_id":"$c1.id$","level":"2"} as:c2
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subsubcategory","parent_id":"$c2.id$","level":"2"} as:c3
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"name":"Simple Product In Stock","sku":"in-stock-product","category_ids":["2","333","$c1.id$","$c2.id$","$c3.id$"]}
     * @magentoDataFixture Magento\Store\Test\Fixture\Website with:{"code":"test","name":"Test Website","default_group_id":"1"} as:w2
     * @magentoDataFixture Magento\Store\Test\Fixture\Group with:{"code":"test_store_group_1","name":"Test Store Group","website_id":"$w2.id$","root_category_id":"$c1.id$"} as:s2
     * @magentoDataFixture Magento\Store\Test\Fixture\Store with:{"code":"test_store_1","name":"Test Store","website_id":"$w2.id$","store_group_id":"$s2.id$"}
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * phpcs:enable Generic.Files.LineLength.TooLong
     */
    public function testProductCategoriesInNonDefaultStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
        $defaultWebsiteId = $websiteRepository->get('base')->getId();
        $secondWebsiteId = $websiteRepository->get('test')->getId();

        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var $product ProductInterface */
        $product = $productRepository->get('in-stock-product');
        $product
            ->setUrlKey('in-stock-product')
            ->setWebsiteIds([$defaultWebsiteId, $secondWebsiteId]);
        $productRepository->save($product);

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
        self::assertEquals('Second Root Subsubcategory', $categories[1]['name']);
        self::assertEquals('second-root-subcategory/second-root-subsubcategory', $categories[1]['url_path']);
        self::assertCount(1, $categories[1]['breadcrumbs']);
        self::assertEquals('Second Root Subcategory', $categories[1]['breadcrumbs'][0]['category_name']);
        self::assertEquals(2, $categories[1]['breadcrumbs'][0]['category_level']);
    }

    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Category","parent_id":"1","position":"2"} as:c1
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subcategory","parent_id":"$c1.id$","level":"2"} as:c2
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Category with:{"name":"Second Root Subsubcategory","parent_id":"$c2.id$","level":"2"} as:c3
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"name":"Simple Product In Stock","sku":"in-stock-product","category_ids":["2","333","$c1.id$","$c2.id$","$c3.id$"]}
     * @magentoDataFixture Magento\Store\Test\Fixture\Website with:{"code":"test","name":"Test Website","default_group_id":"1"} as:w2
     * @magentoDataFixture Magento\Store\Test\Fixture\Group with:{"code":"test_store_group_1","name":"Test Store Group","website_id":"$w2.id$","root_category_id":"$c1.id$"} as:s2
     * @magentoDataFixture Magento\Store\Test\Fixture\Store with:{"code":"test_store_1","name":"Test Store","website_id":"$w2.id$","store_group_id":"$s2.id$"}
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * phpcs:enable Generic.Files.LineLength.TooLong
     */
    public function testProductCategoriesInNotRelevantStore(): void
    {
        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => 'fixture_second_store']
        );

        self::assertEmpty($response['products']['items']);
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
