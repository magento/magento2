<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Catalog\Model\Product\Image\ConvertImageMiscParamsToReadableFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test convert image misc params to readable format
 */
class ConvertImageMiscParamsToReadableFormatTest extends TestCase
{
    /**
     * @var ConvertImageMiscParamsToReadableFormat|MockObject
     */
    protected ConvertImageMiscParamsToReadableFormat|MockObject $model;

    protected function setUp(): void
    {
        $this->model = new ConvertImageMiscParamsToReadableFormat();
    }

    /**
     * @param array $data
     * @return void
     * @dataProvider createDataProvider
     */
    public function testConvertImageMiscParamsToReadableFormat(array $data): void
    {
        $this->assertEquals(
            $data['expectedMiscParamsWithArray'],
            $this->model->convertImageMiscParamsToReadableFormat(
                $data['convertImageParamsToReadableFormatWithArray']
            )
        );
        $this->assertEquals(
            $data['expectedMiscParamsWithOutArray'],
            $this->model->convertImageMiscParamsToReadableFormat(
                $data['convertImageParamsToReadableFormatWithOutArray']
            )
        );
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            $this->getTestDataWithAttributes()
        ];
    }

    /**
     * @return array
     */
    private function getTestDataWithAttributes(): array
    {
        return [
            'data' => [
                'convertImageParamsToReadableFormatWithArray' => [
                    'image_height' => '50',
                    'image_width' => '100',
                    'quality' => '80',
                    'angle' => '90',
                    'keep_aspect_ratio' => 'proportional',
                    'keep_frame' => 'frame',
                    'keep_transparency' => 'transparency',
                    'constrain_only' => 'constrainonly',
                    'background' => [255,255,255]
                ],
                'convertImageParamsToReadableFormatWithOutArray' => [],
                'expectedMiscParamsWithArray' => [
                    'image_height' => 'h:50',
                    'image_width' => 'w:100',
                    'quality' => 'q:80',
                    'angle' => 'r:90',
                    'keep_aspect_ratio' => 'proportional',
                    'keep_frame' => 'frame',
                    'keep_transparency' => 'transparency',
                    'constrain_only' => 'doconstrainonly',
                    'background' => 'rgb255,255,255'
                ],
                'expectedMiscParamsWithOutArray' => [
                    'image_height' => 'h:empty',
                    'image_width' => 'w:empty',
                    'quality' => 'q:empty',
                    'angle' => 'r:empty',
                    'keep_aspect_ratio' => 'nonproportional',
                    'keep_frame' => 'noframe',
                    'keep_transparency' => 'notransparency',
                    'constrain_only' => 'notconstrainonly',
                    'background' => 'nobackground'
                ]
            ]
        ];
    }
}
