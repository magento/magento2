<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput;
use Magento\Swatches\Model\Swatch;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput.
 */
class ConvertSwatchAttributeFrontendInputTest extends TestCase
{
    /**
     * @var ConvertSwatchAttributeFrontendInput
     */
    private $convertSwatchAttributeFrontendInput;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->convertSwatchAttributeFrontendInput =
            $objectManager->getObject(ConvertSwatchAttributeFrontendInput::class);
    }

    /**
     * @dataProvider attributeData
     */
    public function testExecute($inputData, $outputData)
    {
        $result = $this->convertSwatchAttributeFrontendInput->execute($inputData);
        $this->assertEquals($outputData, $result);
    }

    /**
     * @return array
     */
    public static function attributeData()
    {
        return [
            [
                [
                    'frontend_input' => 'swatch_visual'
                ],
                [
                    'frontend_input' => 'select',
                    Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_VISUAL,
                ]
            ],
            [
                [
                    'frontend_input' => 'swatch_text'
                ],
                [
                    'frontend_input' => 'select',
                    Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT,
                    'use_product_image_for_swatch' => 0
                ]
            ],
            [
                [
                    'frontend_input' => 'select'
                ],
                [
                    'frontend_input' => 'select',
                    Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_DROPDOWN,
                ]
            ],
            [
                [],
                []
            ],
            [
                null,
                null
            ],
        ];
    }
}
