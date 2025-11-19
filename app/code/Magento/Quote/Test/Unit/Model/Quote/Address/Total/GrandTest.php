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
use Magento\Quote\Test\Unit\Helper\TotalTestHelper;
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

        $totalMock = new TotalTestHelper();
        $totalMock->setGrandTotal(2);
        $totalMock->setBaseGrandTotal(2);
        // Inject amounts via data to be read by getAllTotalAmounts methods
        foreach ($totals as $i => $val) {
            $totalMock->setTotalAmount('t' . $i, $val);
            $totalMock->setBaseTotalAmount('t' . $i, $totalsBase[$i]);
        }

        $this->model->collect(
            $this->createMock(Quote::class),
            $this->createMock(ShippingAssignmentInterface::class),
            $totalMock
        );
        $this->assertEquals($grandTotal + 2, $totalMock->getGrandTotal());
        $this->assertEquals($grandTotalBase + 2, $totalMock->getBaseGrandTotal());
    }
}
