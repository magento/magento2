<?php
/**
 * File with unit tests for API configuration class: Mage_Webapi_Model_Config_Rest.
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
/**#@-*/


/**
 * Test of API configuration class: Mage_Webapi_Model_Config.
 */
class Mage_Webapi_Model_Config_RestTest extends PHPUnit_Framework_TestCase
{
    const WEBAPI_AREA_FRONT_NAME = 'webapi';

    /**
     * @var Mage_Webapi_Model_Config_Rest
     */
    protected static $_apiConfig;

    /**
     * App mock clone usage helps to improve performance. It is required because mock will be removed in tear down.
     *
     * @var Mage_Core_Model_App
     */
    protected static $_appClone;

    public static function tearDownAfterClass()
    {
        self::$_apiConfig = null;
        self::$_appClone = null;
        parent::tearDownAfterClass();
    }

    /**
     * @return Mage_Webapi_Model_Config_Rest
     */
    protected function _getModel()
    {
        if (!self::$_apiConfig) {
            $pathToFixtures = __DIR__ . '/../../_files/autodiscovery';
            self::$_apiConfig = $this->_createResourceConfig($pathToFixtures);
        }
        return self::$_apiConfig;
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

    public function testGetRestRoutes()
    {
        $actualRoutes = $this->_getModel()->getAllRestRoutes();
        $expectedRoutesCount = 16;

        /**
         * Vendor_Module_Controller_Webapi_Resource fixture contains two methods getV2 and deleteV3 that have
         * different names of ID param.
         * If there are two different routes generated for these methods with different ID param names,
         * it will be impossible to identify which route should be used as they both will match the same requests.
         * E.g. DELETE /resource/:deleteId and GET /resource/:getId will match the same requests.
         */
        $this->assertNotCount(
            $expectedRoutesCount + 1,
            $actualRoutes,
            "Some resource methods seem to have different routes, in case when should have the same ones."
        );

        $this->assertCount($expectedRoutesCount, $actualRoutes, "Routes quantity is not equal to expected one.");
        /** @var $actualRoute Mage_Webapi_Controller_Router_Route_Rest */
        foreach ($actualRoutes as $actualRoute) {
            $this->assertInstanceOf('Mage_Webapi_Controller_Router_Route_Rest', $actualRoute);
        }
    }

    public function testGetRestRouteToItem()
    {
        $expectedRoute = '/:resourceVersion/vendorModuleResources/subresources/:id';
        $this->assertEquals($expectedRoute, $this->_getModel()->getRestRouteToItem('vendorModuleResourceSubresource'));
    }

    public function testGetRestRouteToItemInvalidArguments()
    {
        $resourceName = 'vendorModuleResources';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('No route to the item of "%s" resource was found.', $resourceName)
        );
        $this->_getModel()->getRestRouteToItem($resourceName);
    }

    public function testGetMethodRestRoutes()
    {
        $actualRoutes = $this->_getModel()->getMethodRestRoutes('vendorModuleResourceSubresource', 'create', 'v1');
        $this->assertCount(5, $actualRoutes, "Routes quantity does not match expected one.");
        foreach ($actualRoutes as $actualRoute) {
            $this->assertInstanceOf('Mage_Webapi_Controller_Router_Route_Rest', $actualRoute);
        }
    }

    public function testGetMethodRestRoutesException()
    {
        $resourceName = 'vendorModuleResourceSubresource';
        $methodName = 'multiUpdate';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('"%s" resource does not have any REST routes for "%s" method.', $resourceName, $methodName)
        );
        $this->_getModel()->getMethodRestRoutes($resourceName, $methodName, 'v1');
    }

    /**
     * Create resource config initialized with classes found in the specified directory.
     *
     * @param string $pathToResources
     * @return Mage_Webapi_Model_Config_Rest
     */
    protected function _createResourceConfig($pathToResources)
    {
        $objectManager = new Magento_Test_ObjectManager();
        /** Prepare arguments for SUT constructor. */
        /** @var Mage_Core_Model_Cache $cache */
        $cache = $this->getMockBuilder('Mage_Core_Model_Cache')->disableOriginalConstructor()->getMock();
        $configMock = $this->getMockBuilder('Mage_Core_Model_Config')->disableOriginalConstructor()->getMock();
        $configMock->expects($this->any())->method('getAreaFrontName')->will(
            $this->returnValue(self::WEBAPI_AREA_FRONT_NAME)
        );
        $appMock = $this->getMockBuilder('Mage_Core_Model_App')->disableOriginalConstructor()->getMock();
        $appMock->expects($this->any())->method('getConfig')->will($this->returnValue($configMock));
        self::$_appClone = clone $appMock;
        /** @var Mage_Webapi_Model_Config_Reader_Rest $reader */
        $reader = $objectManager->get('Mage_Webapi_Model_Config_Reader_Rest', array('cache' => $cache));
        $reader->setDirectoryScanner(new Zend\Code\Scanner\DirectoryScanner($pathToResources));

        /** Initialize SUT. */
        $apiConfig = $objectManager->create(
            'Mage_Webapi_Model_Config_Rest',
            array('reader' => $reader, 'application' => self::$_appClone)
        );
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
