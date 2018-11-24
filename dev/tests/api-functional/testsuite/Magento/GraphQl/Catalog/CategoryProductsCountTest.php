<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as productStatus;

/**
 * Class CategoryProductsCountTest
 *
 * Test for Magento\CatalogGraphQl\Model\Resolver\Category\ProductsCount resolver
 */
class CategoryProductsCountTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCategoryWithSaleableProduct()
    {
        $categoryId = 2;

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      product_count
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertEquals(1, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testCategoryWithInvisibleProduct()
    {
        $categoryId = 333;
        $sku = 'simple333';

        $product = $this->productRepository->get($sku);
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $this->productRepository->save($product);

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      product_count
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertEquals(0, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testCategoryWithOutOfStockProductManageStockEnabled()
    {
        $categoryId = 2;

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      product_count
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertEquals(0, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testCategoryWithOutOfStockProductManageStockDisabled()
    {
        $categoryId = 333;

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      product_count
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertEquals(1, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testCategoryWithDisabledProduct()
    {
        $categoryId = 333;
        $sku = 'simple333';

        $product = $this->productRepository->get($sku);
        $product->setStatus(ProductStatus::STATUS_DISABLED);
        $this->productRepository->save($product);

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      product_count
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertEquals(0, $response['category']['product_count']);
    }
}
