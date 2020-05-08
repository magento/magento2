<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\System\Config\Source\Coupon;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Helper\Coupon;
use Magento\SalesRule\Model\System\Config\Source\Coupon\Format;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    /**
     * @var Format|MockObject
     */
    protected $model;

    /**
     * @var Coupon|MockObject
     */
    protected $salesRuleCoupon;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->salesRuleCoupon = $this->createMock(Coupon::class);

        $this->model = $objectManager->getObject(
            Format::class,
            [
                'salesRuleCoupon' => $this->salesRuleCoupon
            ]
        );
    }

    public function testToOptionArray()
    {
        $formatTitle = 'format Title';
        $expected = [
            [
                'label' => $formatTitle,
                'value' => 0,
            ],
        ];
        $this->salesRuleCoupon->expects($this->once())
            ->method('getFormatsList')
            ->willReturn([$formatTitle]);

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
