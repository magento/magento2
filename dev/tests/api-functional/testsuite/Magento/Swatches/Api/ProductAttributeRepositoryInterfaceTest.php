<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Api;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test attribute management API for swatch attribute type
 */
class ProductAttributeRepositoryInterfaceTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogProductAttributeRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products/attributes';
    /**
     * @var array
     */
    private $createdAttributes = [];

    /**
     * Test create swatch attribute
     *
     * @param array $payload
     * @param array $expected
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/create_attribute_service.php
     * @dataProvider saveDataProvider
     */
    public function testSave(
        array $payload,
        array $expected
    ) {
        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH,
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_NAME . 'Save',
                ],
            ],
            [
                'attribute' => $payload,
            ]
        );

        $this->assertNotNull($response);
        if (!empty($response['attribute_id'])) {
            $this->createdAttributes[] = $payload['attribute_code'];
        }
        $expected = array_replace_recursive($response, $expected);
        $this->assertEquals($expected, $response);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveDataProvider(): array
    {
        return [
            'visual swatch option with value' => [
                'payload' => [
                    ProductAttributeInterface::ATTRIBUTE_CODE => 'visual_swatch_attr_20200713',
                    ProductAttributeInterface::FRONTEND_INPUT => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
                    ProductAttributeInterface::IS_USER_DEFINED => true,
                    ProductAttributeInterface::IS_REQUIRED => false,
                    ProductAttributeInterface::ENTITY_TYPE_ID => 4,
                    'default_frontend_label' => 'Visual Swatch Attribute',
                    ProductAttributeInterface::OPTIONS => [
                        [
                            AttributeOptionInterface::LABEL => 'Black',
                            AttributeOptionInterface::VALUE => '#000000',
                            AttributeOptionInterface::SORT_ORDER => 1,
                            AttributeOptionInterface::IS_DEFAULT => true,
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'White',
                            AttributeOptionInterface::VALUE => '#ffffff',
                            AttributeOptionInterface::SORT_ORDER => 2
                        ]
                    ],
                    ProductAttributeInterface::FRONTEND_LABELS => [
                        [
                            AttributeOptionLabelInterface::STORE_ID => 0,
                            AttributeOptionLabelInterface::LABEL => 'Visual Swatch Attribute'
                        ],
                    ],
                ],
                'expected' => [
                    ProductAttributeInterface::ATTRIBUTE_CODE => 'visual_swatch_attr_20200713',
                    ProductAttributeInterface::FRONTEND_INPUT => 'select',
                    ProductAttributeInterface::OPTIONS => [
                        [
                            AttributeOptionInterface::LABEL => ' ',
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'Black',
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'White',
                        ]
                    ]
                ],
            ],
            'text swatch option with value' => [
                'payload' => [
                    ProductAttributeInterface::ATTRIBUTE_CODE => 'text_swatch_attr_20200713',
                    ProductAttributeInterface::FRONTEND_INPUT => Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
                    ProductAttributeInterface::IS_USER_DEFINED => true,
                    ProductAttributeInterface::IS_REQUIRED => false,
                    ProductAttributeInterface::ENTITY_TYPE_ID => 4,
                    'default_frontend_label' => 'Text Swatch Attribute',
                    ProductAttributeInterface::OPTIONS => [
                        [
                            AttributeOptionInterface::LABEL => 'Small',
                            AttributeOptionInterface::VALUE => 'S',
                            AttributeOptionInterface::SORT_ORDER => 1,
                            AttributeOptionInterface::IS_DEFAULT => true,
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'Medium',
                            AttributeOptionInterface::VALUE => 'M',
                            AttributeOptionInterface::SORT_ORDER => 2
                        ]
                    ],
                    ProductAttributeInterface::FRONTEND_LABELS => [
                        [
                            AttributeOptionLabelInterface::STORE_ID => 0,
                            AttributeOptionLabelInterface::LABEL => 'Text Swatch Attribute'
                        ],
                    ],
                ],
                'expected' => [
                    ProductAttributeInterface::ATTRIBUTE_CODE => 'text_swatch_attr_20200713',
                    ProductAttributeInterface::FRONTEND_INPUT => 'select',
                    ProductAttributeInterface::OPTIONS => [
                        [
                            AttributeOptionInterface::LABEL => ' ',
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'Small',
                        ],
                        [
                            AttributeOptionInterface::LABEL => 'Medium',
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * Delete attribute by code
     *
     * @param $attributeCode
     * @return bool
     */
    private function deleteAttribute($attributeCode): bool
    {
        return $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH . '/' . $attributeCode,
                    'httpMethod' => Request::HTTP_METHOD_DELETE,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_NAME . 'deleteById',
                ],
            ],
            [
                'attributeCode' => $attributeCode
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->createdAttributes as $attributeCode) {
            $this->deleteAttribute($attributeCode);
        }
        parent::tearDown();
    }
}
