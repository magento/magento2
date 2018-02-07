<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleWeight;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;

class BundleWeightTest extends AbstractModifierTest
{
    /**
     * @return BundleWeight
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            BundleWeight::class,
            ['arrayManager' => $this->arrayManagerMock]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testModifyMeta()
    {
        $weightTypePath = 'bundle-items/children/' . BundleWeight::CODE_WEIGHT_TYPE;
        $weightTypeConfigPath = $weightTypePath . BundleWeight::META_CONFIG_PATH;
        $weightPath = 'product-details/children/' . ProductAttributeInterface::CODE_WEIGHT;
        $weightConfigPath = $weightPath . BundleWeight::META_CONFIG_PATH;
        $hasWeightPath = 'product-details/children/' . ProductAttributeInterface::CODE_HAS_WEIGHT;
        $hasWeightConfigPath = $hasWeightPath . BundleWeight::META_CONFIG_PATH;
        $sourceMeta = [
            'product-details' => [
                'children' => [
                    ProductAttributeInterface::CODE_WEIGHT => [],
                    ProductAttributeInterface::CODE_HAS_WEIGHT => []
                ]
            ],
            'bundle-items' => [
                'children' => [
                    BundleWeight::CODE_WEIGHT_TYPE => []
                ]
            ]
        ];
        $weightTypeParams = [
            'valueMap' => [
                'false' => '1',
                'true' => '0'
            ],
            'validation' => [
                'required-entry' => false
            ]
        ];
        $weightParams = [
            'imports' => [
                'disabled' => 'ns = ${ $.ns }, index = ' . BundleWeight::CODE_WEIGHT_TYPE . ':checked',
            ]
        ];
        $hasWeightParams = [
            'disabled' => true,
            'visible' => false
        ];
        $weightTypeMeta = [
            'product-details' => [
                'children' => [
                    ProductAttributeInterface::CODE_WEIGHT => [],
                    ProductAttributeInterface::CODE_HAS_WEIGHT => []
                ]
            ],
            'bundle-items' => [
                'children' => [
                    BundleWeight::CODE_WEIGHT_TYPE => $weightTypeParams
                ]
            ]
        ];
        $hasWeightMeta = [
            'product-details' => [
                'children' => [
                    ProductAttributeInterface::CODE_WEIGHT => [],
                    ProductAttributeInterface::CODE_HAS_WEIGHT => $hasWeightParams
                ]
            ],
            'bundle-items' => [
                'children' => [
                    BundleWeight::CODE_WEIGHT_TYPE => $weightTypeParams
                ]
            ]
        ];
        $weightMeta = [
            'product-details' => [
                'children' => [
                    ProductAttributeInterface::CODE_WEIGHT => $weightParams,
                    ProductAttributeInterface::CODE_HAS_WEIGHT => $hasWeightParams
                ]
            ],
            'bundle-items' => [
                'children' => [
                    BundleWeight::CODE_WEIGHT_TYPE => $weightTypeParams
                ]
            ]
        ];

        $this->arrayManagerMock->expects(static::any())
            ->method('findPath')
            ->willReturnMap(
                [
                    [
                        BundleWeight::CODE_WEIGHT_TYPE,
                        $sourceMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $weightTypePath
                    ],
                    [
                        ProductAttributeInterface::CODE_HAS_WEIGHT,
                        $weightTypeMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $hasWeightPath
                    ],
                    [
                        ProductAttributeInterface::CODE_WEIGHT,
                        $hasWeightMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $weightPath
                    ]
                ]
            );
        $this->arrayManagerMock->expects($this->exactly(3))
            ->method('merge')
            ->willReturnMap(
                [
                    [
                        $weightTypeConfigPath,
                        $sourceMeta,
                        $weightTypeParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $weightTypeMeta
                    ],
                    [
                        $hasWeightConfigPath,
                        $weightTypeMeta,
                        $hasWeightParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $hasWeightMeta
                    ],
                    [
                        $weightConfigPath,
                        $hasWeightMeta,
                        $weightParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $weightMeta
                    ]
                ]
            );

        $this->assertSame($weightMeta, $this->getModel()->modifyMeta($sourceMeta));
    }

    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}
