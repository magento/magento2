<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\System\Config\Source\Coupon;

class FormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\System\Config\Source\Coupon\Format|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Helper\Coupon|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesRuleCoupon;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->salesRuleCoupon = $this->createMock(\Magento\SalesRule\Helper\Coupon::class);

        $this->model = $objectManager->getObject(
            \Magento\SalesRule\Model\System\Config\Source\Coupon\Format::class,
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
