<?php
/**
 * File contains tests for Auto Discovery functionality.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**#@+
 * API resources must be available without auto loader as the file name cannot be calculated from class name.
 */
include_once __DIR__ . '/../_files/data_types/Customer/AddressData.php';
include_once __DIR__ . '/../_files/data_types/CustomerData.php';
include_once __DIR__ . '/../_files/autodiscovery/resource_class_fixture.php';
include_once __DIR__ . '/../_files/autodiscovery/subresource_class_fixture.php';
/**#@-*/

/**
 * Class implements tests for Mage_Webapi_Helper_Data class.
 */
class Mage_Webapi_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /** @var Mage_Webapi_Model_ConfigAbstract */
    protected static $_apiConfig;

    protected function setUp()
    {
        $this->_helper = Mage::getObjectManager()->get('Mage_Webapi_Helper_Data');
        parent::setUp();
    }

    /**
     * @return Mage_Webapi_Model_ConfigAbstract
     */
    protected function _getApiConfig()
    {
        if (!self::$_apiConfig) {
            $objectManager = new Magento_Test_ObjectManager();
            /** Prepare arguments for SUT constructor. */
            $pathToFixtures = __DIR__ . '/../_files/autodiscovery';
            /** @var Mage_Webapi_Model_Config_Reader_Soap $reader */
            $reader = $objectManager->get(
                'Mage_Webapi_Model_Config_Reader_Soap',
                array(
                    'cache' => $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false)
                )
            );
            $reader->setDirectoryScanner(new Zend\Code\Scanner\DirectoryScanner($pathToFixtures));
            /** Initialize SUT. */
            self::$_apiConfig = $objectManager->create('Mage_Webapi_Model_Config_Soap', array('reader' => $reader));
        }
        return self::$_apiConfig;
    }

    public static function tearDownAfterClass()
    {
        self::$_apiConfig = null;
        parent::tearDownAfterClass();
    }

    /**
     * @dataProvider dataProviderForTestPrepareMethodParamsPositive
     * @param string|object $class
     * @param string $methodName
     * @param array $requestData
     * @param array $expectedResult
     */
    public function testPrepareMethodParamsPositive(
        $class,
        $methodName,
        $requestData,
        $expectedResult = array()
    ) {
        $actualResult = $this->_helper->prepareMethodParams($class, $methodName, $requestData, $this->_getApiConfig());
        $this->assertEquals($expectedResult, $actualResult, "The array of arguments was prepared incorrectly.");
    }

    public static function dataProviderForTestPrepareMethodParamsPositive()
    {
        $customerDataObject = new Vendor_Module_Model_Webapi_CustomerData();
        $customerDataObject->email = "test_email@example.com";
        $customerDataObject->firstname = "firstName";
        $customerDataObject->address = new Vendor_Module_Model_Webapi_Customer_AddressData();
        $customerDataObject->address->city = "cityName";
        $customerDataObject->address->street = "streetName";

        /** Test passing of complex type parameter with optional field not set */
        $optionalNotSetInput = array(
            'email' => "test_email@example.com",
            'firstname' => 'firstName'
        );
        $optionalNotSetOutput = new Vendor_Module_Model_Webapi_CustomerData();
        $optionalNotSetOutput->email = "test_email@example.com";
        $optionalNotSetOutput->firstname = "firstName";
        $optionalNotSetOutput->lastname = "DefaultLastName";
        $optionalNotSetOutput->password = "123123q";

        return array(
            // Test valid data that does not need transformations.
            array(
                'Vendor_Module_Controller_Webapi_Resource_Subresource',
                'createV1',
                array('param1' => 1, 'param2' => 2, 'param3' => array($customerDataObject), 'param4' => 4),
                array('param1' => 1, 'param2' => 2, 'param3' => array($customerDataObject), 'param4' => 4),
            ),
            // Test filtering unnecessary data.
            array(
                'Vendor_Module_Controller_Webapi_Resource_Subresource',
                'createV2',
                array('param1' => 1, 'param2' => 2, 'param3' => array($customerDataObject), 'param4' => 4),
                array('param1' => 1, 'param2' => 2),
            ),
            // Test parameters sorting.
            array(
                'Vendor_Module_Controller_Webapi_Resource_Subresource',
                'createV1',
                array('param4' => 4, 'param2' => 2, 'param3' => array($customerDataObject), 'param1' => 1),
                array('param1' => 1, 'param2' => 2, 'param3' => array($customerDataObject), 'param4' => 4),
            ),
            // Test default values setting.
            array(
                'Vendor_Module_Controller_Webapi_Resource_Subresource',
                'createV1',
                array('param1' => 1, 'param2' => 2),
                array('param1' => 1, 'param2' => 2, 'param3' => array(), 'param4' => 'default_value'),
            ),
            // Test with object instead of class name.
            array(
                new Vendor_Module_Controller_Webapi_Resource_Subresource(),
                'createV2',
                array('param2' => 2, 'param1' => 1),
                array('param1' => 1, 'param2' => 2),
            ),
            // Test passing of partially formatted objects.
            array(
                new Vendor_Module_Controller_Webapi_Resource_Subresource(),
                'updateV1',
                array('param1' => 1, 'param2' => get_object_vars($customerDataObject)),
                array('param1' => 1, 'param2' => $customerDataObject),
            ),
            // Test passing of complex type parameter with optional field not set.
            array(
                new Vendor_Module_Controller_Webapi_Resource_Subresource(),
                'updateV1',
                array('param1' => 1, 'param2' => $optionalNotSetInput),
                array('param1' => 1, 'param2' => $optionalNotSetOutput),
            ),
        );
    }

    /**
     * Test prepareMethodParams method with unexpected data instead of array.
     */
    public function testPrepareMethodParamsArrayExpectedException()
    {
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Data corresponding to "VendorModuleCustomerData[]" type is expected to be an array.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_helper->prepareMethodParams(
            'Vendor_Module_Controller_Webapi_Resource_Subresource',
            'createV1',
            array('param1' => 1, 'param2' => 2, 'param3' => 'not_array', 'param4' => 4),
            $this->_getApiConfig()
        );
    }

    /**
     * Test prepareMethodParams method with complex type equal to unexpected data instead of array.
     */
    public function testPrepareMethodParamsComplexTypeArrayExpectedException()
    {
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Data corresponding to "VendorModuleCustomerData" type is expected to be an array.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_helper->prepareMethodParams(
            'Vendor_Module_Controller_Webapi_Resource_Subresource',
            'updateV1',
            array('param1' => 1, 'param2' => 'Non array complex data'),
            $this->_getApiConfig()
        );
    }

    /**
     * @dataProvider dataProviderForTestPrepareMethodParamsNegative
     * @param string|object $class
     * @param string $methodName
     * @param array $requestData
     * @param string $exceptionClass
     * @param string $exceptionMessage
     */
    public function testPrepareMethodParamsNegative(
        $class,
        $methodName,
        $requestData,
        $exceptionClass,
        $exceptionMessage
    ) {
        $this->setExpectedException($exceptionClass, $exceptionMessage);
        $this->_helper->prepareMethodParams($class, $methodName, $requestData, $this->_getApiConfig());
    }

    public static function dataProviderForTestPrepareMethodParamsNegative()
    {
        /** Customer data without required field */
        $withoutRequired = array(
            'email' => "test_email@example.com"
        );
        return array(
            // Test exception in case of missing required parameter.
            array(
                'Vendor_Module_Controller_Webapi_Resource_Subresource',
                'createV1',
                array('param2' => 2, 'param4' => 4),
                'Mage_Webapi_Exception',
                'Required parameter "param1" is missing.'
            ),
            // Test passing of complex type parameter with not specified required field.
            array(
                new Vendor_Module_Controller_Webapi_Resource_Subresource(),
                'updateV1',
                array('param1' => 1, 'param2' => $withoutRequired),
                'Mage_Webapi_Exception',
                'Value of "firstname" attribute is required.'
            ),
        );
    }
}
