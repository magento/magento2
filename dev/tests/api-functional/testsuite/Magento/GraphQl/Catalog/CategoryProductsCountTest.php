<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as productStatus;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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

    /**
     * @var Config $config
     */
    private $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = ObjectManager::getInstance();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->resourceConfig = $objectManager->get(Config::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
        $this->categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
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
        $sku = 'simple-out-of-stock';
        $manageStock = $this->scopeConfig->getValue(Configuration::XML_PATH_MANAGE_STOCK);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_MANAGE_STOCK, 1);
        $this->reinitConfig->reinit();

        // need to resave product to reindex it with new configuration.
        $product = $this->productRepository->get($sku);
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

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_MANAGE_STOCK, $manageStock);
        $this->reinitConfig->reinit();

        self::assertEquals(0, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testCategoryWithOutOfStockProductManageStockDisabled()
    {
        $categoryId = 2;
        $sku = 'simple-out-of-stock';
        $manageStock = $this->scopeConfig->getValue(Configuration::XML_PATH_MANAGE_STOCK);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_MANAGE_STOCK, 0);
        $this->reinitConfig->reinit();

        // need to resave product to reindex it with new configuration.
        $product = $this->productRepository->get($sku);
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

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_MANAGE_STOCK, $manageStock);
        $this->reinitConfig->reinit();

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

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testCategoryWithOutOfStockProductShowOutOfStockProduct()
    {
        $showOutOfStock = $this->scopeConfig->getValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1);
        $this->reinitConfig->reinit();

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

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $showOutOfStock);
        $this->reinitConfig->reinit();

        self::assertEquals(1, $response['category']['product_count']);
    }

    /**
     * @magentoApiDataFixture Magento/CatalogRule/_files/configurable_product.php
     */
    public function testCategoryWithConfigurableChildrenOutOfStock()
    {
        $categoryId = 2;

        $this->categoryLinkManagement->assignProductToCategories('configurable', [$categoryId]);

        foreach (['simple1', 'simple2'] as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setStockData(['is_in_stock' => 0]);
            $this->productRepository->save($product);
        }

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
    public function testCategoryWithProductNotAvailableOnWebsite()
    {
        $product = $this->productRepository->getById(333);
        $product->setWebsiteIds([]);
        $this->productRepository->save($product);

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

        self::assertEquals(0, $response['category']['product_count']);
    }
}
