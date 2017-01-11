<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Store;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\ResourceModel\Store\Collection::class
        );
    }

    public function testSetGetLoadDefault()
    {
        $this->assertFalse($this->_collection->getLoadDefault());

        $this->_collection->setLoadDefault(true);
        $this->assertTrue($this->_collection->getLoadDefault());

        $this->_collection->setLoadDefault(false);
        $this->assertFalse($this->_collection->getLoadDefault());
    }

    public function testSetWithoutDefaultFilter()
    {
        $this->_collection->setWithoutDefaultFilter();
        $quote = $this->_getQuoteIdentifierSymbol();

        $this->assertContains("{$quote}store_id{$quote} > 0", (string)$this->_collection->getSelect());
    }

    /**
     * @covers \Magento\Store\Model\ResourceModel\Store\Collection::addGroupFilter
     * @covers \Magento\Store\Model\ResourceModel\Store\Collection::addIdFilter
     * @covers \Magento\Store\Model\ResourceModel\Store\Collection::addWebsiteFilter
     * @covers \Magento\Store\Model\ResourceModel\Store\Collection::addCategoryFilter
     */
    public function testAddFilters()
    {
        $this->_collection->addGroupFilter(1);
        $quote = $this->_getQuoteIdentifierSymbol();
        $this->assertContains("{$quote}group_id{$quote} IN", (string)$this->_collection->getSelect(), 'Group filter');

        $this->_collection->addIdFilter(1);
        $this->assertContains("{$quote}store_id{$quote} IN", (string)$this->_collection->getSelect(), 'Id filter');

        $this->_collection->addWebsiteFilter(1);
        $this->assertContains(
            "{$quote}website_id{$quote} IN",
            (string)$this->_collection->getSelect(),
            'Website filter'
        );

        $this->_collection->addCategoryFilter(1);
        $this->assertContains(
            "{$quote}root_category_id{$quote} IN",
            (string)$this->_collection->getSelect(),
            'Category filter'
        );
    }

    /**
     * Get quote symbol from adapter.
     *
     * @return string
     */
    protected function _getQuoteIdentifierSymbol()
    {
        return $this->_collection->getConnection()->getQuoteIdentifierSymbol();
    }

    public function testToOptionArrayHash()
    {
        $this->assertTrue(is_array($this->_collection->toOptionArray()));
        $this->assertNotEmpty($this->_collection->toOptionArray());

        $this->assertTrue(is_array($this->_collection->toOptionHash()));
        $this->assertNotEmpty($this->_collection->toOptionHash());
    }

    /**
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::addFieldToSelect
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::removeFieldFromSelect
     */
    public function testAddRemoveFieldToSelect()
    {
        $this->_collection->addFieldToSelect(['store_id']);
        $this->assertContains('store_id', (string)$this->_collection->getSelect());
        $this->_collection->addFieldToSelect('*');
        $this->assertContains('*', (string)$this->_collection->getSelect());

        $this->_collection->addFieldToSelect('test_field', 'test_alias');
        $this->assertContains('test_field', (string)$this->_collection->getSelect());
        $this->assertContains('test_alias', (string)$this->_collection->getSelect());

        $this->_collection->removeFieldFromSelect('test_field');
        $this->_collection->addFieldToSelect('store_id');
        $this->assertNotContains('test_field', (string)$this->_collection->getSelect());
    }

    /**
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::addExpressionFieldToSelect
     */
    public function testAddExpressionFieldToSelect()
    {
        $this->_collection->addExpressionFieldToSelect('test_alias', 'SUM({{store_id}})', 'store_id');
        $this->assertContains('SUM(store_id)', (string)$this->_collection->getSelect());
        $this->assertContains('test_alias', (string)$this->_collection->getSelect());
    }

    /**
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getAllIds
     */
    public function testGetAllIds()
    {
        $this->assertContains(\Magento\Store\Model\Store::DISTRO_STORE_ID, $this->_collection->getAllIds());
    }

    /**
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getData
     */
    public function testGetData()
    {
        $this->assertNotEmpty($this->_collection->getData());
    }

    /**
     * @covers \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::join
     */
    public function testJoin()
    {
        $this->_collection->join(['w' => 'store_website'], 'main_table.website_id=w.website_id');
        $this->assertContains('store_website', (string)$this->_collection->getSelect());
    }
}
