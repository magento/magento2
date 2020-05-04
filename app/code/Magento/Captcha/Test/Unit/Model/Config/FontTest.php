<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Config;

use Magento\Captcha\Helper\Data as HelperData;
use Magento\Captcha\Model\Config\Font;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Font
     */
    private $model;

    /**
     * @var HelperData|MockObject
     */
    private $helperDataMock;

    /**
     * Setup Environment For Testing
     */
    protected function setUp(): void
    {
        $this->helperDataMock = $this->createMock(HelperData::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            Font::class,
            [
                'captchaData' => $this->helperDataMock
            ]
        );
    }

    /**
     * Test toOptionArray() with data provider below
     *
     * @param array $fonts
     * @param array $expectedResult
     * @dataProvider toOptionArrayDataProvider
     */
    public function testToOptionArray($fonts, $expectedResult)
    {
        $this->helperDataMock->expects($this->any())->method('getFonts')
            ->willReturn($fonts);

        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }

    /**
     * Data Provider for testing toOptionArray()
     *
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return [
            'Empty get font' => [
                [],
                []
            ],
            'Get font result' => [
                [
                    'arial' => [
                        'label' => 'Arial',
                        'path' => '/www/magento/fonts/arial.ttf'
                    ],
                    'verdana' => [
                        'label' => 'Verdana',
                        'path' => '/www/magento/fonts/verdana.ttf'
                    ]
                ],
                [
                    [
                        'label' => 'Arial',
                        'value' => 'arial'
                    ],
                    [
                        'label' => 'Verdana',
                        'value' => 'verdana'
                    ]
                ]
            ]
        ];
    }
}
