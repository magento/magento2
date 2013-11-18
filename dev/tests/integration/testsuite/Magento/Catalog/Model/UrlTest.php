<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model;

/**
 * Test class for \Magento\Catalog\Model\Url.
 *
 * @magentoDataFixture Magento/Catalog/_files/url_rewrites.php
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Url
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Url');
    }

    /**
     * Retrieve loaded url rewrite
     *
     * @param string $idPath
     * @return \Magento\Core\Model\Url\Rewrite
     */
    protected function _loadRewrite($idPath)
    {
        /** @var $rewrite \Magento\Core\Model\Url\Rewrite */
        $rewrite = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Url\Rewrite');
        $rewrite->loadByIdPath($idPath);
        return $rewrite;
    }

    public function testGetStores()
    {
        $stores = $this->_model->getStores();
        $this->assertArrayHasKey(1, $stores); /* Current store identifier */
    }

    public function testGetResource()
    {
        $resource = $this->_model->getResource();
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Url', $resource);
        $this->assertSame($resource, $this->_model->getResource());
    }

    public function testGetCategoryModel()
    {
        $this->assertInstanceOf('Magento\Catalog\Model\Category', $this->_model->getCategoryModel());
    }

    public function testGetProductModel()
    {
        $this->assertInstanceOf('Magento\Catalog\Model\Product', $this->_model->getProductModel());
    }

    public function testGetStoreRootCategory()
    {
        $root = $this->_model->getStoreRootCategory(1);
        $this->assertNotEmpty($root);
        $this->assertInstanceOf('Magento\Object', $root);
        $this->assertEquals(2, $root->getId());
        $this->assertEquals(1, $root->getParentId());
    }

    public function testSetGetShouldSaveRewritesHistory()
    {
        $this->assertTrue($this->_model->getShouldSaveRewritesHistory()); /* default value */
        $this->_model->setShouldSaveRewritesHistory(false);
        $this->assertFalse($this->_model->getShouldSaveRewritesHistory());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites_invalid.php
     */
    public function testRefreshRewrites()
    {
        $this->assertNotEmpty($this->_loadRewrite('product/1/4')->getId());
        $this->assertInstanceOf('Magento\Catalog\Model\Url', $this->_model->refreshRewrites());
        $this->assertEmpty($this->_loadRewrite('product/1/4')->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites_invalid.php
     */
    public function testRefreshCategoryRewrite()
    {
        $this->assertNotEmpty($this->_loadRewrite('product/1/4')->getId());
        $this->_model->refreshCategoryRewrite(4);
        $this->assertEmpty($this->_loadRewrite('product/1/4')->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites_invalid.php
     */
    public function testRefreshProductRewrite()
    {
        $this->assertNotEmpty($this->_loadRewrite('product/1/4')->getId());
        $this->_model->refreshProductRewrite(1);
        $this->assertEmpty($this->_loadRewrite('product/1/4')->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites_invalid.php
     */
    public function testRefreshProductRewrites()
    {
        $this->assertNotEmpty($this->_loadRewrite('product/1/4')->getId());
        $this->_model->refreshProductRewrites(1);

        $this->markTestIncomplete('Rewrite was not removed after refresh, method responsibility is not clear.');
        $this->assertEmpty($this->_loadRewrite('product/1/4')->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/url_rewrites_invalid.php
     */
    public function testClearStoreInvalidRewrites()
    {
        $this->assertNotEmpty($this->_loadRewrite('product/1/5')->getId());
        $this->_model->clearStoreInvalidRewrites();
        $this->assertEmpty($this->_loadRewrite('product/1/5')->getId());
    }

    public function testGetUnusedPath()
    {
        $this->assertEquals(
            'simple-product-1.html',
            $this->_model->getUnusedPath(1, 'simple-product.html', 'product/2', 'simple-product')
        );

        $this->markTestIncomplete('Bug MAGETWO-144');

        $this->assertEquals('category-3.html', $this->_model->getUnusedPath(1, 'category-2.html', 'category/5',
            'category-2'));
    }

    public function testGetProductUrlSuffix()
    {
        $this->assertEquals('.html', $this->_model->getProductUrlSuffix(1));
    }

    public function testGetCategoryUrlSuffix()
    {
        $this->assertEquals('.html', $this->_model->getCategoryUrlSuffix(1));
    }

    public function testGetProductRequestPath()
    {
        $product = new \Magento\Object();
        $product->setName('test product')
            ->setId(uniqid());

        $category = new \Magento\Object();
        $category->setName('test category')
            ->setId(uniqid())
            ->setLevel(2)
            ->setUrlPath('test/category');

        $this->assertEquals(
            'test/category/test-product.html',
            $this->_model->getProductRequestPath($product, $category)
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     */
    public function testGeneratePathDefault()
    {
        $this->_model->generatePath();
    }

    public function generatePathDataProvider()
    {
        $product = new \Magento\Object();
        $product->setName('test product')
            ->setId(111);

        $category = new \Magento\Object();
        $category->setName('test category')
            ->setId(999)
            ->setLevel(2)
            ->setUrlPath('test/category')
            ->setParentId(3);

        return array(
            array('target', $product, null, null, 'catalog/product/view/id/111'),
            array('target', null, $category, null, 'catalog/category/view/id/999'),
            array('id', $product, null, null, 'product/111'),
            array('id', null, $category, null, 'category/999'),
            array('request', $product, $category, null, 'test/category/test-product.html'),
            array('request', null, $category, null, 'category-1/test-category.html'),
        );
    }

    /**
     * @dataProvider generatePathDataProvider
     */
    public function testGeneratePath($type, $product, $category, $parentPath, $result)
    {
        $this->assertEquals($result, $this->_model->generatePath($type, $product, $category, $parentPath));
    }

    public function testGenerateUniqueIdPath()
    {
        $path = $this->_model->generateUniqueIdPath();
        $this->assertNotEmpty($path);
        $this->assertContains('_', $path);
    }
}
