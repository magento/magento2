<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GrandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\Grand
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\Quote\Model\Quote\Address\Total\Grand::class);
    }

    public function testCollect()
    {
        $totals = [1, 2, 3.4];
        $totalsBase = [4, 5, 6.7];
        $grandTotal = 6.4; // 1 + 2 + 3.4
        $grandTotalBase = 15.7; // 4 + 5 + 6.7

        $totalMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address\Total::class,
            [
                'getAllTotalAmounts',
                'getAllBaseTotalAmounts',
                'setGrandTotal',
                'setBaseGrandTotal',
                'getGrandTotal',
                'getBaseGrandTotal'
            ],
            [],
            '',
            false
        );
        $totalMock->expects($this->once())->method('getGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getAllTotalAmounts')->willReturn($totals);
        $totalMock->expects($this->once())->method('getAllBaseTotalAmounts')->willReturn($totalsBase);
        $totalMock->expects($this->once())->method('setGrandTotal')->with($grandTotal + 2);
        $totalMock->expects($this->once())->method('setBaseGrandTotal')->with($grandTotalBase + 2);

        $this->model->collect(
            $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false),
            $this->getMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class),
            $totalMock
        );
    }
}
