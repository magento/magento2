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
namespace Magento\Store\Model\Resource\Store;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Resource\Store\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Store\Model\Resource\Store\Collection'
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
     * @covers \Magento\Store\Model\Resource\Store\Collection::addGroupFilter
     * @covers \Magento\Store\Model\Resource\Store\Collection::addIdFilter
     * @covers \Magento\Store\Model\Resource\Store\Collection::addWebsiteFilter
     * @covers \Magento\Store\Model\Resource\Store\Collection::addCategoryFilter
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
        /** @var \Zend_Db_Adapter_Abstract $adapter */
        $adapter = $this->_collection->getConnection();
        $quote = $adapter->getQuoteIdentifierSymbol();
        return $quote;
    }

    public function testToOptionArrayHash()
    {
        $this->assertTrue(is_array($this->_collection->toOptionArray()));
        $this->assertNotEmpty($this->_collection->toOptionArray());

        $this->assertTrue(is_array($this->_collection->toOptionHash()));
        $this->assertNotEmpty($this->_collection->toOptionHash());
    }

    /**
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::addFieldToSelect
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::removeFieldFromSelect
     */
    public function testAddRemoveFieldToSelect()
    {
        $this->_collection->addFieldToSelect(array('store_id'));
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
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::addExpressionFieldToSelect
     */
    public function testAddExpressionFieldToSelect()
    {
        $this->_collection->addExpressionFieldToSelect('test_alias', 'SUM({{store_id}})', 'store_id');
        $this->assertContains('SUM(store_id)', (string)$this->_collection->getSelect());
        $this->assertContains('test_alias', (string)$this->_collection->getSelect());
    }

    /**
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::getAllIds
     */
    public function testGetAllIds()
    {
        $this->assertContains(\Magento\Store\Model\Store::DISTRO_STORE_ID, $this->_collection->getAllIds());
    }

    /**
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::getData
     */
    public function testGetData()
    {
        $this->assertNotEmpty($this->_collection->getData());
    }

    /**
     * @covers \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection::join
     */
    public function testJoin()
    {
        $this->_collection->join(array('w' => 'store_website'), 'main_table.website_id=w.website_id');
        $this->assertContains('store_website', (string)$this->_collection->getSelect());
    }
}
