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

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Url::class
        );
        $this->urlPathGenerator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class
        );
    }

    public function testGetUrlInStore()
    {
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
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
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
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
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrl($product));

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setId(100);
        $this->assertContains('catalog/product/view/id/100/', $this->_model->getUrl($product));
    }
}
