<?php
/**
 * File with unit tests for API configuration class: Mage_Webapi_Model_Config_Soap.
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
require_once __DIR__ . '/../../_files/autodiscovery/resource_class_fixture.php';
require_once __DIR__ . '/../../_files/autodiscovery/subresource_class_fixture.php';
require_once __DIR__ . '/../../_files/data_types/CustomerData.php';
require_once __DIR__ . '/../../_files/data_types/Customer/AddressData.php';
require_once __DIR__ . '/../_files/resource_with_invalid_interface.php';
require_once __DIR__ . '/../_files/resource_with_invalid_name.php';
require_once __DIR__ . '/../_files/autodiscovery/invalid_deprecation_policy/class.php';
require_once __DIR__ . '/../_files/autodiscovery/empty_var_tags/data_type.php';
require_once __DIR__ . '/../_files/autodiscovery/empty_var_tags/class.php';
require_once __DIR__ . '/../_files/autodiscovery/empty_property_description/data_type.php';
require_once __DIR__ . '/../_files/autodiscovery/empty_property_description/class.php';
require_once __DIR__ . '/../_files/autodiscovery/reference_to_invalid_type/class.php';
/**#@-*/

/**
 * Test of API configuration class: Mage_Webapi_Model_Config.
 */
class Mage_Webapi_Model_Config_SoapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Config_Soap
     */
    protected static $_apiConfig;

    public static function tearDownAfterClass()
    {
        self::$_apiConfig = null;
        parent::tearDownAfterClass();
    }

    /**
     * @return Mage_Webapi_Model_Config_Soap
     */
    protected function _getModel()
    {
        if (!self::$_apiConfig) {
            $pathToFixtures = __DIR__ . '/../../_files/autodiscovery';
            self::$_apiConfig = $this->_createResourceConfig($pathToFixtures);
        }
        return self::$_apiConfig;
    }

    /**
     * @dataProvider dataProviderTestGetResourceNameByOperationPositive
     * @param string $operation
     * @param string $resourceVersion
     * @param string $expectedResourceName
     * @param string $message
     */
    public function testGetResourceNameByOperationPositive(
        $operation,
        $resourceVersion,
        $expectedResourceName,
        $message = 'Resource name was identified incorrectly by given operation.'
    ) {
        $actualResourceName = $this->_getModel()->getResourceNameByOperation($operation, $resourceVersion);
        $this->assertEquals($expectedResourceName, $actualResourceName, $message);
    }

    public function dataProviderTestGetResourceNameByOperationPositive()
    {
        return array(
            array('vendorModuleResourceCreate', 'v1', 'vendorModuleResource'),
            array(
                'vendorModuleResourceCreate',
                '1',
                'vendorModuleResource',
                "Resource was identified incorrectly by version without 'v' prefix"
            ),
            array(
                'vendorModuleResourceMultiUpdate',
                'v2',
                'vendorModuleResource',
                'Compound method names or version seem to be identified incorrectly.'
            ),
            array(
                'vendorModuleResourceSubresourceUpdate',
                'v1',
                'vendorModuleResourceSubresource',
                'Compound resource name is identified incorrectly.'
            ),
            array(
                'vendorModuleResourceSubresourceMultiDelete',
                null,
                'vendorModuleResourceSubresource',
                "If version is not set - no check must be performed for operation existence in resource."
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestGetResourceNameByOperationNegative
     * @param string $operation
     * @param string $resourceVersion
     * @param string $expectedResourceName
     * @param string $message
     */
    public function testGetResourceNameByOperationNegative(
        $operation,
        $resourceVersion,
        $expectedResourceName,
        $message = 'Resource name was identified incorrectly by given operation.'
    ) {
        $actualResourceName = $this->_getModel()->getResourceNameByOperation($operation, $resourceVersion);
        $this->assertEquals($expectedResourceName, $actualResourceName, $message);
    }

    public function dataProviderTestGetResourceNameByOperationNegative()
    {
        return array(
            array('customerUpdate', 'v1', false, "In case when resource is not found, 'false' is expected."),
            array(
                'vendorModuleResourceCreate',
                'v100',
                false,
                "In case when version is not found, 'false' is expected."
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestGetResourceNameByOperationException
     * @param string $operation
     * @param string $resourceVersion
     */
    public function testGetResourceNameByOperationException($operation, $resourceVersion)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('The "%s" is not a valid API resource operation name.', $operation)
        );
        $this->_getModel()->getResourceNameByOperation($operation, $resourceVersion);
    }

    public function dataProviderTestGetResourceNameByOperationException()
    {
        return array(
            array('customerMultiDeleteExcessiveSuffix', 'v2', 'Excessive suffix is ignored.'),
            array('customerInvalid', 'v1', "In case when operation is not found, 'false' is expected."),
        );
    }

    /**
     * @dataProvider dataProviderTestGetMethodNameByOperation
     * @param string $operation
     * @param string $resourceVersion
     * @param string $expectedResourceName
     * @param string $message
     */
    public function testGetMethodNameByOperation(
        $operation,
        $resourceVersion,
        $expectedResourceName,
        $message = 'Resource name was identified incorrectly by given operation.'
    ) {
        $actualResourceName = $this->_getModel()->getMethodNameByOperation($operation, $resourceVersion);
        $this->assertEquals($expectedResourceName, $actualResourceName, $message);
    }

    public function dataProviderTestGetMethodNameByOperation()
    {
        return array(
            array('vendorModuleResourceCreate', 'v1', 'create'),
            array(
                'vendorModuleResourceMultiUpdate',
                'v2',
                'multiUpdate',
                'Compound method names seem to be identified incorrectly or version processing is broken.'
            ),
            array(
                'vendorModuleResourceSubresourceMultiDelete',
                null,
                'multiDelete',
                "If version is not set - no check must be performed for operation existence in resource."
            ),
            array(
                'vendorModuleResourceUpdate',
                'v100',
                false,
                "In case when version is not found, 'false' is expected."
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestGetMethodNameByOperationException
     * @param string $operation
     * @param string $resourceVersion
     */
    public function testGetMethodNameByOperationException($operation, $resourceVersion)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('The "%s" is not a valid API resource operation name.', $operation)
        );
        $this->_getModel()->getMethodNameByOperation($operation, $resourceVersion);
    }

    public function dataProviderTestGetMethodNameByOperationException()
    {
        return array(
            array('vendorModuleResourceMultiUpdateExcessiveSuffix', 'v2', 'Excessive suffix is ignored.'),
            array('vendorModuleResourceInvalid', 'v1', "In case when operation is not found, 'false' is expected."),
        );
    }

    public function testGetControllerClassByOperationNamePositive()
    {
        $actualController = $this->_getModel()->getControllerClassByOperationName('vendorModuleResourceList');
        $message = 'Controller class was identified incorrectly by given operation.';
        $this->assertEquals('Vendor_Module_Controller_Webapi_Resource', $actualController, $message);
    }

    /**
     * @dataProvider dataProviderTestGetControllerClassByOperationNameNegative
     * @param string $operation
     */
    public function testGetControllerClassByOperationNameNegative($operation)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('The "%s" is not a valid API resource operation name.', $operation)
        );
        $this->_getModel()->getControllerClassByOperationName($operation);
    }

    public function dataProviderTestGetControllerClassByOperationNameNegative()
    {
        return array(
            array('customerMultiDeleteExcessiveSuffix', 'Excessive suffix is ignored.'),
            array('customerInvalid', "In case when operation is not found, 'false' is expected."),
        );
    }

    public function testGetControllerClassByOperationNameWithException()
    {
        $this->setExpectedException(
            'LogicException',
            'Resource "resourceWithoutControllerAndModule" must have associated controller class.'
        );
        $this->_getModel()->getControllerClassByOperationName('resourceWithoutControllerAndModuleGet');
    }

    /**
     * @dataProvider dataProviderForTestGetResourceMaxVersion
     * @param string $resourceName
     * @param int $expectedMaxVersion
     */
    public function testGetResourceMaxVersion($resourceName, $expectedMaxVersion)
    {
        $this->assertEquals(
            $expectedMaxVersion,
            $this->_getModel()->getResourceMaxVersion($resourceName),
            "Resource maximum available version was identified incorrectly."
        );
    }

    public function dataProviderForTestGetResourceMaxVersion()
    {
        return array(
            array('vendorModuleResource', 5),
            array('vendorModuleResourceSubresource', 4),
        );
    }

    public function testGetResourceMaxVersionException()
    {
        $resourceName = 'InvalidResource';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Resource "%s" does not exist.', $resourceName)
        );
        $this->_getModel()->getResourceMaxVersion($resourceName);
    }

    public function testGetResource()
    {
        $resourceData = $this->_getModel()->getResourceDataMerged('vendorModuleResource', 'v1');
        $this->assertTrue(isset($resourceData['methods']['create']), "Information about methods is not available.");
        $this->assertTrue(
            isset($resourceData['methods']['create']['interface']['in']['parameters']['requiredField']),
            "Data structure seems to be missing method input parameters."
        );
        $this->assertTrue(
            isset($resourceData['methods']['create']['interface']['out']['parameters']['result']['type']),
            "Data structure seems to be missing method output parameters."
        );
    }

    public function testGetResourceInvalidResourceName()
    {
        $this->setExpectedException('RuntimeException', 'Unknown resource "invalidResource".');
        $this->_getModel()->getResourceDataMerged('invalidResource', 'v1');
    }

    public function testGetResourceInvalidVersion()
    {
        $this->setExpectedException('RuntimeException', 'Unknown version "V100" for resource "vendorModuleResource".');
        $this->_getModel()->getResourceDataMerged('vendorModuleResource', 'v100');
    }

    public function testGetTypeData()
    {
        $actualDataType = $this->_getModel()->getTypeData('VendorModuleCustomerAddressData');
        $expectedDataType = array(
            'documentation' => 'Tests fixture for Auto Discovery functionality. Customer address entity.',
            'parameters' => array(
                'street' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => NULL,
                    'documentation' => 'Street',
                ),
                'city' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => NULL,
                    'documentation' => 'City',
                ),
                'state' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => NULL,
                    'documentation' => 'State',
                ),
            ),
        );
        $this->assertEquals($expectedDataType, $actualDataType);
    }

    public function testGetTypeDataInvalidName()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Data type "InvalidDataTypeName" was not found in config.'
        );
        $this->_getModel()->getTypeData('InvalidDataTypeName');
    }

    public function testGetAllResourcesVersions()
    {
        $expectedResult = array(
            'vendorModuleResource' => array('V1', 'V2', 'V3', 'V4', 'V5'),
            'vendorModuleResourceSubresource' => array('V1', 'V2', 'V4')
        );
        $allResourcesVersions = $this->_getModel()->getAllResourcesVersions();
        $this->assertEquals($expectedResult, $allResourcesVersions, "The list of all resources versions is incorrect.");
    }

    public function testGetMethodMetadataDataNotAvailable()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The "update" method of "vendorModuleInvalidInterface" resource in version "V2" is not registered.'
        );
        $this->_getModel()->getMethodMetadata(
            $this->_createMethodReflection(
                'Vendor_Module_Controller_Webapi_Invalid_Interface',
                'updateV2'
            )
        );
    }

    public function testExtractDataPopulateClassException()
    {
        $this->setExpectedException('LogicException', 'There can be only one class in');
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/several_classes_in_one_file');
    }

    public function testExtractDataEmptyResult()
    {
        $this->setExpectedException('LogicException', 'Cannot populate config - no action controllers were found.');
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/no_resources');
    }

    public function testExtractDataInvalidTypeOfArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'Could not load the ');
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/reference_to_invalid_type');
    }

    public function testExtractDataUndocumentedProperty()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Each property must have description with @var annotation.'
        );
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/empty_property_description');
    }

    public function testExtractDataPropertyWithoutVarTag()
    {
        $this->setExpectedException('InvalidArgumentException', 'Property type must be defined with @var tag.');
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/empty_var_tags');
    }

    public function testExtractDataInvalidDeprecationPolicy()
    {
        $this->setExpectedException(
            'LogicException',
            '"Invalid_Deprecation_Controller_Webapi_Policy::getV1" '
                . 'method has invalid format of Deprecation policy. Accepted formats are createV1, '
                . 'catalogProduct::createV1 and Mage_Catalog_Webapi_ProductController::createV1.'
        );
        $this->_createResourceConfig(__DIR__ . '/../_files/autodiscovery/invalid_deprecation_policy');
    }

    /**
     * @dataProvider dataProviderForTestGetDeprecationPolicy
     * @param string $resource
     * @param string $method
     * @param string $version
     * @param string $deprecationFormat
     * @param array $expectedResult
     */
    public function testGetDeprecationPolicy($resource, $method, $version, $expectedResult, $deprecationFormat)
    {
        $actualResult = $this->_getModel()->getDeprecationPolicy($resource, $method, $version);
        $this->assertEquals(
            $expectedResult,
            $actualResult,
            "Deprecation policy was defined incorrectly. The following definition was used: '$deprecationFormat'"
        );
    }

    public static function dataProviderForTestGetDeprecationPolicy()
    {
        return array(
            array(
                'vendorModuleResource',
                'list',
                2,
                array(
                    'deprecated' => true,
                    'use_resource' => 'vendorModuleResource',
                    'use_method' => 'list',
                    'use_version' => 'V3'
                ),
                '@apiDeprecated vendorModuleResource::listV3'
            ),
            array('vendorModuleResource', 'list', 3, false, 'No policy is defined.'),
            array(
                'vendorModuleResource',
                'delete',
                'V1',
                array(
                    'removed' => true,
                    'use_resource' => 'vendorModuleResource',
                    'use_method' => 'delete',
                    'use_version' => 'V3'
                ),
                '@apiRemoved deleteV3'
            ),
            array(
                'vendorModuleResource',
                'delete',
                2,
                array(
                    'deprecated' => true,
                    'use_resource' => 'vendorModuleResourceSubresource',
                    'use_method' => 'delete',
                    'use_version' => 'V3'
                ),
                '@apiDeprecated Vendor_Module_Controller_Webapi_Resource_Subresource::deleteV3'
            ),
            array(
                'vendorModuleResource',
                'delete',
                'v3',
                array(
                    'removed' => true,
                ),
                '@apiDeprecated \n @apiRemoved'
            ),
            array(
                'vendorModuleResource',
                'delete',
                'v4',
                array(
                    'deprecated' => true,
                ),
                '@apiDeprecated'
            ),
        );
    }

    /**
     * @dataProvider dataProviderForTestGetDeprecationPolicyException
     * @param string $resource
     * @param string $method
     * @param string $version
     * @param string $expectedMessage
     */
    public function testGetDeprecationPolicyException($resource, $method, $version, $expectedMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedMessage);
        $this->_getModel()->getDeprecationPolicy($resource, $method, $version);
    }

    public static function dataProviderForTestGetDeprecationPolicyException()
    {
        return array(
            array('invalidResource', 'delete', 1, 'Unknown resource "invalidResource".'),
            array('vendorModuleResource', 'update', 10, 'Unknown version "V10" for resource "vendorModuleResource".'),
            array(
                'vendorModuleResource',
                'update',
                1,
                'Method "update" does not exist in "1" version of resource "vendorModuleResource".'
            ),
        );
    }

    /**
     * Create resource config initialized with classes found in the specified directory.
     *
     * @param string $pathToResources
     * @return Mage_Webapi_Model_Config_Soap
     */
    protected function _createResourceConfig($pathToResources)
    {
        $objectManager = new Magento_Test_ObjectManager();
        /** Prepare arguments for SUT constructor. */
        /** @var Mage_Core_Model_Cache $cache */
        $cache = $this->getMockBuilder('Mage_Core_Model_Cache')->disableOriginalConstructor()->getMock();
        /** @var Mage_Webapi_Model_Config_Reader_Soap $reader */
        $reader = $objectManager->get('Mage_Webapi_Model_Config_Reader_Soap', array('cache' => $cache));
        $reader->setDirectoryScanner(new Zend\Code\Scanner\DirectoryScanner($pathToResources));

        /** Initialize SUT. */
        $apiConfig = $objectManager->create('Mage_Webapi_Model_Config_Soap', array('reader' => $reader));
        return $apiConfig;
    }

    /**
     * Create Zend method reflection object.
     *
     * @param string|object $classOrObject
     * @param string $methodName
     * @return Zend\Server\Reflection\ReflectionMethod
     */
    protected function _createMethodReflection($classOrObject, $methodName)
    {
        $methodReflection = new \ReflectionMethod($classOrObject, $methodName);
        $classReflection = new \ReflectionClass($classOrObject);
        $zendClassReflection = new Zend\Server\Reflection\ReflectionClass($classReflection);
        $zendMethodReflection = new Zend\Server\Reflection\ReflectionMethod($zendClassReflection, $methodReflection);
        return $zendMethodReflection;
    }
}
