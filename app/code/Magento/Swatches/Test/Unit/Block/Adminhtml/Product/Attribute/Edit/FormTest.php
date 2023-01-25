<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Block\Adminhtml\Product\Attribute\Edit\Form;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @dataProvider dataForAddValues
     */
    public function testAddValues($values)
    {
        $objectManager = new ObjectManager($this);
        $block = $objectManager->getObject(Form::class);
        $result= $block->addValues($values);
        $this->assertEquals($block, $result);
    }

    /**
     * @return array
     */
    public function dataForAddValues()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        return [
            [
                [
                    'frontend_input' => 'select',
                    'swatch_input_type' => 'text',
                ]
            ],
            [
                [
                    'frontend_input' => 'textarea',
                ]
            ],
            [
                [
                    'frontend_input' => 'select',
                ]
            ],
            [
                'wrong_string_value',
            ],
            [
                [
                    'additional_data' => json_encode($additionalData),
                    'frontend_input' => 'select',
                ]
            ],
            [
                [
                    'additional_data' => '',
                    'frontend_input' => 'select',
                ]
            ],
        ];
    }
}
