<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class VariationMatrixTest extends TestCase
{
    /** @var VariationMatrix */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            VariationMatrix::class
        );
    }

    /**
     * Variations matrix test.
     *
     * @param array $result
     * @param array $input
     */
    #[DataProvider('variationProvider')]
    public function testGetVariations($result, $input)
    {
        $this->assertEquals($result, $this->model->getVariations($input));
    }

    /**
     * Test data provider.
     */

    public static function variationProvider()
    {
        return [
            [
                // result parameter
                [
                    [
                        130 => [
                            'value' => '3',
                            'label' => 'red',
                            'price' => [
                                'value_index' => '3',
                                'pricing_value' => '',
                                'is_percent' => '0',
                                'include' => '1'
                            ],
                        ],
                    ],
                    [
                        130 => [
                            'value' => '4',
                            'label' => 'blue',
                            'price' => [
                                'value_index' => '4',
                                'pricing_value' => '',
                                'is_percent' => '0',
                                'include' => '1'
                            ],
                        ],
                    ],
                ],
                // input parameter
                [
                    130 => [
                        'values' => [
                            [
                                'value_index' => '3',
                                'pricing_value' => '',
                                'is_percent' => '0',
                                'include' => '1'
                            ],
                            [
                                'value_index' => '4',
                                'pricing_value' => '',
                                'is_percent' => '0',
                                'include' => '1'
                            ],
                        ],
                        'attribute_id' => '130',
                        'options' => [
                            [
                                'value' => '3',
                                'label' => 'red'
                            ],
                            ['value' => '4',
                                'label' => 'blue'
                            ]
                        ],
                    ],
                ]
            ]
        ];
    }
}
