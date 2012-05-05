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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Resource_Store_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Store_Collection
     */
    protected $_collection;

    public function setUp()
    {
        $this->_collection = new Mage_Core_Model_Resource_Store_Collection();
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
        $this->assertContains('store_id > 0', (string)$this->_collection->getSelect());
    }

    /**
     * @covers Mage_Core_Model_Resource_Store_Collection::addGroupFilter
     * @covers Mage_Core_Model_Resource_Store_Collection::addIdFilter
     * @covers Mage_Core_Model_Resource_Store_Collection::addWebsiteFilter
     * @covers Mage_Core_Model_Resource_Store_Collection::addCategoryFilter
     */
    public function testAddFilters()
    {
        $this->_collection->addGroupFilter(1);
        $this->assertContains('group_id IN', (string)$this->_collection->getSelect(), 'Group filter');

        $this->_collection->addIdFilter(1);
        $this->assertContains('store_id IN', (string)$this->_collection->getSelect(), 'Id filter');

        $this->_collection->addWebsiteFilter(1);
        $this->assertContains('website_id IN', (string)$this->_collection->getSelect(), 'Website filter');

        $this->_collection->addCategoryFilter(1);
        $this->assertContains('root_category_id IN', (string)$this->_collection->getSelect(), 'Category filter');
    }

    public function testToOptionArrayHash()
    {
        $this->assertTrue(is_array($this->_collection->toOptionArray()));
        $this->assertNotEmpty($this->_collection->toOptionArray());

        $this->assertTrue(is_array($this->_collection->toOptionHash()));
        $this->assertNotEmpty($this->_collection->toOptionHash());

    }

    /**
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::addFieldToSelect
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::removeFieldFromSelect
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
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::addExpressionFieldToSelect
     */
    public function testAddExpressionFieldToSelect()
    {
        $this->_collection->addExpressionFieldToSelect('test_alias', 'SUM({{store_id}})', 'store_id');
        $this->assertContains('SUM(store_id)', (string)$this->_collection->getSelect());
        $this->assertContains('test_alias', (string)$this->_collection->getSelect());
    }

    /**
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::getAllIds
     */
    public function testGetAllIds()
    {
        $this->assertContains(Mage_Core_Model_App::ADMIN_STORE_ID, $this->_collection->getAllIds());
    }

    /**
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::getData
     */
    public function testGetData()
    {
        $this->assertNotEmpty($this->_collection->getData());
    }

    /**
     * @covers Mage_Core_Model_Resource_Db_Collection_Abstract::join
     */
    public function testJoin()
    {
        $this->_collection->join(array('w' => 'core_website'), 'main_table.website_id=w.website_id');
        $this->assertContains('core_website', (string)$this->_collection->getSelect());
    }
}
