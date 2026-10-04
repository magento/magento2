<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePrice as ConfigurablePriceModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;

class ConfigurablePriceTest extends AbstractModifierTestCase
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
     */
    #[DataProvider('metaDataProvider')]
    public function testModifyMeta($metaInput, $metaOutput)
    {
        $this->productMock->setTypeId(Configurable::TYPE_CODE);
        $this->productMock->method('getTypeId')->willReturn(Configurable::TYPE_CODE);

        $metaResult = $this->getModel()->modifyMeta($metaInput);
        $this->assertEquals($metaResult, $metaOutput);
    }

    /**
     * @return array
     */
    public static function metaDataProvider()
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

    public function testModifyMetaRemovesScopeLabelAndServiceForConfigurable()
    {
        $locator = $this->createMock(LocatorInterface::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $locator->method('getProduct')->willReturn($product);

        $modifier = new ConfigurablePriceModifier($locator);

        $meta = [
            'product_details' => [
                'children' => [
                    ConfigurablePriceModifier::CODE_GROUP_PRICE => [
                        'children' => [
                            'price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'scopeLabel' => 'Some Label',
                                            'service' => 'Some Service'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $modifier->modifyMeta($meta);

        $config = $result['group']['children'][ConfigurablePriceModifier::CODE_GROUP_PRICE]
            ['children']['price']['arguments']['data']['config'] ?? [];
        $this->assertArrayNotHasKey('scopeLabel', $config);
        $this->assertArrayNotHasKey('service', $config);
    }
}
