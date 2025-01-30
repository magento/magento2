<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Track;

use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\Track\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|MockObject
     */
    protected $trackModelMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->trackModelMock = $this->createPartialMock(
            Track::class,
            ['hasData', 'getData']
        );
        $this->validator = new Validator();
    }

    /**
     * Run test validate
     *
     * @param $trackDataMap
     * @param $trackData
     * @param $expectedWarnings
     * @dataProvider providerTrackData
     */
    public function testValidate($trackDataMap, $trackData, $expectedWarnings)
    {
        $this->trackModelMock->expects($this->any())
            ->method('hasData')
            ->willReturnMap($trackDataMap);
        $this->trackModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($trackData);
        $actualWarnings = $this->validator->validate($this->trackModelMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides track data for tests
     *
     * @return array
     */
    public static function providerTrackData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['order_id', true],
                    ['track_number', true],
                    ['carrier_code', true],
                ],
                [
                    'parent_id' => 25,
                    'order_id' => 12,
                    'track_number' => 125,
                    'carrier_code' => 'custom'
                ],
                [],
            ],
            [
                [
                    ['parent_id', true],
                    ['order_id', false],
                    ['track_number', true],
                    ['carrier_code', false],
                ],
                [
                    'parent_id' => 0,
                    'order_id' => null,
                    'track_number' => '',
                    'carrier_code' => null
                ],
                [
                    'parent_id' => 'Parent Track Id can not be empty',
                    'order_id' => '"Order Id" is required. Enter and try again.',
                    'track_number' => 'Number can not be empty',
                    'carrier_code' => '"Carrier Code" is required. Enter and try again.'
                ]
            ]
        ];
    }
}
