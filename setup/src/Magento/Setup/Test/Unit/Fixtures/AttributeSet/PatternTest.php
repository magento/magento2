<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\AttributeSet;

/**
 * @SuppressWarnings(PHPMD)
 */
class PatternTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerateAttributeSet()
    {
        $attributeSets = [
            'name' => 'attribute set name',
            'attributes' => [
                'attribute' => [
                    [
                        'is_required' => 1,
                        'is_visible_on_front' => 1,
                        'is_visible_in_advanced_search' => 0,
                        'is_filterable' => 0,
                        'is_filterable_in_search' => 0,
                        'attribute_code' => 'attribute_1',
                        'is_searchable' => 0,
                        'frontend_label' => 'Attribute 1',
                        'frontend_input' => 'select',
                        'backend_type' => 1,
                        'default_value' => 'option_1',
                        'options' => [
                            'option' => [
                                [
                                    'label' => 'option 1',
                                    'value' => 'option_1'
                                ],
                                [
                                    'label' => 'option 2',
                                    'value' => 'option_2'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $pattern = new \Magento\Setup\Fixtures\AttributeSet\Pattern();
        $this->assertEquals(
            $attributeSets,
            $pattern->generateAttributeSet(
                'attribute set name',
                1,
                2,
                function ($index, $attributeData) {
                    $attributeData['backend_type'] = $index;
                    return $attributeData;
                }
            )
        );
    }
}
