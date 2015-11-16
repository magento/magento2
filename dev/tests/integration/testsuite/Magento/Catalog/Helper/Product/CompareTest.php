<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

class CompareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testGetListUrl()
    {
        /** @var $empty \Magento\Catalog\Helper\Product\Compare */
        $empty = $this->_objectManager->create('Magento\Catalog\Helper\Product\Compare');
        $this->assertContains('/catalog/product_compare/index/', $empty->getListUrl());

        $this->_populateCompareList();
        $this->assertRegExp('#/catalog/product_compare/index/items/(?:10,11|11,10)/#', $this->_helper->getListUrl());
    }

    public function testGetAddUrl()
    {
        $this->_testGetProductUrl('getAddUrl', '/catalog/product_compare/add/');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetAddToWishlistParams()
    {
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $product->setId(10);
        $json = $this->_helper->getAddToWishlistParams($product);
        $params = (array)json_decode($json);
        $data = (array)$params['data'];
        $this->assertEquals('10', $data['product']);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    public function testGetAddToCartUrl()
    {
        $this->_testGetProductUrl('getAddToCartUrl', '/checkout/cart/add/');
    }

    public function testGetRemoveUrl()
    {
        $url = $this->_helper->getRemoveUrl();
        $this->assertContains('/catalog/product_compare/remove/', $url);
    }

    public function testGetClearListUrl()
    {
        $this->assertContains('\/catalog\/product_compare\/clear\/', $this->_helper->getPostDataClearList());
    }

    /**
     * @see testGetListUrl() for coverage of customer case
     */
    public function testGetItemCollection()
    {
        $this->assertInstanceOf(
            'Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection',
            $this->_helper->getItemCollection()
        );
    }

    /**
     * calculate()
     * getItemCount()
     * hasItems()
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCalculate()
    {
        /** @var \Magento\Catalog\Model\Session $session */
        $session = $this->_objectManager->get('Magento\Catalog\Model\Session');
        try {
            $session->unsCatalogCompareItemsCount();
            $this->assertFalse($this->_helper->hasItems());
            $this->assertEquals(0, $session->getCatalogCompareItemsCount());

            $this->_populateCompareList();
            $this->_helper->calculate();
            $this->assertEquals(2, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());

            $session->unsCatalogCompareItemsCount();
        } catch (\Exception $e) {
            $session->unsCatalogCompareItemsCount();
            throw $e;
        }
    }

    public function testSetGetAllowUsedFlat()
    {
        $this->assertTrue($this->_helper->getAllowUsedFlat());
        $this->_helper->setAllowUsedFlat(false);
        $this->assertFalse($this->_helper->getAllowUsedFlat());
    }

    protected function _testGetProductUrl($method, $expectedFullAction)
    {
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $product->setId(10);
        $url = $this->_helper->{$method}($product);
        $this->assertContains($expectedFullAction, $url);
    }

    /**
     * Add products from fixture to compare list
     */
    protected function _populateCompareList()
    {
        $productOne = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $productTwo = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $productOne->load(10);
        $productTwo->load(11);
        /** @var $compareList \Magento\Catalog\Model\Product\Compare\ListCompare */
        $compareList = $this->_objectManager->create('Magento\Catalog\Model\Product\Compare\ListCompare');
        $compareList->addProduct($productOne)->addProduct($productTwo);
    }
}
