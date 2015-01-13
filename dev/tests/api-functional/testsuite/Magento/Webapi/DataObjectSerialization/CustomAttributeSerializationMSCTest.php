<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\DataObjectSerialization;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleMSC\Api\Data\ItemDataBuilder;
use Magento\Webapi\Controller\Rest\Response\DataObjectConverter;
use Magento\Webapi\Model\Rest\Config as RestConfig;

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
     * @var DataObjectConverter $dataObjectConverter
     */
    protected $dataObjectConverter;

    /**
     * Set up custom attribute related data objects
     */
    protected function setUp()
    {
        $this->markTestSkipped('This test become irrelevant according to new API Contract');
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

        $this->dataObjectConverter = Bootstrap::getObjectManager()->create(
            'Magento\Webapi\Controller\Rest\Response\DataObjectConverter'
        );
    }

    public function testSimpleAndNonExistentCustomAttributes()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
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
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }
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
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $this->dataObjectProcessor->buildOutputDataArray($item, get_class($item));
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->dataObjectConverter->processServiceOutput(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModuleMSC\Api\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDataObjectCustomAttributesPreconfiguredItem()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemPreconfigured',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
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

        $expectedResponse = $this->dataObjectConverter->processServiceOutput(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'getPreconfiguredItem'
        );
        $this->assertEquals($expectedResponse, $result);
    }

    public function testNestedDataObjectCustomAttributes()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }
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
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $this->dataObjectProcessor->buildOutputDataArray(
            $item,
            '\Magento\TestModuleMSC\Api\Data\ItemInterface'
        );
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->dataObjectConverter->processServiceOutput(
            $item,
            '\Magento\TestModuleMSC\Api\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModuleMSC\Api\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }
}
