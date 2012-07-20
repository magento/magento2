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

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address
 */
class Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * Customer address entity adapter mock
     *
     * @var Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = array(
        1 => 'website1',
        2 => 'website2',
    );

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = array(
        'country_id' => array(
            'id'          => 1,
            'code'        => 'country_id',
            'table'       => '',
            'is_required' => true,
            'is_static'   => false,
            'rules'       => null,
            'type'        => 'select',
            'options'     => null
        ),
    );

    /**
     * Customers array
     *
     * @var array
     */
    protected $_customers = array(
        array(
            'id'         => 1,
            'email'      => 'test1@email.com',
            'website_id' => 1
        ),
        array(
            'id'         => 2,
            'email'      => 'test2@email.com',
            'website_id' => 2
        ),
    );

    /**
     * Customer addresses array
     *
     * @var array
     */
    protected $_addresses = array(
        1 => array(1)
    );

    /**
     * Customers array
     *
     * @var array
     */
    protected $_regions = array(
        array(
            'id'           => 1,
            'country_id'   => 'c1',
            'code'         => 'code1',
            'default_name' => 'region1',
        ),
        array(
            'id'           => 2,
            'country_id'   => 'c1',
            'code'         => 'code2',
            'default_name' => 'region2',
        ),
    );

    /**
     * Available behaviours
     *
     * @var array
     */
    protected $_availableBehaviors = array(
        Mage_ImportExport_Model_Import::BEHAVIOR_V2_ADD_UPDATE,
        Mage_ImportExport_Model_Import::BEHAVIOR_V2_DELETE,
        Mage_ImportExport_Model_Import::BEHAVIOR_V2_CUSTOM,
    );

    /**
     * Customer behaviours parameters
     *
     * @var array
     */
    protected $_customBehaviour = array(
        'update_id' => 1,
        'delete_id' => 2,
    );


    /**
     * Init entity adapter model
     */
    public function setUp()
    {
        parent::setUp();

        $this->_model = $this->_getModelMock();
    }

    /**
     * Unset entity adapter model
     */
    public function tearDown()
    {
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Create mock for customer address model class (for testInitCountryRegions() method)
     *
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMockForTestInitCountryRegions()
    {
        $regionCollection = new Varien_Data_Collection();
        foreach ($this->_regions as $region) {
            $regionCollection->addItem(new Varien_Object($region));
        }

        $modelMock = $this->getMock('Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address',
            array('_getRegionCollection'), array(), '', false, true, true
        );

        $modelMock->expects($this->any())
            ->method('_getRegionCollection')
            ->will($this->returnValue($regionCollection));

        return $modelMock;
    }


    /**
     * Create mock for custom behavior test
     *
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMockForTestImportDataWithCustomBehaviour()
    {
        // input data
        $customBehaviorRows = array(
             array(
                Mage_ImportExport_Model_Import_Entity_V2_Abstract::COLUMN_ACTION => 'update',
                Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_ADDRESS_ID
                    => $this->_customBehaviour['update_id'],
            ),
            array(
                Mage_ImportExport_Model_Import_Entity_V2_Abstract::COLUMN_ACTION
                    => Mage_ImportExport_Model_Import_Entity_V2_Abstract::COLUMN_ACTION_VALUE_DELETE,
                Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_ADDRESS_ID
                    => $this->_customBehaviour['delete_id'],
            ),
        );
        $updateResult = array(
            'entity_row' => $this->_customBehaviour['update_id'],
            'attributes' => array(),
            'defaults'   => array(),
        );

        // entity adapter mock
        $modelMock = $this->getMock(
            'Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address',
            array(
                'validateRow',
                '_prepareDataForUpdate',
                '_saveAddressEntities',
                '_saveAddressAttributes',
                '_saveCustomerDefaults',
                '_deleteAddressEntities',
                '_mergeEntityAttributes',
            ),
            array(),
            '',
            false,
            true,
            true
        );

        $availableBehaviors = new ReflectionProperty($modelMock, '_availableBehaviors');
        $availableBehaviors->setAccessible(true);
        $availableBehaviors->setValue($modelMock, $this->_availableBehaviors);

        // mock to imitate data source model
        $dataSourceMock = $this->getMock(
            'Mage_ImportExport_Model_Resource_Import_Data',
            array('getNextBunch'),
            array(),
            '',
            false
        );
        $dataSourceMock->expects($this->at(0))
            ->method('getNextBunch')
            ->will($this->returnValue($customBehaviorRows));
        $dataSourceMock->expects($this->at(1))
            ->method('getNextBunch')
            ->will($this->returnValue(null));

        $dataSourceModel = new ReflectionProperty(
            'Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address',
            '_dataSourceModel'
        );
        $dataSourceModel->setAccessible(true);
        $dataSourceModel->setValue($modelMock, $dataSourceMock);

        // mock expects for entity adapter
        $modelMock->expects($this->any())
            ->method('validateRow')
            ->will($this->returnValue(true));

        $modelMock->expects($this->any())
            ->method('_prepareDataForUpdate')
            ->will($this->returnValue($updateResult));

        $modelMock->expects($this->any())
            ->method('_saveAddressEntities')
            ->will($this->returnCallback(array($this, 'validateSaveAddressEntities')));

        $modelMock->expects($this->any())
            ->method('_saveAddressAttributes')
            ->will($this->returnValue($modelMock));

        $modelMock->expects($this->any())
            ->method('_saveCustomerDefaults')
            ->will($this->returnValue($modelMock));

        $modelMock->expects($this->any())
            ->method('_deleteAddressEntities')
            ->will($this->returnCallback(array($this, 'validateDeleteAddressEntities')));

        $modelMock->expects($this->any())
            ->method('_mergeEntityAttributes')
            ->will($this->returnValue(array()));

        return $modelMock;
    }

    /**
     * Create mock for customer address model class
     *
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock()
    {
        $modelMock = $this->getMock('Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address',
            array(
                '_getRegionCollection',
                'isAttributeValid',
                '_getCustomerCollection',
            ),
            array(),
            '',
            false,
            true,
            true
        );

        $regionCollection = new Varien_Data_Collection();
        foreach ($this->_regions as $region) {
            $regionCollection->addItem(new Varien_Object($region));
        }

        $modelMock->expects($this->any())
            ->method('_getRegionCollection')
            ->will($this->returnValue($regionCollection));

        $modelMock->expects($this->any())
            ->method('isAttributeValid')
            ->will($this->returnValue(true));

        $customerCollection = new Varien_Data_Collection();
        foreach ($this->_customers as $customer) {
            $customerCollection->addItem(new Varien_Object($customer));
        }

        $modelMock->expects($this->any())
            ->method('_getCustomerCollection')
            ->will($this->returnValue($customerCollection));

        $method = new ReflectionMethod($modelMock, '_initCustomers');
        $method->setAccessible(true);
        $method->invoke($modelMock);

        $property = new ReflectionProperty($modelMock, '_addresses');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_addresses);

        $property = new ReflectionProperty($modelMock, '_websiteCodeToId');
        $property->setAccessible(true);
        $property->setValue($modelMock, array_flip($this->_websites));

        $property = new ReflectionProperty($modelMock, '_attributes');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_attributes);

        $property = new ReflectionProperty($modelMock, '_availableBehaviors');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_availableBehaviors);

        $regions = array();
        $countryRegions = array();
        foreach ($this->_regions as $region) {
            $countryNormalized = strtolower($region['country_id']);
            $regionCode = strtolower($region['code']);
            $regionName = strtolower($region['default_name']);
            $countryRegions[$countryNormalized][$regionCode] = $region['id'];
            $countryRegions[$countryNormalized][$regionName] = $region['id'];
            $regions[$region['id']] = $region['default_name'];
        }

        $method = new ReflectionMethod($modelMock, '_initCountryRegions');
        $method->setAccessible(true);
        $method->invoke($modelMock);

        return $modelMock;
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForUpdateDataProvider()
    {
        return array(
            'valid' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors'  => array(),
                '$isValid' => true,
            ),
            'empty address id' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_empty_address_id.php',
                '$errors' => array(),
                '$isValid' => true,
            ),
            'no customer' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_no_customer.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_CUSTOMER_NOT_FOUND => array(
                        array(1, null)
                    )
                ),
            ),
            'absent required attribute' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_absent_required_attribute.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_VALUE_IS_REQUIRED => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_COUNTRY_ID)
                    )
                ),
            ),
            'invalid region' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_invalid_region.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_INVALID_REGION => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_REGION)
                    )
                ),
            ),
        );
    }

    /**
     * Data provider of row data and errors for add/update action
     *
     * @return array
     */
    public function validateRowForDeleteDataProvider()
    {
        return array(
            'valid' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_update_valid.php',
                '$errors'  => array(),
                '$isValid' => true,
            ),
            'empty address id' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_empty_address_id.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_ADDRESS_ID_IS_EMPTY => array(
                        array(1, null)
                    ),
                )
            ),
            'invalid address' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_address_not_found.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_ADDRESS_NOT_FOUND => array(
                        array(1, null)
                    ),
                )
            ),
            'no customer' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_address_delete_no_customer.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_CUSTOMER_NOT_FOUND => array(
                        array(1, null)
                    )
                ),
            ),
        );
    }

    /**
     * Check whether Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_regions and
     * Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_countryRegions are filled correctly
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_initCountryRegions
     */
    public function testInitCountryRegions()
    {
        $modelMock = $this->_getModelMockForTestInitCountryRegions();

        $regions = array();
        $countryRegions = array();
        foreach ($this->_regions as $region) {
            $countryNormalized = strtolower($region['country_id']);
            $regionCode = strtolower($region['code']);
            $regionName = strtolower($region['default_name']);
            $countryRegions[$countryNormalized][$regionCode] = $region['id'];
            $countryRegions[$countryNormalized][$regionName] = $region['id'];
            $regions[$region['id']] = $region['default_name'];
        }

        $method = new ReflectionMethod($modelMock, '_initCountryRegions');
        $method->setAccessible(true);
        $method->invoke($modelMock);

        $this->assertAttributeEquals($regions, '_regions', $modelMock);
        $this->assertAttributeEquals($countryRegions, '_countryRegions', $modelMock);
    }

    /**
     * Test Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow() with add/update action
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow
     * @dataProvider validateRowForUpdateDataProvider
     * @depends testInitCountryRegions
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     */
    public function testValidateRowForUpdate(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(array('behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_V2_ADD_UPDATE));

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
        $this->assertAttributeEquals($errors, '_errors', $this->_model);
    }

    /**
     * Test Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow() with delete action
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow
     * @dataProvider validateRowForDeleteDataProvider
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     */
    public function testValidateRowForDelete(array $rowData, array $errors, $isValid = false)
    {
        $this->_model->setParameters(array('behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_V2_DELETE));

        if ($isValid) {
            $this->assertTrue($this->_model->validateRow($rowData, 0));
        } else {
            $this->assertFalse($this->_model->validateRow($rowData, 0));
        }
        $this->assertAttributeEquals($errors, '_errors', $this->_model);
    }

    /**
     * Test entity type code getter
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer_address', $this->_model->getEntityTypeCode());
    }

    /**
     * Test default address attribute mapping array
     */
    public function testGetDefaultAddressAttributeMapping()
    {
        $attributeMapping = $this->_model->getDefaultAddressAttributeMapping();
        $this->assertInternalType('array', $attributeMapping, 'Default address attribute mapping must be an array.');
        $this->assertArrayHasKey(
            Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_DEFAULT_BILLING,
            $attributeMapping,
            'Default address attribute mapping array must have a default billing column.'
        );
        $this->assertArrayHasKey(
            Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_DEFAULT_SHIPPING,
            $attributeMapping,
            'Default address attribute mapping array must have a default shipping column.'
        );
    }

    /**
     * Test if correct methods are invoked according to different custom behaviours
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_importData
     */
    public function testImportDataWithCustomBehaviour()
    {
        $this->_model = $this->_getModelMockForTestImportDataWithCustomBehaviour();
        $this->_model->setParameters(array('behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_V2_CUSTOM));

        // validation in validateSaveAddressEntities and validateDeleteAddressEntities
        $this->_model->importData();
    }

    /**
     * Validation method for _saveAddressEntities (callback for _saveAddressEntities)
     *
     * @param array $addUpdateRows
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    public function validateSaveAddressEntities(array $addUpdateRows)
    {
        $this->assertCount(1, $addUpdateRows);
        $this->assertContains($this->_customBehaviour['update_id'], $addUpdateRows);
        return $this->_model;
    }

    /**
     * Validation method for _deleteAddressEntities (callback for _deleteAddressEntities)
     *
     * @param array $deleteRowIds
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    public function validateDeleteAddressEntities(array $deleteRowIds)
    {
        $this->assertCount(1, $deleteRowIds);
        $this->assertContains($this->_customBehaviour['delete_id'], $deleteRowIds);
        return $this->_model;
    }
}
