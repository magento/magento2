<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

/**
 * Test class for \Magento\Catalog\Model\Product\Url.
 *
 * @magentoDataFixture Magento/Catalog/_files/url_rewrites.php
 * @magentoAppArea frontend
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $_model;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $urlPathGenerator;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Url::class
        );
        $this->urlPathGenerator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class
        );

        /** @var \Magento\Framework\Registry $registry */
        $this->registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Registry::class
        );
    }

    public function testGetUrlInStore()
    {
        $product = $this->getProduct();
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrlInStore($product));
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default_store web/unsecure/base_url http://sample.com/
     * @magentoConfigFixture default_store web/unsecure/base_link_url http://sample.com/
     * @magentoConfigFixture fixturestore_store web/unsecure/base_url http://sample-second.com/
     * @magentoConfigFixture fixturestore_store web/unsecure/base_link_url http://sample-second.com/
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore.php
     * @dataProvider getUrlsWithSecondStoreProvider
     * @magentoAppArea adminhtml
     */
    public function testGetUrlInStoreWithSecondStore($storeCode, $expectedProductUrl)
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        /** @var \Magento\Store\Model\Store $store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\Store::class);
        $store->load($storeCode, 'code');
        /** @var \Magento\Store\Model\Store $store */

        $product = $repository->get('simple');

        $this->assertEquals(
            $expectedProductUrl,
            $this->_model->getUrlInStore($product, ['_scope' => $store->getId(), '_nosid' => true])
        );
    }

    /**
     * @return array
     */
    public function getUrlsWithSecondStoreProvider()
    {
        return [
           'case1' => ['fixturestore', 'http://sample-second.com/index.php/simple-product-one.html'],
           'case2' => ['default', 'http://sample.com/index.php/simple-product-one.html']
        ];
    }

    public function testGetProductUrl()
    {
        $product = $this->getProduct();
        $this->assertStringEndsWith('simple-product.html', $this->_model->getProductUrl($product));
    }

    public function testFormatUrlKey()
    {
        $this->assertEquals('abc-test', $this->_model->formatUrlKey('AbC#-$^test'));
    }

    public function testGetUrlPath()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setUrlPath('product.html');

        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class,
            ['data' => ['url_path' => 'category', 'entity_id' => 5, 'path_ids' => [2, 3, 5]]]
        );
        $category->setOrigData();

        $this->assertEquals('product.html', $this->urlPathGenerator->getUrlPath($product));
        $this->assertEquals('category/product.html', $this->urlPathGenerator->getUrlPath($product, $category));
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetUrl()
    {
        $product = $this->getProduct();
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrl($product));

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setId(100);
        $this->assertContains('catalog/product/view/id/100/', $this->_model->getUrl($product));
    }

    /**
     * @magentoAppArea frontend
     *
     * @magentoDataFixture Magento/Catalog/_files/config_use_category_in_url.php
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites.php
     *
     * @return void
     */
    public function testGetUrlWithCategoryInUrl()
    {
        $product = $this->getProduct();
        $category = $this->getCategory($product);

        $this->assertStringEndsWith(
            $category->getUrlKey() . '/' . $product->getUrlKey() . '.html',
            $this->_model->getUrl($product)
        );
    }

    /**
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testGetUrlWithoutCategoryInUrl()
    {
        $product = $this->getProduct();
        $category = $this->getCategory($product);

        $url = $this->_model->getUrl($product);

        $this->assertStringEndsWith($product->getUrlKey() . '.html', $url);
        $this->assertNotContains($url, $category->getUrlKey());
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct(): \Magento\Catalog\Api\Data\ProductInterface
    {
        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );

        return $productRepository->get('simple');
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    private function getCategory(
        \Magento\Catalog\Api\Data\ProductInterface $product
    ): \Magento\Catalog\Api\Data\CategoryInterface {
        /** @var \Magento\Catalog\Model\CategoryRepository $categoryRepository */
        $categoryRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\CategoryRepository::class
        );

        $categoryId = $product->getCategoryIds()[0];

        $category = $categoryRepository->get($categoryId);
        $this->registry->unregister('current_category');
        $this->registry->register('current_category', $category);

        return $category;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('current_category');

        parent::tearDown();
    }
}
