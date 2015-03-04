<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\DataObjectSerialization;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleMSC\Api\Data\ItemDataBuilder;

/**
 * api-functional/testsuite/Magento/Webapi/DataObjectSerialization/CustomAttributeSerializationMSCTest.php
 * Class to test if custom attributes are serialized correctly for the new Module Service Contract approach
 */
class CustomAttributeSerializationMSCTest extends \Magento\Webapi\Routing\BaseService
{
    /**
     * @var string
     */
    protected $_version;
    /**
     * @var string
     */
    protected $_restResourcePath;
    /**
     * @var string
     */
    protected $_soapService = 'testModuleMSCAllSoapAndRest';

    /**
     * @var ItemDataBuilder
     */
    protected $itemDataBuilder;

    /**
     * @var \Magento\TestModuleMSC\Api\Data\CustomAttributeNestedDataObjectDataBuilder
     */
    protected $customAttributeNestedDataObjectDataBuilder;

    /**
     * @var \Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectDataBuilder
     */
    protected $customAttributeDataObjectDataBuilder;

    /**
     * @var DataObjectProcessor $dataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var ServiceOutputProcessor $serviceOutputProcessor
     */
    protected $serviceOutputProcessor;

    /**
     * Set up custom attribute related data objects
     */
    protected function setUp()
    {
        $this->_version = 'V1';
        $this->_soapService = 'testModuleMSCAllSoapAndRestV1';
        $this->_restResourcePath = "/{$this->_version}/testmoduleMSC/";

        $this->itemDataBuilder = Bootstrap::getObjectManager()->create(
            'Magento\TestModuleMSC\Api\Data\ItemDataBuilder'
        );

        $this->customAttributeNestedDataObjectDataBuilder = Bootstrap::getObjectManager()->create(
            'Magento\TestModuleMSC\Api\Data\CustomAttributeNestedDataObjectDataBuilder'
        );

        $this->customAttributeDataObjectDataBuilder = Bootstrap::getObjectManager()->create(
            'Magento\TestModuleMSC\Api\Data\CustomAttributeDataObjectDataBuilder'
        );

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Reflection\DataObjectProcessor'
        );

        $this->serviceOutputProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Webapi\ServiceOutputProcessor'
        );
    }

    public function testSimpleAndNonExistentCustomAttributes()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = [
            'item_id' => 1,
            'name' => 'testProductAnyType',
            'custom_attributes' => [
                    'non_existent' => [
                            'attribute_code' => 'non_existent',
                            'value' => 'test',
                        ],
                    'custom_attribute_string' => [
                            'attribute_code' => 'custom_attribute_string',
                            'value' => 'someStringValue',
                        ],
                ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        //The non_existent custom attribute should be dropped since its not a defined custom attribute
        $expectedResponse = [
            'item_id' => 1,
            'name' => 'testProductAnyType',
            'custom_attributes' => [
                    [
                        'attribute_code' => 'custom_attribute_string',
                        'value' => 'someStringValue',
                    ],
                ],
        ];

        //\Magento\TestModuleMSC\Api\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDataObjectCustomAttributes()
    {
        $customAttributeDataObject = $this->customAttributeDataObjectDataBuilder
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1)
            ->create();

        $item = $this->itemDataBuilder
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue')
            ->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $this->dataObjectProcessor->buildOutputDataArray(
            $item,
            '\Magento\TestModuleMSC\Api\Data\ItemInterface'
        );
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModuleMSC\Api\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDataObjectCustomAttributesPreconfiguredItem()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemPreconfigured',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'GetPreconfiguredItem'],
        ];

        $result = $this->_webApiCall($serviceInfo, []);

        $customAttributeDataObject = $this->customAttributeDataObjectDataBuilder
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1)
            ->create();

        $item = $this->itemDataBuilder
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue')
            ->create();

        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'getPreconfiguredItem'
        );
        $this->assertEquals($expectedResponse, $result);
    }

    public function testNestedDataObjectCustomAttributes()
    {
        $customAttributeNestedDataObject = $this->customAttributeNestedDataObjectDataBuilder
            ->setName('nestedNameValue')
            ->create();

        $customAttributeDataObject = $this->customAttributeDataObjectDataBuilder
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_nested', $customAttributeNestedDataObject)
            ->setCustomAttribute('custom_attribute_int', 1)
            ->create();

        $item = $this->itemDataBuilder
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue')
            ->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $this->dataObjectProcessor->buildOutputDataArray(
            $item,
            '\Magento\TestModuleMSC\Api\Data\ItemInterface'
        );
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModuleMSC\Api\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }
}
