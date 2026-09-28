<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\MultiselectAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test saving a product multiselect custom attribute through the REST API
 * using both an array of option IDs and a comma-separated string.
 */
#[
    DataFixture(
        MultiselectAttribute::class,
        [
            'source_model' => Table::class,
            'backend_model' => ArrayBackend::class,
        ],
        'multiselect_attribute'
    ),
]
class MultiselectAttributeSaveTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/products';

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
    }

    public function testSaveMultiselectAttributeWithArrayValue(): void
    {
        $attribute = $this->getAttribute();
        $attributeCode = (string) $attribute->getData('attribute_code');
        $optionValues = [
            (string) $attribute->getData('option_1'),
            (string) $attribute->getData('option_2'),
        ];

        $productData = $this->getProductData($attributeCode, $optionValues);
        $this->saveProduct($productData);

        $this->assertStoredValue(
            $productData[ProductInterface::SKU],
            $attributeCode,
            implode(',', $optionValues)
        );
    }

    public function testSaveMultiselectAttributeWithStringValue(): void
    {
        $attribute = $this->getAttribute();
        $attributeCode = (string) $attribute->getData('attribute_code');
        $optionValues = [
            (string) $attribute->getData('option_1'),
            (string) $attribute->getData('option_2'),
        ];
        $multiselectValue = implode(',', $optionValues);

        $productData = $this->getProductData($attributeCode, $multiselectValue);
        $this->saveProduct($productData);

        $this->assertStoredValue(
            $productData[ProductInterface::SKU],
            $attributeCode,
            $multiselectValue
        );
    }

    /**
     * @return DataObject
     */
    private function getAttribute(): DataObject
    {
        return DataFixtureStorageManager::getStorage()->get('multiselect_attribute');
    }

    /**
     * @param string $attributeCode
     * @param string|string[] $attributeValue
     * @return array
     */
    private function getProductData(string $attributeCode, $attributeValue): array
    {
        return [
            ProductInterface::SKU => uniqid('sku-', true),
            ProductInterface::NAME => uniqid('name-', true),
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 3.62,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes' => [
                ['attribute_code' => $attributeCode, 'value' => $attributeValue],
            ],
        ];
    }

    /**
     * @param array $productData
     * @return void
     */
    private function saveProduct(array $productData): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $this->_webApiCall($serviceInfo, ['product' => $productData]);
    }

    /**
     * @param string $sku
     * @param string $attributeCode
     * @param string $expectedValue
     * @return void
     */
    private function assertStoredValue(string $sku, string $attributeCode, string $expectedValue): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku]);

        $multiselectValue = null;
        foreach ($response['custom_attributes'] as $customAttribute) {
            if ($customAttribute['attribute_code'] === $attributeCode) {
                $multiselectValue = $customAttribute['value'];
                break;
            }
        }

        $this->assertEquals($expectedValue, $multiselectValue);
    }
}
