<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Checks product availability on storefront by url rewrite
 *
 * @magentoDbIsolation enabled
 */
class ProductUrlRewriteTest extends AbstractController
{
    /** @var ScopeConfigInterface */
    private $config;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var string */
    private $urlSuffix;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->_objectManager->get(ScopeConfigInterface::class);
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->registry = $this->_objectManager->get(Registry::class);
        $this->categoryRepository = $this->_objectManager->create(CategoryRepositoryInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->urlSuffix = $this->config->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testProductUrlRewrite(): void
    {
        $product = $this->productRepository->get('simple2');
        $url = $this->prepareUrl($product->getUrlKey());
        $this->dispatch($url);

        $this->assertProductIsVisible($product);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @return void
     */
    public function testCategoryProductUrlRewrite(): void
    {
        $category = $this->categoryRepository->get(333);
        $product = $this->productRepository->get('simple333');
        $url = $this->prepareUrl($category->getUrlKey(), false) . $this->prepareUrl($product->getUrlKey());
        $this->dispatch($url);

        $this->assertProductIsVisible($product);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testProductRedirect(): void
    {
        $product = $this->productRepository->get('simple2');
        $oldUrl = $this->prepareUrl($product->getUrlKey());
        $data = [
            'url_key' => 'new-url-key',
            'url_key_create_redirect' => $product->getUrlKey(),
            'save_rewrites_history' => true,
        ];
        $this->updateProduct($product, $data);
        $this->dispatch($oldUrl);

        $this->assertRedirect($this->stringContains($this->prepareUrl('new-url-key')));
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testMultistoreProductUrlRewrite(): void
    {
        $currentStore = $this->storeManager->getStore();
        $product = $this->productRepository->get('simple2');
        $firstStoreUrl = $this->prepareUrl($product->getUrlKey());
        $secondStoreId = $this->storeManager->getStore('fixturestore')->getId();
        $this->storeManager->setCurrentStore($secondStoreId);

        try {
            $product = $this->updateProduct($product, ['url_key' => 'second-store-url-key']);
            $this->assertEquals('second-store-url-key', $product->getUrlKey());
            $secondStoreUrl = $this->prepareUrl($product->getUrlKey());

            $this->dispatch($secondStoreUrl);
            $this->assertProductIsVisible($product);
            $this->cleanUpCachedObjects();
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }

        $this->dispatch($firstStoreUrl);
        $this->assertProductIsVisible($product);
    }

    /**
     * Update product
     *
     * @param ProductInterface $product
     * @param array $data
     * @return ProductInterface
     */
    private function updateProduct(ProductInterface $product, array $data): ProductInterface
    {
        $product->addData($data);

        return $this->productRepository->save($product);
    }

    /**
     * Clean up cached objects
     *
     * @return void
     */
    private function cleanUpCachedObjects(): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->_objectManager->removeSharedInstance(Request::class);
        $this->_objectManager->removeSharedInstance(Response::class);
        $this->_response = null;
        $this->_request = null;
    }

    /**
     * Prepare url to dispatch
     *
     * @param string $urlKey
     * @param bool $addSuffix
     * @return string
     */
    private function prepareUrl(string $urlKey, bool $addSuffix = true): string
    {
        $url = $addSuffix ? '/' . $urlKey . $this->urlSuffix : '/' . $urlKey;

        return $url;
    }

    /**
     * Assert that product is available in storefront
     *
     * @param ProductInterface $product
     * @return void
     */
    private function assertProductIsVisible(ProductInterface $product): void
    {
        $this->assertEquals(
            Response::STATUS_CODE_200,
            $this->getResponse()->getHttpResponseCode(),
            'Wrong response code is returned'
        );
        $currentProduct = $this->registry->registry('current_product');
        $this->assertNotNull($currentProduct);
        $this->assertEquals(
            $product->getSku(),
            $currentProduct->getSku(),
            'Wrong product is registered'
        );
    }
}
