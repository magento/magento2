<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->model = $objectManager->getObject('Magento\Quote\Model\Quote\Address\Total\Grand');
    }

    public function testCollect()
    {
        $totals = [1, 2, 3.4];
        $totalsBase = [4, 5, 6.7];
        $grandTotal = 6.4; // 1 + 2 + 3.4
        $grandTotalBase = 15.7; // 4 + 5 + 6.7

        $addressMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
            ['getAllTotalAmounts', 'getAllBaseTotalAmounts', 'setGrandTotal', 'setBaseGrandTotal', '__wakeup'],
            [],
            '',
            false
        );
        $addressMock->expects($this->once())->method('getAllTotalAmounts')->willReturn($totals);
        $addressMock->expects($this->once())->method('getAllBaseTotalAmounts')->willReturn($totalsBase);
        $addressMock->expects($this->once())->method('setGrandTotal')->with($grandTotal);
        $addressMock->expects($this->once())->method('setBaseGrandTotal')->with($grandTotalBase);

        $this->model->collect($addressMock);
    }
}
