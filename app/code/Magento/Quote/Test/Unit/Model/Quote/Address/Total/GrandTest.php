<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Grand totals collector test.
 */
class GrandTest extends TestCase
{
    use MockCreationTrait;
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
        $this->priceRounder = $this->createPartialMock(\Magento\Directory\Model\PriceCurrency::class, ['roundPrice']);

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

        $totalMock = $this->createPartialMockWithReflection(
            Total::class,
            [
                'getAllTotalAmounts', 'getAllBaseTotalAmounts', 'getGrandTotal', 'getBaseGrandTotal',
                'setGrandTotal', 'setBaseGrandTotal'
            ]
        );
        $totalMock->method('getAllTotalAmounts')->willReturn($totals);
        $totalMock->method('getAllBaseTotalAmounts')->willReturn($totalsBase);
        
        // getGrandTotal called once in collect (returns 2), then in assertion (returns final value)
        $totalMock->method('getGrandTotal')->willReturnOnConsecutiveCalls(2, $grandTotal + 2);
        $totalMock->method('getBaseGrandTotal')->willReturnOnConsecutiveCalls(2, $grandTotalBase + 2);
        
        $totalMock->expects($this->once())->method('setGrandTotal')->with($grandTotal + 2);
        $totalMock->expects($this->once())->method('setBaseGrandTotal')->with($grandTotalBase + 2);

        $this->model->collect(
            $this->createMock(Quote::class),
            $this->createMock(ShippingAssignmentInterface::class),
            $totalMock
        );
        
        $this->assertEquals($grandTotal + 2, $totalMock->getGrandTotal());
        $this->assertEquals($grandTotalBase + 2, $totalMock->getBaseGrandTotal());
    }
}
