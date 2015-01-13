<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\System\Config\Source\Coupon;

class FormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\System\Config\Source\Coupon\Format|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Helper\Coupon|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesRuleCoupon;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->salesRuleCoupon = $this->getMock(
            'Magento\SalesRule\Helper\Coupon',
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            'Magento\SalesRule\Model\System\Config\Source\Coupon\Format',
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
            ->will($this->returnValue([$formatTitle]));

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
