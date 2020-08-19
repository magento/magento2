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
 * Class to test update Product Attribute Options
 */
class ProductAttributeOptionUpdateInterfaceTest extends WebapiAbstract
{
    private const SERVICE_NAME_UPDATE = 'catalogProductAttributeOptionUpdateV1';
    private const SERVICE_NAME = 'catalogProductAttributeOptionManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products/attributes';

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
        $resourcePath = self::RESOURCE_PATH . "/{$attributeCode}/options";
        if ($optionId) {
            $resourcePath .= '/' . $optionId;
        }
        $serviceName = $soapMethod === 'update' ? self::SERVICE_NAME_UPDATE : self::SERVICE_NAME;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => $httpMethod,
            ],
            'soap' => [
                'service' => $serviceName,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => $serviceName . $soapMethod,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $arguments, null, $storeCode);
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
}
