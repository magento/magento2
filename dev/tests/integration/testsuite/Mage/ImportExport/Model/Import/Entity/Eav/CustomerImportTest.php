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
 * @category    Mage
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for customer V2 import model
 */
class Mage_ImportExport_Model_Import_Entity_Eav_CustomerImportTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var Mage_ImportExport_Model_Import_Entity_Eav_Customer
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = new Mage_ImportExport_Model_Import_Entity_Eav_Customer();
    }

    protected function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Test importData() method
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_importData
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_prepareDataForUpdate
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_saveCustomerEntity
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_saveCustomerAttributes
     *
     * @magentoDataFixture Mage/ImportExport/_files/customer.php
     */
    public function testImportData()
    {
        // 3 customers will be imported.
        // 1 of this customers is already exist, but its first and last name were changed in file
        $expectAddedCustomers = 5;
        $source = new Mage_ImportExport_Model_Import_Adapter_Csv(__DIR__ . '/_files/customers_to_import.csv');

        /** @var $customersCollection Mage_Customer_Model_Resource_Customer_Collection */
        $customersCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');
        $customersCollection->addAttributeToSelect('firstname', 'inner')
            ->addAttributeToSelect('lastname', 'inner');

        $existCustomersCount = count($customersCollection->load());

        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model->setParameters(
                array(
                    'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE
                )
            )
            ->setSource($source)
            ->isDataValid();

        $this->_model->importData();

        $customers = $customersCollection->getItems();

        $addedCustomers = count($customers) - $existCustomersCount;

        $this->assertEquals($expectAddedCustomers, $addedCustomers, 'Added unexpected amount of customers');

        $existingCustomer = Mage::registry('_fixture/Mage_ImportExport_Customer');

        $updatedCustomer = $customers[$existingCustomer->getId()];

        $this->assertNotEquals(
            $existingCustomer->getFirstname(),
            $updatedCustomer->getFirstname(),
            'Firstname must be changed'
        );

        $this->assertNotEquals(
            $existingCustomer->getLastname(),
            $updatedCustomer->getLastname(),
            'Lastname must be changed'
        );

        $this->assertNotEquals(
            $existingCustomer->getCreatedAt(),
            $updatedCustomer->getCreatedAt(),
            'Creation date must be changed'
        );
    }

    /**
     * Test importData() method (delete behavior)
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_importData
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::_deleteCustomers
     *
     * @magentoDataFixture Mage/ImportExport/_files/customers.php
     */
    public function testDeleteData()
    {
        $source = new Mage_ImportExport_Model_Import_Adapter_Csv(__DIR__ . '/_files/customers_to_import.csv');

        /** @var $customerCollection Mage_Customer_Model_Resource_Customer_Collection */
        $customerCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');
        $this->assertEquals(3, $customerCollection->count(), 'Count of existing customers are invalid');

        $this->_model->setParameters(
                array(
                    'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
                )
            )
            ->setSource($source)
            ->isDataValid();

        $this->_model->importData();

        $customerCollection->resetData();
        $customerCollection->clear();
        $this->assertEmpty($customerCollection->count(), 'Customers were not imported');
    }

    /**
     * Test entity type code value
     *
     * @covers Mage_ImportExport_Model_Import_Entity_Eav_Customer::getAttributeCollection
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer', $this->_model->getEntityTypeCode());
    }
}
