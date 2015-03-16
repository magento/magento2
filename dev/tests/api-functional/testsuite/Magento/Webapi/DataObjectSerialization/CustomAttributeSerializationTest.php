<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to test if custom attributes are serialized correctly
 */
namespace Magento\Webapi\DataObjectSerialization;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModule1\Service\V1\Entity\ItemFactory;

class CustomAttributeSerializationTest extends \Magento\Webapi\Routing\BaseService
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
    protected $_soapService = 'testModule1AllSoapAndRest';

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\TestModule1\Service\V1\Entity\CustomAttributeNestedDataObjectFactory
     */
    protected $customAttributeNestedDataObjectFactory;

    /**
     * @var \Magento\TestModule1\Service\V1\Entity\CustomAttributeDataObjectFactory
     */
    protected $customAttributeDataObjectFactory;

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
        $this->_soapService = 'testModule1AllSoapAndRestV1';
        $this->_restResourcePath = "/{$this->_version}/testmodule1/";

        $this->itemFactory = Bootstrap::getObjectManager()->create(
            'Magento\TestModule1\Service\V1\Entity\ItemFactory'
        );

        $this->customAttributeNestedDataObjectFactory = Bootstrap::getObjectManager()->create(
            'Magento\TestModule1\Service\V1\Entity\CustomAttributeNestedDataObjectFactory'
        );

        $this->customAttributeDataObjectFactory = Bootstrap::getObjectManager()->create(
            'Magento\TestModule1\Service\V1\Entity\CustomAttributeDataObjectFactory'
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

        //\Magento\TestModule1\Service\V1\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDataObjectCustomAttributes()
    {
        $customAttributeDataObject = $this->customAttributeDataObjectFactory->create()
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1);

        $item = $this->itemFactory->create()
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $item->__toArray();
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModule1\Service\V1\AllSoapAndRest::itemAnyType just return the input data back as response
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

        $customAttributeDataObject = $this->customAttributeDataObjectFactory->create()
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_int', 1);

        $item = $this->itemFactory->create()
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue');
        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
            'getPreconfiguredItem'
        );
        $this->assertEquals($expectedResponse, $result);
    }

    public function testNestedDataObjectCustomAttributes()
    {
        $customAttributeNestedDataObject = $this->customAttributeNestedDataObjectFactory->create()
            ->setName('nestedNameValue');

        $customAttributeDataObject = $this->customAttributeDataObjectFactory->create()
            ->setName('nameValue')
            ->setCustomAttribute('custom_attribute_nested', $customAttributeNestedDataObject)
            ->setCustomAttribute('custom_attribute_int', 1);

        $item = $this->itemFactory->create()
            ->setItemId(1)
            ->setName('testProductAnyType')
            ->setCustomAttribute('custom_attribute_data_object', $customAttributeDataObject)
            ->setCustomAttribute('custom_attribute_string', 'someStringValue');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . 'itemAnyType',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => ['service' => $this->_soapService, 'operation' => $this->_soapService . 'ItemAnyType'],
        ];
        $requestData = $item->__toArray();
        $result = $this->_webApiCall($serviceInfo, ['entityItem' => $requestData]);

        $expectedResponse = $this->serviceOutputProcessor->process(
            $item,
            '\Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
            'itemAnyType'
        );
        //\Magento\TestModule1\Service\V1\AllSoapAndRest::itemAnyType just return the input data back as response
        $this->assertEquals($expectedResponse, $result);
    }
}
