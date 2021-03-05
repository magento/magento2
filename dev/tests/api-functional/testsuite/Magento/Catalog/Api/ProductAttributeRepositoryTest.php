<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

/**
 * API tests for \Magento\Catalog\Model\Product\Attribute\Repository.
 */
class ProductAttributeRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attributes';

    /**
     * @var array
     */
    private $createdAttributes = [];

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_attribute.php
     * @return void
     */
    public function testGet()
    {
        $attributeCode = 'test_attribute_code_333';
        $attribute = $this->getAttribute($attributeCode);

        $this->assertIsArray($attribute);
        $this->assertArrayHasKey('attribute_id', $attribute);
        $this->assertArrayHasKey('attribute_code', $attribute);
        $this->assertEquals($attributeCode, $attribute['attribute_code']);
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'frontend_input',
                                'value' => 'textarea',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
            ],
            'entityTypeCode' => \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['default_frontend_label']);
        $this->assertNotNull($response['items'][0]['attribute_id']);
    }

    /**
     * Test create attribute
     *
     * @dataProvider attributeCodeDataProvider
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @param string $attributeCode
     * @return void
     */
    public function testCreate(string $attributeCode): void
    {
        $attribute = $this->createAttribute($attributeCode);

        $expectedData = [
            'attribute_code' => $attributeCode,
            'is_required' => true,
            'entity_type_id' => "4",
            "frontend_input" => "select",
            "is_visible_on_front" => true,
            "is_searchable" => true,
            "is_visible_in_advanced_search" => true,
            "is_filterable" => true,
            "is_filterable_in_search" => true,
        ];

        $this->assertEquals('default_label', $attribute['default_frontend_label']);
        $this->assertEquals('front_lbl_store1', $attribute['frontend_labels'][0]['label']);
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $attribute[$key]);
        }
        //Validate options
        //'Blue' should be first as it has sort_order = 0
        $this->assertEquals('Default Blue', $attribute['options'][1]['label']);
        $this->assertArrayHasKey('default_value', $attribute);
        //'Blue' should be set as default
        $this->assertEquals($attribute['default_value'], $attribute['options'][1]['value']);
        $this->assertEquals('Default Red', $attribute['options'][2]['label']);
    }

    /**
     * @return array
     */
    public function attributeCodeDataProvider(): array
    {
        return [
            [str_repeat('az_7', 15)],
            [uniqid('label_attr_code')],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_attribute.php
     * @return void
     */
    public function testCreateWithExceptionIfAttributeAlreadyExists()
    {
        $attributeCode = 'test_attribute_code_333';
        try {
            $this->createAttribute($attributeCode);
            $this->fail("Expected exception");
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\SoapFault $e) {
            //Expects soap exception
        } catch (\Exception $e) {
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @return void
     */
    public function testUpdate()
    {
        $attributeCode = uniqid('label_attr_code');
        $attribute = $this->createAttribute($attributeCode);

        //Make sure that 'Blue' is set as default
        $this->assertEquals($attribute['default_value'], $attribute['options'][1]['value']);
        $attributeData = [
            'attribute' => [
                'attribute_id' => $attribute['attribute_id'],
                'attribute_code' => $attributeCode,
                'entity_type_id' => 4,
                'is_used_in_grid' => true,
                //Update existing
                'default_frontend_label' => 'default_label_new',
                'frontend_labels' => [
                    //Update existing
                    ['store_id' => 0, 'label' => 'front_lbl_store0_new'],
                    ['store_id' => 1, 'label' => 'front_lbl_store1_new'],
                ],
                "options" => [
                    //Update existing
                    [
                        "value" => $attribute['options'][1]['value'],
                        "label" => "New Label",
                        "store_labels" => [
                            [
                                "store_id" => 1,
                                "label" => "Default Blue Updated"
                            ]
                        ]
                    ],
                    //Add new option
                    [
                        "label" => "Green",
                        "value" => "",
                        "sort_order" => 200,
                        "is_default" => true,
                        "store_labels" => [
                            [
                                "store_id" => 0,
                                "label" => "Admin Green"
                            ],
                            [
                                "store_id" => 1,
                                "label" => "Default Green"
                            ]
                        ]
                    ]
                ],
                'is_required' => false,
                'frontend_input' => 'select',
            ],
        ];
        $result = $this->updateAttribute($attributeCode, $attributeData);

        $this->assertEquals($attribute['attribute_id'], $result['attribute_id']);
        $this->assertTrue($result['is_used_in_grid']);
        $this->assertEquals($attributeCode, $result['attribute_code']);
        $this->assertEquals('default_label_new', $result['default_frontend_label']);
        $this->assertEquals('front_lbl_store1_new', $result['frontend_labels'][0]['label']);
        //New option set as default
        $this->assertEquals($result['options'][3]['value'], $result['default_value']);
        $this->assertEquals("Default Blue Updated", $result['options'][1]['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @return void
     */
    public function testUpdateWithNoDefaultLabelAndAdminStorelabel()
    {
        $attributeCode = uniqid('label_attr_code');
        $attribute = $this->createAttribute($attributeCode);

        $attributeData = [
            'attribute' => [
                'attribute_id' => $attribute['attribute_id'],
                'attribute_code' => $attributeCode,
                'entity_type_id' => 4,
                'is_used_in_grid' => true,
                'frontend_labels' => [
                    //Update existing
                    ['store_id' => 0, 'label' => 'front_lbl_store0_new'],
                    ['store_id' => 1, 'label' => 'front_lbl_store1_new'],
                ],
                'is_required' => false,
                'frontend_input' => 'select',
            ],
        ];
        $result = $this->updateAttribute($attributeCode, $attributeData);

        $this->assertEquals($attribute['attribute_id'], $result['attribute_id']);
        $this->assertTrue($result['is_used_in_grid']);
        $this->assertEquals($attributeCode, $result['attribute_code']);
        $this->assertEquals('front_lbl_store0_new', $result['default_frontend_label']);
        $this->assertEquals('front_lbl_store1_new', $result['frontend_labels'][0]['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @return void
     */
    public function testUpdateWithNoDefaultLabelAndNoAdminStoreLabel()
    {
        $attributeCode = uniqid('label_attr_code');
        $attribute = $this->createAttribute($attributeCode);

        $attributeData = [
            'attribute' => [
                'attribute_id' => $attribute['attribute_id'],
                'attribute_code' => $attributeCode,
                'entity_type_id' => 4,
                'is_used_in_grid' => true,
                'frontend_labels' => [
                    //Update existing
                    ['store_id' => 1, 'label' => 'front_lbl_store1_new'],
                ],
                'is_required' => false,
                'frontend_input' => 'select',
            ],
        ];
        $result = $this->updateAttribute($attributeCode, $attributeData);

        $this->assertEquals($attribute['attribute_id'], $result['attribute_id']);
        $this->assertTrue($result['is_used_in_grid']);
        $this->assertEquals($attributeCode, $result['attribute_code']);
        $this->assertEquals('default_label', $result['default_frontend_label']);
        $this->assertEquals('front_lbl_store1_new', $result['frontend_labels'][0]['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @return void
     */
    public function testUpdateWithNewOption()
    {
        $attributeCode = uniqid('label_attr_code');
        $attribute = $this->createAttribute($attributeCode);

        $attributeData = [
            'attribute' => [
                'attribute_id' => $attribute['attribute_id'],
                'attribute_code' => $attributeCode,
                'entity_type_id' => 4,
                'is_required' => true,
                'frontend_labels' => [
                    ['store_id' => 0, 'label' => 'front_lbl_new'],
                ],
                "options" => [
                    [
                        "value" => 'option',
                        "label" => "New Label",
                        "store_labels" => [
                            [
                                "store_id" => 1,
                                "label" => "new label"
                            ]
                        ]
                    ],
                ],
                'frontend_input' => 'select',
            ]
        ];

        $output = $this->updateAttribute($attributeCode, $attributeData);
        $this->assertCount(4, $output['options']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_attribute.php
     * @return void
     */
    public function testDeleteById()
    {
        $attributeCode = 'test_attribute_code_333';
        $this->assertTrue($this->deleteAttribute($attributeCode));
    }

    /**
     * Trying to delete system attribute.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_system_attribute.php
     * @return void
     */
    public function testDeleteSystemAttributeById(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The system attribute can\'t be deleted.');

        $attributeCode = 'test_attribute_code_333';
        $this->deleteAttribute($attributeCode);
    }

    /**
     * @return void
     */
    public function testDeleteNoSuchEntityException()
    {
        $attributeCode = 'some_test_code';
        $expectedMessage =
            'The attribute with a "%1" attributeCode doesn\'t exist. Verify the attribute and try again.';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];

        try {
            $this->_webApiCall($serviceInfo, ['attributeCode' => $attributeCode]);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$attributeCode], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * @param $attributeCode
     * @return array|bool|float|int|string
     */
    protected function createAttribute($attributeCode)
    {
        $attributeData = [
            'attribute' => [
                'attribute_code' => $attributeCode,
                'entity_type_id' => '4',
                "default_frontend_label" => 'default_label',
                'frontend_labels' => [
                    ['store_id' => 0, 'label' => 'front_lbl_store0'],
                    ['store_id' => 1, 'label' => 'front_lbl_store1'],
                ],
                'is_required' => true,
                "default_value" => "",
                "frontend_input" => "select",
                "is_visible_on_front" => true,
                "is_searchable" => true,
                "is_visible_in_advanced_search" => true,
                "is_filterable" => true,
                "is_filterable_in_search" => true,
                "options" => [
                    [
                        "label" => "Red",
                        "value" => "",
                        "sort_order" => 100,
                        "is_default" => false,
                        "store_labels" => [
                            [
                                "store_id" => 0,
                                "label" => "Admin Red"
                            ],
                            [
                                "store_id" => 1,
                                "label" => "Default Red"
                            ]
                        ]
                    ],
                    [
                        "label" => "Blue",
                        "value" => "",
                        "sort_order" => 0,
                        "is_default" => true,
                        "store_labels" => [
                            [
                                "store_id" => 0,
                                "label" => "Admin Blue"
                            ],
                            [
                                "store_id" => 1,
                                "label" => "Default Blue"
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $attribute = $this->_webApiCall($serviceInfo, $attributeData);
        if (isset($attribute['attribute_id']) && $attribute['attribute_id']) {
            $this->createdAttributes[] = $attributeCode;
        }
        return $attribute;
    }

    /**
     * @param $attributeCode
     * @return array|bool|float|int|string
     */
    protected function getAttribute($attributeCode)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['attributeCode' => $attributeCode]);
    }

    /**
     * Delete attribute by code
     *
     * @param $attributeCode
     * @return array|bool|float|int|string
     */
    protected function deleteAttribute($attributeCode)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['attributeCode' => $attributeCode]);
    }

    /**
     * Update attribute by code
     *
     * @param $attributeCode
     * @return array|bool|float|int|string
     */
    protected function updateAttribute($attributeCode, $attributeData)
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $attributeData['attribute']['attributeCode'] = $attributeCode;
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $attributeData);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->createdAttributes as $attributeCode) {
            $this->deleteAttribute($attributeCode);
        }
    }
}
