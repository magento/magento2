<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductSwatchAttributeOptionManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeOptionManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attributes';

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/swatch_attribute.php
     * @dataProvider addDataProvider
     */
    public function testAdd($optionData)
    {
        $testAttributeCode = 'color_swatch';
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

        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'attributeCode' => $testAttributeCode,
                'option' => $optionData,
            ]
        );

        $this->assertNotNull($response);
        $updatedData = $this->getAttributeOptions($testAttributeCode);
        $lastOption = array_pop($updatedData);
        $this->assertEquals(
            $optionData[AttributeOptionInterface::STORE_LABELS][0][AttributeOptionLabelInterface::LABEL],
            $lastOption['label']
        );
    }

    /**
     * @return array
     */
    public function addDataProvider()
    {
        $optionPayload = [
            AttributeOptionInterface::LABEL => 'new color',
            AttributeOptionInterface::SORT_ORDER => 100,
            AttributeOptionInterface::IS_DEFAULT => true,
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => 'DE label',
                    AttributeOptionLabelInterface::STORE_ID => 1,
                ],
            ],
            AttributeOptionInterface::VALUE => ''
        ];

        return [
            'option_without_value_node' => [
                $optionPayload
            ],
            'option_with_value_node_that_starts_with_text' => [
                array_merge($optionPayload, [AttributeOptionInterface::VALUE => 'some_text'])
            ],
            'option_with_value_node_that_starts_with_a_number' => [
                array_merge($optionPayload, [AttributeOptionInterface::VALUE => '123_some_text'])
            ],

        ];
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
