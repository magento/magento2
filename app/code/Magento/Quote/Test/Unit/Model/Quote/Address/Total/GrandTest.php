<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\Total\Grand;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceRounder;
use PHPUnit_Framework_MockObject_MockObject as ObjectMock;
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
    protected function setUp()
    {
        $this->priceRounder = $this->getMockBuilder(PriceRounder::class)
            ->disableOriginalConstructor()
            ->setMethods(['roundPrice'])
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Grand::class,
            [
                'priceRounder' => $this->priceRounder,
            ]
        );
    }

    public function testCollect()
    {
        $totals = [1, 2, 3.4];
        $totalsBase = [4, 5, 6.7];
        $grandTotal = 6.4; // 1 + 2 + 3.4
        $grandTotalBase = 15.7; // 4 + 5 + 6.7

        $this->priceRounder->expects($this->at(0))->method('roundPrice')->willReturn($grandTotal + 2);
        $this->priceRounder->expects($this->at(1))->method('roundPrice')->willReturn($grandTotalBase + 2);

        $totalMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Total::class,
            [
                'getAllTotalAmounts',
                'getAllBaseTotalAmounts',
                'setGrandTotal',
                'setBaseGrandTotal',
                'getGrandTotal',
                'getBaseGrandTotal'
            ]
        );
        $totalMock->expects($this->once())->method('getGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(2);
        $totalMock->expects($this->once())->method('getAllTotalAmounts')->willReturn($totals);
        $totalMock->expects($this->once())->method('getAllBaseTotalAmounts')->willReturn($totalsBase);
        $totalMock->expects($this->once())->method('setGrandTotal')->with($grandTotal + 2);
        $totalMock->expects($this->once())->method('setBaseGrandTotal')->with($grandTotalBase + 2);

        $this->model->collect(
            $this->createMock(\Magento\Quote\Model\Quote::class),
            $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class),
            $totalMock
        );
    }
}
