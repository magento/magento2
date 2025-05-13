<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Source\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Source\Export\Format;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $exportConfigMock;

    /**
     * @var Format
     */
    private $model;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->exportConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Format::class,
            [
                'exportConfig' => $this->exportConfigMock
            ]
        );
    }

    /**
     * Test toOptionArray with data provider
     *
     * @param array $fileFormats
     * @param array $expected
     * @dataProvider toOptionArrayDataProvider
     */
    public function testToOptionArray($fileFormats, $expected)
    {
        $this->exportConfigMock->expects($this->any())->method('getFileFormats')->willReturn($fileFormats);

        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    /**
     * Data Provider for test toOptionArray
     *
     * @return array
     */
    public static function toOptionArrayDataProvider()
    {
        return [
            'Empty file format' => [
                [],
                []
            ],
            'Has file format' => [
                [
                    'fileFormat1' => [
                        'label' => 'File Format 1'
                    ]
                ],
                [
                    [
                        'label' => (string)__('File Format 1'),
                        'value' => 'fileFormat1'
                    ]
                ]
            ]
        ];
    }
}
