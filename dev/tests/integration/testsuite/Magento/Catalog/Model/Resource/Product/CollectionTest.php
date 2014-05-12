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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product;

use Magento\Customer\Service\V1\CustomerGroupServiceInterface;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Collection'
        );
    }

    /**
     * @dataProvider setOrderDataProvider
     */
    public function testSetOrder($order, $expectedOrder)
    {
        $this->_collection->setOrder($order);
        $this->_collection->load();
        // perform real SQL query

        $selectOrder = $this->_collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        foreach ($expectedOrder as $field) {
            $orderBy = array_shift($selectOrder);
            $this->assertArrayHasKey(0, $orderBy);
            $this->assertTrue(
                false !== strpos($orderBy[0], $field),
                'Ordering by same column more than once is restricted by multiple RDBMS requirements.'
            );
        }
    }

    public function setOrderDataProvider()
    {
        return array(
            array(array('sku', 'sku'), array('sku')),
            array(array('sku', 'name', 'sku'), array('name', 'sku'))
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/Resource/_files/url_rewrites.php
     * @magentoConfigFixture current_store catalog/seo/product_use_categories 1
     */
    public function testAddUrlRewrite()
    {
        $this->_collection->addUrlRewrite(3);
        $expectedResult = array(
            'category-1/url-key.html',
            'category-1/url-key-1.html',
            'category-1/url-key-2.html',
            'category-1/url-key-5.html',
            'category-1/url-key-1000.html',
            'category-1/url-key-999.html',
            'category-1/url-key-asdf.html'
        );
        $this->assertEquals($expectedResult, $this->_collection->getColumnValues('request_path'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/Resource/_files/product_simple.php
     */
    public function testAddTierPriceData()
    {
        $this->_collection->setFlag('tier_price_added', false);
        $this->_collection->addIdFilter(2);
        $this->assertInstanceOf(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            $this->_collection->addTierPriceData()
        );
        $tierPrice = $this->_collection->getFirstItem()->getDataByKey('tier_price');
        $this->assertEquals(CustomerGroupServiceInterface::NOT_LOGGED_IN_ID, current($tierPrice)['cust_group']);
        $this->assertEquals(CustomerGroupServiceInterface::CUST_GROUP_ALL, next($tierPrice)['cust_group']);
        $this->assertTrue($this->_collection->getFlag('tier_price_added'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore.php
     * @magentoConfigFixture current_store customer/account_share/scope 0
     */
    public function testStoreDependentAttributeValue()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->create('Magento\Store\Model\Store');
        $store->load('fixturestore', 'code');

        $product = $this->_collection
            ->addAttributeToSelect('name')
            ->load()
            ->getFirstItem();
        $this->assertEquals('Simple Product One', $product->getName());

        $product = $this->_collection
            ->clear()
            ->addAttributeToSelect('name')
            ->addStoreFilter($store)
            ->load()
            ->getFirstItem();
        $this->assertEquals("StoreTitle", $product->getName());
    }
}
