<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\WebapiAbstract;

#[
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => 'product_custom_attribute',
            'is_filterable' => false,
            'frontend_input' => 'select',
            'backend_type' => 'int',
        ]
    ),
]
class ProductAttributeIsFilterableManagementTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogProductAttributeIsFilterableManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products/attributes/%s/is-filterable';

    /**
     * @return void
     */
    public function testGet(): void
    {
        $isFilterable = $this->getAttributeIsFilterable('product_custom_attribute');

        $this->assertEquals(0, $isFilterable);
    }

    /**
     * @return void
     */
    public function testSet(): void
    {
        $attributeCode = 'product_custom_attribute';
        $isFilterableIntValue = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $attributeCode) . '/' . $isFilterableIntValue,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
            ],
        ];

        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'attributeCode' => $attributeCode,
                'isFilterable' => $isFilterableIntValue,
            ]
        );
        $this->assertTrue($response);
        $this->assertEquals(
            $isFilterableIntValue,
            $this->getAttributeIsFilterable($attributeCode)
        );
    }

    /**
     * @param string $attributeCode
     * @return int
     */
    private function getAttributeIsFilterable(string $attributeCode): int
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $attributeCode),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['attributeCode' => $attributeCode]);
    }
}
