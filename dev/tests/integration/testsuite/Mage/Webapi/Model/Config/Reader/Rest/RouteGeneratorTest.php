<?php
/**
 * File with unit tests for REST routes generator class: Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator
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
require_once __DIR__ . '/../../../../_files/autodiscovery/resource_class_fixture.php';
require_once __DIR__ . '/../../../../_files/autodiscovery/subresource_class_fixture.php';
require_once __DIR__ . '/../../../_files/resource_with_invalid_interface.php';
require_once __DIR__ . '/../../../_files/resource_with_invalid_name.php';

/**#@-*/

class Mage_Webapi_Model_Config_Reader_Rest_RouteGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator
     */
    protected $_model;

    protected function setUp()
    {
        $helper = $this->getMock('Mage_Webapi_Helper_Config', array('__'));
        $this->_model = new Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator($helper);
    }

    /**
     * @dataProvider dataProviderTestGenerateRestRoutesTopLevelResource
     * @param string $className
     * @param string $methodName
     * @param array $expectedRoutes
     */
    public function testGenerateRestRoutesTopLevelResource($className, $methodName, $expectedRoutes)
    {
        $actualRoutes = $this->_model->generateRestRoutes(
            Mage_Webapi_Helper_Data::createMethodReflection($className, $methodName)
        );
        $this->assertRoutesEqual($expectedRoutes, $actualRoutes);
    }

    public static function dataProviderTestGenerateRestRoutesTopLevelResource()
    {
        $versionParam = Mage_Webapi_Controller_Router_Route_Rest::PARAM_VERSION;
        $className = "Vendor_Module_Controller_Webapi_Resource";
        $createPath = "/:$versionParam/vendorModuleResources/requiredField/:requiredField";
        return array(
            array(
                $className,
                "createV1",
                array(
                    $createPath => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                    $createPath . "/optionalField/:optionalField" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                    $createPath . "/secondOptional/:secondOptional" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                    $createPath . "/optionalField/:optionalField/secondOptional/:secondOptional" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                    $createPath . "/secondOptional/:secondOptional/optionalField/:optionalField" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    )
                ),
            ),
            array(
                $className,
                "updateV2",
                array(
                    "/:$versionParam/vendorModuleResources/:id/additionalRequired/:additionalRequired" => array(
                        "actionType" => "item",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
            array(
                $className,
                "getV2",
                array(
                    "/:$versionParam/vendorModuleResources/:id" => array(
                        "actionType" => "item",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
            array(
                $className,
                "listV2",
                array(
                    "/:$versionParam/vendorModuleResources/additionalRequired/:additionalRequired" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                    "/:$versionParam/vendorModuleResources/additionalRequired/:additionalRequired/"
                        . "optional/:optional" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
            array(
                $className,
                "deleteV3",
                array(
                    "/:$versionParam/vendorModuleResources/:id" => array(
                        "actionType" => "item",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
            array(
                $className,
                "multiUpdateV2",
                array(
                    "/:$versionParam/vendorModuleResources" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
            array(
                $className,
                "multiDeleteV2",
                array(
                    "/:$versionParam/vendorModuleResources" => array(
                        "actionType" => "collection",
                        "resourceName" => "vendorModuleResource"
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestGenerateRestRoutesSubresource
     * @param string $className
     * @param string $methodName
     * @param array $expectedRoutes
     */
    public function testGenerateRestRoutesSubresource($className, $methodName, $expectedRoutes)
    {
        $actualRoutes = $this->_model->generateRestRoutes(
            Mage_Webapi_Helper_Data::createMethodReflection($className, $methodName)
        );
        $this->assertRoutesEqual($expectedRoutes, $actualRoutes);
    }

    public static function dataProviderTestGenerateRestRoutesSubresource()
    {
        $className = 'Vendor_Module_Controller_Webapi_Resource_Subresource';
        $versionParam = Mage_Webapi_Controller_Router_Route_Rest::PARAM_VERSION;
        return array(
            array(
                $className,
                'createV2',
                array(
                    "/:$versionParam/vendorModuleResources/:parentId/subresources" => array(
                        'actionType' => 'collection',
                        'resourceName' => 'vendorModuleResourceSubresource'
                    )
                ),
            ),
            array(
                $className,
                'updateV1',
                array(
                    "/:$versionParam/vendorModuleResources/subresources/:id" => array(
                        'actionType' => 'item',
                        'resourceName' => 'vendorModuleResourceSubresource'
                    )
                ),
            ),
        );
    }

    /**
     * Check if list of REST routes are equal.
     *
     * @param array $expectedRoutes
     * @param array $actualRoutes
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function assertRoutesEqual($expectedRoutes, $actualRoutes)
    {
        $this->assertInternalType(
            'array',
            $actualRoutes,
            "Mage_Webapi_Model_Config::generateRestRoutes() must return value of 'array' type."
        );

        foreach ($expectedRoutes as $expectedRoute => $expectedMetadata) {
            $this->assertArrayHasKey(
                $expectedRoute,
                $actualRoutes,
                "'$expectedRoute' route was expected to be present in results."
            );
        }
        foreach ($actualRoutes as $actualRoute => $actualRouteMetadata) {
            $this->assertArrayHasKey(
                $actualRoute,
                $expectedRoutes,
                "'$actualRoute' route was not expected to be present in results."
            );
            $this->assertEquals(
                $expectedRoutes[$actualRoute],
                $actualRouteMetadata,
                "'$actualRoute' route metadata is invalid."
            );
        }
    }

    public function testGenerateRestRoutesInvalidMethod()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '"invalidMethodNameV2" is an invalid API resource method.'
        );
        $this->_model->generateRestRoutes(
            Mage_Webapi_Helper_Data::createMethodReflection(
                'Vendor_Module_Controller_Webapi_Invalid_Interface',
                'invalidMethodNameV2'
            )
        );
    }
}
