<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePrice as ConfigurablePriceModifier;

class ConfigurablePriceTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(ConfigurablePriceModifier::class, ['locator' => $this->locatorMock]);
    }

    /**
     * @param array $metaInput
     * @param array $metaOutput
     * @dataProvider metaDataProvider
     */
    public function testModifyMeta($metaInput, $metaOutput)
    {
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $metaResult = $this->getModel()->modifyMeta($metaInput);
        $this->assertEquals($metaResult, $metaOutput);
    }

    /**
     * @return array
     */
    public function metaDataProvider()
    {
        $priceComponentConfig = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_ConfigurableProduct/js/components/price-configurable'
                    ]
                ]
            ]
        ];
        return [
            [
                'metaInput' => [
                    'product-details' => [
                        'children' => [
                            'container_price' => [
                                'children' => [
                                    'advanced_pricing_button' => [
                                        'arguments' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'metaOutput' => [
                    'product-details' => [
                        'children' => [
                            'container_price' => [
                                'children' => [
                                    'advanced_pricing_button' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'visible' => 0,
                                                    'disabled' => 1,
                                                    'componentType' => 'container'
                                                ],
                                            ],
                                        ],
                                    ],
                                    'price' => $priceComponentConfig,
                                ],
                            ],
                        ],
                    ]
                ]
            ], [
                'metaInput' => [
                    'product-details' => [
                        'children' => [
                            'container_price' => [
                                'children' => []
                            ]
                        ]
                    ]
                ],
                'metaOutput' => [
                    'product-details' => [
                        'children' => [
                            'container_price' => [
                                'children' => [
                                    'price' => $priceComponentConfig
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
