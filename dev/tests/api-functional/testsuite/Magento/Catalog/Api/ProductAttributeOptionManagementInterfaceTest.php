<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductAttributeOptionManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeOptionManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attributes';

    public function testGetItems()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $testAttributeCode = 'quantity_and_stock_status';
        $expectedOptions = [
            [
                AttributeOptionInterface::VALUE => '1',
                AttributeOptionInterface::LABEL => 'In Stock',
            ],
            [
                AttributeOptionInterface::VALUE => '0',
                AttributeOptionInterface::LABEL => 'Out of Stock',
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $testAttributeCode . '/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getItems',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['attributeCode' => $testAttributeCode]);

        $this->assertTrue(is_array($response));
        $this->assertEquals($expectedOptions, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testAdd()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $testAttributeCode = 'select_attribute';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $testAttributeCode . '/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'add',
            ],
        ];

        $optionData = [
            AttributeOptionInterface::LABEL => 'new color',
            AttributeOptionInterface::VALUE => 'grey',
            AttributeOptionInterface::SORT_ORDER => 100,
            AttributeOptionInterface::IS_DEFAULT => true,
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => 'DE label',
                    AttributeOptionLabelInterface::STORE_ID => 1,
                ],
            ],
        ];

        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'attributeCode' => $testAttributeCode,
                'option' => $optionData,
            ]
        );

        $this->assertTrue($response);
        $updatedData = $this->getAttributeOptions($testAttributeCode);
        $lastOption = array_pop($updatedData);
        $this->assertEquals(
            $optionData[AttributeOptionInterface::STORE_LABELS][0][AttributeOptionLabelInterface::LABEL],
            $lastOption['label']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testDelete()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $attributeCode = 'select_attribute';
        //get option Id
        $optionList = $this->getAttributeOptions($attributeCode);
        $this->assertGreaterThan(0, count($optionList));
        $lastOption = array_pop($optionList);
        $this->assertNotEmpty($lastOption['value']);
        $optionId = $lastOption['value'];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode . '/options/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'delete',
            ],
        ];
        $this->assertTrue($this->_webApiCall(
            $serviceInfo,
            [
                'attributeCode' => $attributeCode,
                'optionId' => $optionId,
            ]
        ));
        $updatedOptions = $this->getAttributeOptions($attributeCode);
        $this->assertEquals($optionList, $updatedOptions);
    }

    /**
     * @param $testAttributeCode
     * @return array|bool|float|int|string
     */
    private function getAttributeOptions($testAttributeCode)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $testAttributeCode . '/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getItems',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['attributeCode' => $testAttributeCode]);
    }
}
