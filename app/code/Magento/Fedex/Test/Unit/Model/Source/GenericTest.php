<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Model\Source;

use Magento\Fedex\Model\Carrier;
use Magento\Fedex\Model\Source\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Fedex\Test\Unit\Model\Source\Generic
 */
class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    private $model;

    /**
     * @var Carrier|MockObject
     */
    private $shippingFedexMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->shippingFedexMock = $this->createMock(Carrier::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Generic::class,
            [
                'shippingFedex' => $this->shippingFedexMock
            ]
        );
    }

    /**
     * Test toOptionArray
     *
     * @param string $code
     * @param array|false $methods
     * @param array $result
     * @return void
     * @dataProvider toOptionArrayDataProvider
     */
    public function testToOptionArray($methods, $result): void
    {
        $this->shippingFedexMock->expects($this->once())
            ->method('getCode')
            ->willReturn($methods);

        $this->assertEquals($result, $this->model->toOptionArray());
    }

    /**
     * Data provider for testToOptionArray()
     *
     * @return array
     */
    public static function toOptionArrayDataProvider(): array
    {
        return [
            [
                [
                    'FEDEX_GROUND' => __('Ground'),
                    'FIRST_OVERNIGHT' => __('First Overnight')
                ],
                [
                    ['value' => 'FEDEX_GROUND', 'label' => __('Ground')],
                    ['value' => 'FIRST_OVERNIGHT', 'label' => __('First Overnight')]
                ]
            ],
            [
                false,
                []
            ]
        ];
    }
}
