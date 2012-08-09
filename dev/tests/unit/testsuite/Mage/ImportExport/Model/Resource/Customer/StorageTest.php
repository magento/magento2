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
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ImportExport_Model_Resource_Customer_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Resource_Customer_Storage
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_entityTable = 'test';

    /**
     * @var array
     */
    protected $_expectedFields = array('entity_id', 'website_id', 'email');

    protected function setUp()
    {
        $this->_model = new Mage_ImportExport_Model_Resource_Customer_Storage($this->_getModelDependencies());
        $this->_model->load();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $select = $this->getMock('Varien_Db_Select', array('from'), array(), '', false);
        $select->expects($this->any())
            ->method('from')
            ->will($this->returnCallback(array($this, 'validateFrom')));
        $customerCollection = $this->getMock('Mage_Customer_Model_Resource_Customer_Collection',
            array('load', 'removeAttributeToSelect', 'getResource', 'getSelect'), array(), '', false
        );

        $resourceStub = new Varien_Object();
        $resourceStub->setEntityTable($this->_entityTable);
        $customerCollection->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resourceStub));

        $customerCollection->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($select));

        $byPagesIterator = $this->getMock('stdClass', array('iterate'));
        $byPagesIterator->expects($this->once())
            ->method('iterate')
            ->will($this->returnCallback(array($this, 'iterate')));

        return array(
            'customer_collection'          => $customerCollection,
            'collection_by_pages_iterator' => $byPagesIterator,
            'page_size'                    => 10
        );
    }

    /**
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Varien_Data_Collection $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(Varien_Data_Collection $collection, $pageSize, array $callbacks)
    {
        foreach ($collection as $customer) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, $customer);
            }
        }
    }

    /**
     * @param string $tableName
     * @param array $fields
     */
    public function validateFrom($tableName, $fields)
    {
        $this->assertEquals($this->_entityTable, $tableName);
        $this->assertEquals($this->_expectedFields, $fields);
    }

    /**
     * @covers Mage_ImportExport_Model_Resource_Customer_Storage::load
     */
    public function testLoad()
    {
        $this->assertAttributeEquals(true, '_isCollectionLoaded', $this->_model);
    }

    /**
     * @covers Mage_ImportExport_Model_Resource_Customer_Storage::addCustomer
     */
    public function testAddCustomer()
    {
        $propertyName = '_customerIds';
        $customer = $this->_addCustomerToStorage();

        $this->assertAttributeCount(1, $propertyName, $this->_model);

        $expectedCustomerData = array(
            $customer->getWebsiteId() => $customer->getId()
        );
        $this->assertAttributeContains($expectedCustomerData, $propertyName, $this->_model);
    }

    /**
     * @covers Mage_ImportExport_Model_Resource_Customer_Storage::addCustomer
     */
    public function testGetCustomerId()
    {
        $customer = $this->_addCustomerToStorage();

        $this->assertEquals(
            $customer->getId(),
            $this->_model->getCustomerId($customer->getEmail(), $customer->getWebsiteId())
        );
        $this->assertFalse($this->_model->getCustomerId('new@test.com', $customer->getWebsiteId()));
    }

    /**
     * @return Varien_Object
     */
    protected function _addCustomerToStorage()
    {
        $customer = new Varien_Object(array(
            'id'         => 1,
            'website_id' => 1,
            'email'      => 'test@test.com'
        ));
        $this->_model->addCustomer($customer);

        return $customer;
    }
}
