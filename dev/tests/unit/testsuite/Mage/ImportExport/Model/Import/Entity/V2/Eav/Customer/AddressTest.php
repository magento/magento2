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
     * Abstract customer address export model
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
     * Create mock for customer address model class
     *
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock()
    {
        $modelMock = $this->getMock('Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address',
            array('_getRegionCollection', 'isAttributeValid', '_getCustomerCollection'), array(), '', false, true, true
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

        $property = new ReflectionProperty($modelMock, '_websiteCodeToId');
        $property->setAccessible(true);
        $property->setValue($modelMock, array_flip($this->_websites));

        $property = new ReflectionProperty($modelMock, '_attributes');
        $property->setAccessible(true);
        $property->setValue($modelMock, $this->_attributes);

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
     * Data provider of row data and errors
     *
     * @return array
     */
    public function validateRowDataProvider()
    {
        return array(
            'valid' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_valid.php',
                '$errors'  => array(),
                '$isValid' => true,
            ),
            'empty address id' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_empty_address_id.php',
                '$errors' => array(),
                '$isValid' => true,
            ),
            'no website' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_no_website.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_WEBSITE_IS_EMPTY => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_WEBSITE)
                    )
                ),
            ),
            'empty website' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_empty_website.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_WEBSITE_IS_EMPTY => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_WEBSITE)
                    )
                ),
            ),
            'no email' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_no_email.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_EMAIL_IS_EMPTY => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_EMAIL)
                    )
                ),
            ),
            'empty email' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_empty_email.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_EMAIL_IS_EMPTY => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_EMAIL)
                    )
                ),
            ),
            'invalid email' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_email.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_INVALID_EMAIL => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_EMAIL)
                    )
                ),
            ),
            'invalid website' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_website.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_INVALID_WEBSITE => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_WEBSITE)
                    )
                ),
            ),
            'no customer' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_no_customer.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_CUSTOMER_NOT_FOUND => array(
                        array(1, null)
                    )
                ),
            ),
            'absent required attribute' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_absent_required_attribute.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_VALUE_IS_REQUIRED => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_COUNTRY_ID)
                    )
                ),
            ),
            'invalid region' => array(
                '$rowData' => include __DIR__ . '/_files/row_data_invalid_region.php',
                '$errors' => array(
                    Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::ERROR_INVALID_REGION => array(
                        array(1, Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::COLUMN_REGION)
                    )
                ),
            ),
        );
    }

    /**
     * Check whether Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_regions and
     * Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_countryRegions are filled correctly
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::_initCountryRegions()
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
     * Test Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow() with different values
     *
     * @covers Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Address::validateRow()
     * @dataProvider validateRowDataProvider
     * @depends testInitCountryRegions
     *
     * @param array $rowData
     * @param array $errors
     * @param boolean $isValid
     */
    public function testValidateRow(array $rowData, array $errors, $isValid = false)
    {
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
}
