<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface as PriceRounder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Grand;
use PHPUnit\Framework\MockObject\MockObject as ObjectMock;
use PHPUnit\Framework\TestCase;

/**
 * Grand totals collector test.
 */
class GrandTest extends TestCase
{
    /**
     * @var PriceRounder|ObjectMock
     */
    private $priceRounder;

    /**
     * @var Grand
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->priceRounder = $this->getMockBuilder(PriceRounder::class)
            ->disableOriginalConstructor()
            ->addMethods(['roundPrice'])
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Grand::class,
            [
                'priceRounder' => $this->priceRounder
            ]
        );
    }

    /**
     * @return void
     */
    public function testCollect(): void
    {
        $totals = [1, 2, 3.4];
        $totalsBase = [4, 5, 6.7];
        $grandTotal = 6.4; // 1 + 2 + 3.4
        $grandTotalBase = 15.7; // 4 + 5 + 6.7

        $this->priceRounder
            ->method('roundPrice')
            ->willReturnOnConsecutiveCalls($grandTotal + 2, $grandTotalBase + 2);

        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['setGrandTotal', 'setBaseGrandTotal', 'getGrandTotal', 'getBaseGrandTotal'])
            ->onlyMethods(['getAllTotalAmounts', 'getAllBaseTotalAmounts'])
            ->disableOriginalConstructor()
            ->getMock();
        $totalMock->expects($this->once())->method('getGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getAllTotalAmounts')->willReturn($totals);
        $totalMock->expects($this->once())->method('getAllBaseTotalAmounts')->willReturn($totalsBase);
        $totalMock->expects($this->once())->method('setGrandTotal')->with($grandTotal + 2);
        $totalMock->expects($this->once())->method('setBaseGrandTotal')->with($grandTotalBase + 2);

        $this->model->collect(
            $this->createMock(Quote::class),
            $this->getMockForAbstractClass(ShippingAssignmentInterface::class),
            $totalMock
        );
    }
}
