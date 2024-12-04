<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Visitor;

class CompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_helper;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class);
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    public function testGetListUrl()
    {
        /** @var $empty \Magento\Catalog\Helper\Product\Compare */
        $empty = $this->_objectManager->create(\Magento\Catalog\Helper\Product\Compare::class);
        $this->assertStringContainsString('/catalog/product_compare/index/', $empty->getListUrl());
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
        $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
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
        $this->assertStringContainsString('/catalog/product_compare/remove/', $url);
    }

    public function testGetClearListUrl()
    {
        $this->assertStringContainsString(
            '\/catalog\/product_compare\/clear\/',
            $this->_helper->getPostDataClearList()
        );
    }

    /**
     * calculate()
     * getItemCount()
     * hasItems()
     *
     * @magentoDbIsolation disabled
     */
    #[
        Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(ProductFixture::class, ['website_ids' => [1]], as: 'product1'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$']], as: 'product2'),
        DataFixture(ProductFixture::class, ['website_ids' => ['$website2.id$']], as: 'product3'),
    ]
    public function testCalculate()
    {
        /** @var \Magento\Catalog\Model\Session $session */
        $session = $this->_objectManager->get(\Magento\Catalog\Model\Session::class);
        try {
            $session->unsCatalogCompareItemsCount();
            $this->assertFalse($this->_helper->hasItems());
            $this->assertEquals(0, $session->getCatalogCompareItemsCount());

            $visitor = $this->_objectManager->get(Visitor::class);
            $visitor->setVisitorId(1);
            $this->_populateCompareList('product1');
            $this->_populateCompareList('product2');
            $this->_helper->calculate();
            $this->assertEquals(2, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());

            $secondStore = $this->fixtures->get('store2')->getCode();
            $this->storeManager->setCurrentStore($secondStore);
            $this->_helper->calculate();
            $this->assertEquals(0, $session->getCatalogCompareItemsCount());
            $this->_populateCompareList('product3');
            $this->_helper->calculate();
            $this->assertEquals(1, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());
            $this->_populateCompareList('product2');
            $this->_helper->calculate();
            $this->assertEquals(2, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());
            $compareItems = $this->_helper->getItemCollection();
            $compareItems->clear();
            $session->unsCatalogCompareItemsCountPerWebsite();
            $this->assertFalse($this->_helper->hasItems());
            $this->assertEquals(0, $session->getCatalogCompareItemsCount());
            $this->storeManager->setCurrentStore(1);
            $this->_helper->calculate();
            $this->assertEquals(2, $session->getCatalogCompareItemsCount());
            $this->assertTrue($this->_helper->hasItems());
            $session->unsCatalogCompareItemsCount();
        } catch (\Exception $e) {
            $session->unsCatalogCompareItemsCount();
            throw $e;
        }
    }

    /**
     * @see testGetListUrl() for coverage of customer case
     */
    public function testGetItemCollection()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class,
            $this->_helper->getItemCollection()
        );
    }

    public function testSetGetAllowUsedFlat()
    {
        $this->assertTrue($this->_helper->getAllowUsedFlat());
        $this->_helper->setAllowUsedFlat(false);
        $this->assertFalse($this->_helper->getAllowUsedFlat());
    }

    protected function _testGetProductUrl($method, $expectedFullAction)
    {
        $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->setId(10);
        $url = $this->_helper->{$method}($product);
        $this->assertStringContainsString($expectedFullAction, $url);
    }

    /**
     * Add products from fixture to compare list
     *
     * @param string $sku
     */
    protected function _populateCompareList(string $sku)
    {
        $product = $this->fixtures->get($sku);
        /** @var $compareList \Magento\Catalog\Model\Product\Compare\ListCompare */
        $compareList = $this->_objectManager->create(\Magento\Catalog\Model\Product\Compare\ListCompare::class);
        $compareList->addProduct($product);
    }
}
