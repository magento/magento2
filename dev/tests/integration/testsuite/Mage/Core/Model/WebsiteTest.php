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

class Mage_Core_Model_WebsiteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Website
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Website();
        $this->_model->load(1);
    }

    public function testLoad()
    {
        /* Test loading by id */
        $this->assertEquals(1, $this->_model->getId());
        $this->assertEquals('base', $this->_model->getCode());
        $this->assertEquals('Main Website', $this->_model->getName());

        /* Test loading by code */
        $this->_model->load('admin');
        $this->assertEquals(0, $this->_model->getId());
        $this->assertEquals('admin', $this->_model->getCode());
        $this->assertEquals('Admin', $this->_model->getName());
    }

    /**
     * @covers Mage_Core_Model_Website::setGroups
     * @covers Mage_Core_Model_Website::setStores
     * @covers Mage_Core_Model_Website::getStores
     */
    public function testSetGroupsAndStores()
    {
        /* Groups */
        $expectedGroup = new Mage_Core_Model_Store_Group();
        $expectedGroup->setId(123);
        $this->_model->setDefaultGroupId($expectedGroup->getId());
        $this->_model->setGroups(array($expectedGroup));

        $groups = $this->_model->getGroups();
        $this->assertSame($expectedGroup, reset($groups));

        /* Stores */
        $expectedStore = new Mage_Core_Model_Store();
        $expectedStore->setId(456);
        $expectedGroup->setDefaultStoreId($expectedStore->getId());
        $this->_model->setStores(array($expectedStore));

        $stores = $this->_model->getStores();
        $this->assertSame($expectedStore, reset($stores));
    }

    public function testGetGroups()
    {
        $groups = $this->_model->getGroups();
        $this->assertEquals(array(1), array_keys($groups));
        $this->assertInstanceOf('Mage_Core_Model_Store_Group', $groups[1]);
        $this->assertEquals(1, $groups[1]->getId());
    }

    public function testGetGroupIds()
    {
        $this->assertEquals(array(1 => 1), $this->_model->getGroupIds());
    }

    public function testGetGroupsCount()
    {
        $this->assertEquals(1, $this->_model->getGroupsCount());
    }

    public function testGetDefaultGroup()
    {
        $defaultGroup = $this->_model->getDefaultGroup();
        $this->assertInstanceOf('Mage_Core_Model_Store_Group', $defaultGroup);
        $this->assertEquals(1, $defaultGroup->getId());

        $this->_model->setDefaultGroupId(null);
        $this->assertFalse($this->_model->getDefaultGroup());
    }

    public function testGetStores()
    {
        $stores = $this->_model->getStores();
        $this->assertEquals(array(1), array_keys($stores));
        $this->assertInstanceOf('Mage_Core_Model_Store', $stores[1]);
        $this->assertEquals(1, $stores[1]->getId());
    }

    public function testGetStoreIds()
    {
        $this->assertEquals(array(1 => 1), $this->_model->getStoreIds());
    }

    public function testGetStoreCodes()
    {
        $this->assertEquals(array(1 => 'default'), $this->_model->getStoreCodes());
    }

    public function testGetStoresCount()
    {
        $this->assertEquals(1, $this->_model->getStoresCount());
    }

    public function testIsCanDelete()
    {
        $this->assertFalse($this->_model->isCanDelete());
        $this->_model->isReadOnly(true);
        $this->assertFalse($this->_model->isCanDelete());
    }

    public function testGetWebsiteGroupStore()
    {
        $this->assertEquals('1--', $this->_model->getWebsiteGroupStore());
        $this->_model->setGroupId(123);
        $this->_model->setStoreId(456);
        $this->assertEquals('1-123-456', $this->_model->getWebsiteGroupStore());
    }

    public function testGetDefaultGroupId()
    {
        $this->assertEquals(1, $this->_model->getDefaultGroupId());
    }

    public function testGetBaseCurrency()
    {
        $currency = $this->_model->getBaseCurrency();
        $this->assertInstanceOf('Mage_Directory_Model_Currency', $currency);
        $this->assertEquals('USD', $currency->getCode());
    }

    public function testGetDefaultStore()
    {
        $defaultStore = $this->_model->getDefaultStore();
        $this->assertInstanceOf('Mage_Core_Model_Store', $defaultStore);
        $this->assertEquals(1, $defaultStore->getId());
    }

    public function testGetDefaultStoresSelect()
    {
        $this->assertInstanceOf('Varien_Db_Select', $this->_model->getDefaultStoresSelect());
    }

    public function testIsReadonly()
    {
        $this->assertFalse($this->_model->isReadOnly());
        $this->_model->isReadOnly(true);
        $this->assertTrue($this->_model->isReadOnly());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testCRUD()
    {
        $this->_model->setData(
            array(
                'code'              => 'test_website',
                'name'              => 'test website',
                'default_group_id'  => 1,
            )
        );

        /* emulate admin store */
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $crud = new Magento_Test_Entity($this->_model, array('name' => 'new name'));
        $crud->testCrud();
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection()
            ->joinGroupAndStore()
            ->addIdFilter(1);
        $this->assertEquals(1, count($collection->getItems()));
    }
}
