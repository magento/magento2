<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class ProductUrlPathGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    protected function setUp()
    {
        $this->category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $productMethods = ['__wakeup', 'getData', 'getUrlKey', 'getName', 'formatUrlKey', 'getId'];
        $this->product = $this->getMock('Magento\Catalog\Model\Product', $productMethods, [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->categoryUrlPathGenerator = $this->getMock(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator',
            [],
            [],
            '',
            false
        );

        $this->productUrlPathGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator',
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator
            ]
        );
    }

    /**
     * @return array
     */
    public function getUrlPathDataProvider()
    {
        return [
            'path based on url key' => ['url-key', null, 'url-key'],
            'path based on product name 1' => ['', 'product-name', 'product-name'],
            'path based on product name 2' => [null, 'product-name', 'product-name'],
        ];
    }

    /**
     * @dataProvider getUrlPathDataProvider
     * @param $urlKey
     * @param $productName
     * @param $result
     */
    public function testGenerateUrlPath($urlKey, $productName, $result)
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->will($this->returnValue(null));
        $this->product->expects($this->once())->method('getUrlKey')->will($this->returnValue($urlKey));
        $this->product->expects($this->any())->method('getName')->will($this->returnValue($productName));
        $this->product->expects($this->once())->method('formatUrlKey')->will($this->returnArgument(0));

        $this->assertEquals($result, $this->productUrlPathGenerator->getUrlPath($this->product, null));
    }

    public function testGetUrlPath()
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->will($this->returnValue('url-path'));
        $this->product->expects($this->never())->method('getUrlKey');

        $this->assertEquals('url-path', $this->productUrlPathGenerator->getUrlPath($this->product, null));
    }

    public function testGetUrlPathWithCategory()
    {
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->will($this->returnValue('product-path'));
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')
            ->will($this->returnValue('category-url-path'));

        $this->assertEquals(
            'category-url-path/product-path',
            $this->productUrlPathGenerator->getUrlPath($this->product, $this->category)
        );
    }

    public function testGetUrlPathWithSuffix()
    {
        $storeId = 1;
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->will($this->returnValue('product-path'));
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $storeId)
            ->will($this->returnValue('.html'));

        $this->assertEquals(
            'product-path.html',
            $this->productUrlPathGenerator->getUrlPathWithSuffix($this->product, null)
        );
    }

    public function testGetUrlPathWithSuffixAndCategoryAnsStore()
    {
        $storeId = 1;
        $this->product->expects($this->once())->method('getData')->with('url_path')
            ->will($this->returnValue('product-path'));
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')
            ->will($this->returnValue('category-url-path'));
        $this->storeManager->expects($this->never())->method('getStore');
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $storeId)
            ->will($this->returnValue('.html'));

        $this->assertEquals(
            'category-url-path/product-path.html',
            $this->productUrlPathGenerator->getUrlPathWithSuffix($this->product, $storeId, $this->category)
        );
    }

    public function testGetCanonicalUrlPath()
    {
        $this->product->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            'catalog/product/view/id/1',
            $this->productUrlPathGenerator->getCanonicalUrlPath($this->product)
        );
    }

    public function testGetCanonicalUrlPathWithCategory()
    {
        $this->product->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            'catalog/product/view/id/1/category/1',
            $this->productUrlPathGenerator->getCanonicalUrlPath($this->product, $this->category)
        );
    }
}
