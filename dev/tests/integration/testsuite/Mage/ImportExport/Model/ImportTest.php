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
 * Tests for Import model
 *
 * @magentoDataFixture Mage/ImportExport/_files/import_data.php
 */
class Mage_ImportExport_Model_ImportTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var Mage_ImportExport_Model_Import
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        $this->_model = new Mage_ImportExport_Model_Import();
    }

    protected function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Test import from import data storage.
     * Covers _getEntityAdapter() in case when entity adapter was successfully returned
     *
     * @covers Mage_ImportExport_Model_Import::_getEntityAdapter
     */
    public function testImportSource()
    {
        /** @var $customersCollection Mage_Customer_Model_Resource_Customer_Collection */
        $customersCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');

        $existCustomersCount = count($customersCollection->load());

        $customersCollection->resetData();
        $customersCollection->clear();

        $this->_model->importSource();

        $customers = $customersCollection->getItems();

        $addedCustomers = count($customers) - $existCustomersCount;

        $this->assertGreaterThan($existCustomersCount, $addedCustomers);
    }

    /**
     * Test _getEntityAdapter() through validateSource() method in case when entity was not set
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Entity is unknown
     */
    public function testGetEntityAdapterEntityIsNotSet()
    {
        $this->_model->validateSource('');
    }

    /**
     * Test _getEntityAdapter() through validateSource() method
     * in case when not valid customer entity subtype was passed
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid entity
     */
    public function testGetEntityAdapterInvalidCustomerSubtype()
    {
        $this->_model->setEntitySubtype(microtime());

        $this->_model->validateSource('');
    }

    /**
     * Test _getEntityAdapter() through validateSource() method
     * in case when not valid customer entity model was set in config
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid entity model
     *
     * @magentoConfigFixture global/importexport/import_customer_entities/customer_address/model_token Varien_Image
     *
     */
    public function testGetEntityAdapterInvalidCustomerEntityModel()
    {
        $addressesImport = new Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address();

        $this->_model->setEntitySubtype($addressesImport->getEntityTypeCode());

        $this->_model->validateSource('');
    }

    // @codingStandardsIgnoreStart
    /**
     * Test _getEntityAdapter() through validateSource() method
     * in case when in config was set customer entity model which not
     * extends Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address
     *
     * @magentoConfigFixture global/importexport/import_customer_entities/customer_address/model_token Mage_ImportExport_Model_Import_Entity_Customer
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Entity adapter object must be an instance of Mage_ImportExport_Model_Import_Entity_V2_Abstract
     */
    // @codingStandardsIgnoreEnd
    public function testGetEntityAdapterInvalidCustomerEntityObject()
    {
        $addressesImport = new Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address();

        $this->_model->setEntitySubtype($addressesImport->getEntityTypeCode());

        $this->_model->validateSource('');
    }
}
