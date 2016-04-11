<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

/**
 * Test class for \Magento\Catalog\Model\Product\Url.
 *
 * @magentoDataFixture Magento/Catalog/_files/url_rewrites.php
 * @magentoAppArea frontend
 */
class UrlTest extends \PHPUnit_Framework_TestCase
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
            'Magento\Catalog\Model\Product\Url'
        );
        $this->urlPathGenerator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator'
        );
    }

    public function testGetUrlInStore()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrlInStore($product));
    }

    public function testGetProductUrl()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
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
            'Magento\Catalog\Model\Product'
        );
        $product->setUrlPath('product.html');

        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category',
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
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        $this->assertStringEndsWith('simple-product.html', $this->_model->getUrl($product));

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setId(100);
        $this->assertContains('catalog/product/view/id/100/', $this->_model->getUrl($product));
    }
}
