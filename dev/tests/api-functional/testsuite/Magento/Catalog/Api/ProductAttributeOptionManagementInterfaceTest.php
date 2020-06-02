<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class to test Eav Option Management functionality
 */
class ProductAttributeOptionManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeOptionManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attributes';

    /**
     * Test to get attribute options
     */
    public function testGetItems()
    {
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
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getItems',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['attributeCode' => $testAttributeCode]);

        $this->assertIsArray($response);
        $this->assertEquals($expectedOptions, $response);
    }

    /**
     * Test to add attribute option
     *
     * @param array $optionData
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     * @dataProvider addDataProvider
     */
    public function testAdd(array $optionData)
    {
        $testAttributeCode = 'select_attribute';
        $response = $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $testAttributeCode,
                'option' => $optionData,
            ]
        );

        $this->assertTrue(is_numeric($response));
        /* Check new option labels by stores */
        $expectedStoreLabels = [
            'all' => $optionData[AttributeOptionLabelInterface::LABEL],
            'default' => $optionData[AttributeOptionInterface::STORE_LABELS][0][AttributeOptionLabelInterface::LABEL],
        ];
        foreach ($expectedStoreLabels as $store => $label) {
            $option = $this->getAttributeOption($testAttributeCode, $label, $store);
            $this->assertNotNull($option);
            $this->assertEquals($response, $option['value']);
        }
    }

    /**
     * Data provider for adding attribute option
     *
     * @return array
     */
    public function addDataProvider(): array
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
            'option_with_value_node_that_is_a_number' => [
                array_merge($optionPayload, [AttributeOptionInterface::VALUE => '123'])
            ],
        ];
    }

    /**
     * Test to update attribute option
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdate()
    {
        $testAttributeCode = 'select_attribute';
        $optionData = [
            AttributeOptionInterface::LABEL => 'Fixture Option Changed',
            AttributeOptionInterface::VALUE => 'option_value',
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => 'Store Label Changed',
                    AttributeOptionLabelInterface::STORE_ID => 1,
                ],
            ],
        ];

        $existOptionLabel = 'Fixture Option';
        $existAttributeOption = $this->getAttributeOption($testAttributeCode, $existOptionLabel, 'all');
        $optionId = $existAttributeOption['value'];

        $response = $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $optionId,
                'option' => $optionData,
            ],
            $optionId
        );

        $this->assertTrue($response);

        /* Check update option labels by stores */
        $expectedStoreLabels = [
            'all' => $optionData[AttributeOptionLabelInterface::LABEL],
            'default' => $optionData[AttributeOptionInterface::STORE_LABELS][0][AttributeOptionLabelInterface::LABEL],
        ];
        foreach ($expectedStoreLabels as $store => $label) {
            $this->assertNotNull($this->getAttributeOption($testAttributeCode, $label, $store));
        }
    }

    /**
     * Test to update option with already exist exception
     *
     * Test to except case when the two options has a same label
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdateWithAlreadyExistsException()
    {
        $this->expectExceptionMessage("Admin store attribute option label '%1' is already exists.");
        $testAttributeCode = 'select_attribute';

        $newOptionData = [
            AttributeOptionInterface::LABEL => 'New Option',
            AttributeOptionInterface::VALUE => 'new_option_value',
        ];
        $newOptionId = $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $testAttributeCode,
                'option' => $newOptionData,
            ]
        );

        $editOptionData = [
            AttributeOptionInterface::LABEL => 'Fixture Option',
            AttributeOptionInterface::VALUE => $newOptionId,
        ];
        $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $newOptionId,
                'option' => $editOptionData,
            ],
            $newOptionId
        );
    }

    /**
     * Test to update option with not exist exception
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdateWithNotExistsException()
    {
        $this->expectExceptionMessage("The '%1' attribute doesn't include an option id '%2'.");
        $testAttributeCode = 'select_attribute';

        $newOptionData = [
            AttributeOptionInterface::LABEL => 'New Option',
            AttributeOptionInterface::VALUE => 'new_option_value'
        ];
        $newOptionId = (int)$this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $testAttributeCode,
                'option' => $newOptionData,
            ]
        );

        $newOptionId++;
        $editOptionData = [
            AttributeOptionInterface::LABEL => 'New Option Changed',
            AttributeOptionInterface::VALUE => $newOptionId
        ];
        $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $newOptionId,
                'option' => $editOptionData,
            ],
            $newOptionId
        );
    }

    /**
     * Test to delete attribute option
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testDelete()
    {
        $attributeCode = 'select_attribute';
        $optionList = $this->getAttributeOptions($attributeCode);
        $this->assertGreaterThan(0, count($optionList));
        $lastOption = array_pop($optionList);
        $this->assertNotEmpty($lastOption['value']);
        $optionId = $lastOption['value'];

        $response = $this->webApiCallAttributeOptions(
            $attributeCode,
            Request::HTTP_METHOD_DELETE,
            'delete',
            [
                'attributeCode' => $attributeCode,
                'optionId' => $optionId,
            ],
            $optionId
        );
        $this->assertTrue($response);
        $updatedOptions = $this->getAttributeOptions($attributeCode);
        $this->assertEquals($optionList, $updatedOptions);
    }

    /**
     * Perform Web API call to the system under test
     *
     * @param string $attributeCode
     * @param string $httpMethod
     * @param string $soapMethod
     * @param array $arguments
     * @param null $storeCode
     * @param null $optionId
     * @return array|bool|float|int|string
     */
    private function webApiCallAttributeOptions(
        string $attributeCode,
        string $httpMethod,
        string $soapMethod,
        array $arguments = [],
        $optionId = null,
        $storeCode = null
    ) {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode . '/options'
                    . ($optionId ? '/' .$optionId : ''),
                'httpMethod' => $httpMethod,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $soapMethod,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $arguments, null, $storeCode);
    }

    /**
     * @param string $testAttributeCode
     * @param string|null $storeCode
     * @return array|bool|float|int|string
     */
    private function getAttributeOptions(string $testAttributeCode, ?string $storeCode = null)
    {
        return $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_GET,
            'getItems',
            ['attributeCode' => $testAttributeCode],
            null,
            $storeCode
        );
    }

    /**
     * @param string $attributeCode
     * @param string $optionLabel
     * @param string|null $storeCode
     * @return array|null
     */
    private function getAttributeOption(
        string $attributeCode,
        string $optionLabel,
        ?string $storeCode = null
    ): ?array {
        $attributeOptions = $this->getAttributeOptions($attributeCode, $storeCode);
        $option = null;
        /** @var array $attributeOption */
        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption['label'] === $optionLabel) {
                $option = $attributeOption;
                break;
            }
        }

        return $option;
    }
}
