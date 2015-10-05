<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CustomerMetadataTest
 */
class CustomerMetadataTest extends WebapiAbstract
{
    const SERVICE_NAME = "customerCustomerMetadataV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH = "/V1/attributeMetadata/customer";

    /**
     * Test retrieval of attribute metadata for the customer entity type.
     *
     * @param string $attributeCode The attribute code of the requested metadata.
     * @param array $expectedMetadata Expected entity metadata for the attribute code.
     * @dataProvider getAttributeMetadataDataProvider
     */
    public function testGetAttributeMetadata($attributeCode, $expectedMetadata)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/attribute/$attributeCode",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAttributeMetadata',
            ],
        ];

        $requestData = [
            'attributeCode' => $attributeCode,
        ];

        $attributeMetadata = $this->_webapiCall($serviceInfo, $requestData);

        $validationResult = $this->checkValidationRules($expectedMetadata, $attributeMetadata);
        list($expectedMetadata, $attributeMetadata) = $validationResult;
        $this->assertEquals($expectedMetadata, $attributeMetadata);
    }

    /**
     * Data provider for testGetAttributeMetadata.
     *
     * @return array
     */
    public function getAttributeMetadataDataProvider()
    {
        return [
            Customer::FIRSTNAME => [
                Customer::FIRSTNAME,
                [
                    AttributeMetadata::FRONTEND_INPUT   => 'text',
                    AttributeMetadata::INPUT_FILTER     => '',
                    AttributeMetadata::STORE_LABEL      => 'First Name',
                    AttributeMetadata::MULTILINE_COUNT  => 0,
                    AttributeMetadata::VALIDATION_RULES => [
                        ['name' => 'min_text_length', 'value' => 1],
                        ['name' => 'max_text_length', 'value' => 255],
                    ],
                    AttributeMetadata::VISIBLE          => true,
                    AttributeMetadata::REQUIRED         => true,
                    AttributeMetadata::DATA_MODEL       => '',
                    AttributeMetadata::OPTIONS          => [],
                    AttributeMetadata::FRONTEND_CLASS   => ' required-entry',
                    AttributeMetadata::USER_DEFINED     => false,
                    AttributeMetadata::SORT_ORDER       => 40,
                    AttributeMetadata::FRONTEND_LABEL   => 'First Name',
                    AttributeMetadata::NOTE             => '',
                    AttributeMetadata::SYSTEM           => true,
                    AttributeMetadata::BACKEND_TYPE     => 'static',
                    AttributeMetadata::IS_USED_IN_GRID  => '',
                    AttributeMetadata::IS_VISIBLE_IN_GRID => '',
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => '',
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => '',
                    AttributeMetadata::ATTRIBUTE_CODE   => 'firstname',

                ],
            ],
            Customer::GENDER => [
                Customer::GENDER,
                [
                    AttributeMetadata::FRONTEND_INPUT   => 'select',
                    AttributeMetadata::INPUT_FILTER     => '',
                    AttributeMetadata::STORE_LABEL      => 'Gender',
                    AttributeMetadata::MULTILINE_COUNT  => 0,
                    AttributeMetadata::VALIDATION_RULES => [],
                    AttributeMetadata::VISIBLE          => false,
                    AttributeMetadata::REQUIRED         => false,
                    AttributeMetadata::DATA_MODEL       => '',
                    AttributeMetadata::OPTIONS          => [
                        ['label' => '', 'value' => ''],
                        ['label' => 'Male', 'value' => '1'],
                        ['label' => 'Female', 'value' => '2'],
                        ['label' => 'Not Specified', 'value' => '3']
                    ],
                    AttributeMetadata::FRONTEND_CLASS   => '',
                    AttributeMetadata::USER_DEFINED     => false,
                    AttributeMetadata::SORT_ORDER       => 110,
                    AttributeMetadata::FRONTEND_LABEL   => 'Gender',
                    AttributeMetadata::NOTE             => '',
                    AttributeMetadata::SYSTEM           => false,
                    AttributeMetadata::BACKEND_TYPE     => 'static',
                    AttributeMetadata::IS_USED_IN_GRID  => true,
                    AttributeMetadata::IS_VISIBLE_IN_GRID => true,
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => true,
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => '',
                    AttributeMetadata::ATTRIBUTE_CODE   => 'gender',
                ],
            ],
            Customer::WEBSITE_ID => [
                Customer::WEBSITE_ID,
                [
                    AttributeMetadata::FRONTEND_INPUT   => 'select',
                    AttributeMetadata::INPUT_FILTER     => '',
                    AttributeMetadata::STORE_LABEL      => 'Associate to Website',
                    AttributeMetadata::MULTILINE_COUNT  => 0,
                    AttributeMetadata::VALIDATION_RULES => [],
                    AttributeMetadata::VISIBLE          => true,
                    AttributeMetadata::REQUIRED         => true,
                    AttributeMetadata::DATA_MODEL       => '',
                    AttributeMetadata::OPTIONS          => [
                        ['label' => 'Main Website', 'value' => '1'],
                    ],
                    AttributeMetadata::FRONTEND_CLASS   => ' required-entry',
                    AttributeMetadata::USER_DEFINED     => false,
                    AttributeMetadata::SORT_ORDER       => 10,
                    AttributeMetadata::FRONTEND_LABEL   => 'Associate to Website',
                    AttributeMetadata::NOTE             => '',
                    AttributeMetadata::SYSTEM           => true,
                    AttributeMetadata::BACKEND_TYPE     => 'static',
                    AttributeMetadata::IS_USED_IN_GRID  => true,
                    AttributeMetadata::IS_VISIBLE_IN_GRID => true,
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => true,
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => false,
                    AttributeMetadata::ATTRIBUTE_CODE   => 'website_id',
                ],
            ]
        ];
    }

    /**
     * Test retrieval of all customer attribute metadata.
     */
    public function testGetAllAttributesMetadata()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAllAttributesMetadata',
            ],
        ];

        $attributeMetadata = $this->_webApiCall($serviceInfo);

        $firstName = $this->getAttributeMetadataDataProvider()[Customer::FIRSTNAME][1];
        $validationResult = $this->checkMultipleAttributesValidationRules($firstName, $attributeMetadata);
        list($firstName, $attributeMetadata) = $validationResult;
        $this->assertContains($firstName, $attributeMetadata);

        $websiteId = $this->getAttributeMetadataDataProvider()[Customer::WEBSITE_ID][1];
        $validationResult = $this->checkMultipleAttributesValidationRules($websiteId, $attributeMetadata);
        list($websiteId, $attributeMetadata) = $validationResult;
        $this->assertContains($websiteId, $attributeMetadata);
    }

    /**
     * Test retrieval of custom customer attribute metadata.
     */
    public function testGetCustomAttributesMetadata()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/custom',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCustomAttributesMetadata',
            ],
        ];

        $attributeMetadata = $this->_webApiCall($serviceInfo);

        // There are no default custom attributes.
        $this->assertCount(0, $attributeMetadata);
    }

    /**
     * Test retrieval of attributes
     *
     * @param string $formCode Form code
     * @param array $expectedMetadata The expected attribute metadata
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($formCode, $expectedMetadata)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/form/$formCode",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAttributes',
            ],
        ];

        $requestData = [
            'formCode' => $formCode,
        ];

        $attributeMetadataList = $this->_webApiCall($serviceInfo, $requestData);
        foreach ($attributeMetadataList as $attributeMetadata) {
            if (isset($attributeMetadata['attribute_code'])
                && $attributeMetadata['attribute_code'] == $expectedMetadata['attribute_code']) {
                $validationResult = $this->checkValidationRules($expectedMetadata, $attributeMetadata);
                list($expectedMetadata, $attributeMetadata) = $validationResult;
                $this->assertEquals($expectedMetadata, $attributeMetadata);
                break;
            }
        }
    }

    /**
     * Data provider for testGetAttributes.
     *
     * @return array
     */
    public function getAttributesDataProvider()
    {
        $attributeMetadata = $this->getAttributeMetadataDataProvider();
        return [
            [
                'adminhtml_customer',
                $attributeMetadata[Customer::FIRSTNAME][1],
            ],
            [
                'adminhtml_customer',
                $attributeMetadata[Customer::GENDER][1]
            ]
        ];
    }

    /**
     * Checks that expected and actual attribute metadata validation rules are equal
     * and removes the validation rules entry from expected and actual attribute metadata
     *
     * @param array $expectedResult
     * @param array $actualResult
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkValidationRules($expectedResult, $actualResult)
    {
        $expectedRules = [];
        $actualRules   = [];

        if (isset($expectedResult[AttributeMetadata::VALIDATION_RULES])) {
            $expectedRules = $expectedResult[AttributeMetadata::VALIDATION_RULES];
            unset($expectedResult[AttributeMetadata::VALIDATION_RULES]);
        }
        if (isset($actualResult[AttributeMetadata::VALIDATION_RULES])) {
            $actualRules = $actualResult[AttributeMetadata::VALIDATION_RULES];
            unset($actualResult[AttributeMetadata::VALIDATION_RULES]);
        }

        if (is_array($expectedRules) && is_array($actualRules)) {
            foreach ($expectedRules as $expectedRule) {
                if (isset($expectedRule['name']) && isset($expectedRule['value'])) {
                    $found = false;
                    foreach ($actualRules as $actualRule) {
                        if (isset($actualRule['name']) && isset($actualRule['value'])) {
                            if ($expectedRule['name'] == $actualRule['name']
                                && $expectedRule['value'] == $actualRule['value']
                            ) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    $this->assertTrue($found);
                }
            }
        }
        return [$expectedResult, $actualResult];
    }

    /**
     * Check specific attribute validation rules in set of multiple attributes
     *
     * @param array $expectedResult Set of expected attribute metadata
     * @param array $actualResultSet Set of actual attribute metadata
     * @return array
     */
    public function checkMultipleAttributesValidationRules($expectedResult, $actualResultSet)
    {
        if (is_array($expectedResult) && is_array($actualResultSet)) {
            if (isset($expectedResult[AttributeMetadata::ATTRIBUTE_CODE])) {
                foreach ($actualResultSet as $actualAttributeKey => $actualAttribute) {
                    if (isset($actualAttribute[AttributeMetadata::ATTRIBUTE_CODE])
                        && $expectedResult[AttributeMetadata::ATTRIBUTE_CODE]
                        == $actualAttribute[AttributeMetadata::ATTRIBUTE_CODE]
                    ) {
                        $this->checkValidationRules($expectedResult, $actualAttribute);
                        unset($actualResultSet[$actualAttributeKey][AttributeMetadata::VALIDATION_RULES]);
                    }
                }
                unset($expectedResult[AttributeMetadata::VALIDATION_RULES]);
            }
        }
        return [$expectedResult, $actualResultSet];
    }
}
