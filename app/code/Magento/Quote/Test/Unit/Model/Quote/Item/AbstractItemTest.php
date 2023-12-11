<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\TestCase;

class AbstractItemTest extends TestCase
{
    /**
     * Test the getTotalDiscountAmount function
     *
     * @param float|int $expectedDiscountAmount
     * @param array     $children
     * @param bool      $calculated
     * @param float|int $myDiscountAmount
     * @dataProvider    dataProviderGetTotalDiscountAmount
     */
    public function testGetTotalDiscountAmount($expectedDiscountAmount, $children, $calculated, $myDiscountAmount)
    {
        $abstractItemMock = $this->getMockForAbstractClass(
            AbstractItem::class,
            [],
            '',
            false,
            false,
            true,
            ['getChildren', 'isChildrenCalculated', 'getDiscountAmount']
        );
        $abstractItemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn($children);
        $abstractItemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn($calculated);
        $abstractItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($myDiscountAmount);

        $totalDiscountAmount = $abstractItemMock->getTotalDiscountAmount();
        $this->assertEquals($expectedDiscountAmount, $totalDiscountAmount);
    }

    /**
     * @return array
     */
    public function dataProviderGetTotalDiscountAmount()
    {
        $childOneDiscountAmount = 1000;
        $childOneItemMock = $this->getMockForAbstractClass(
            AbstractItem::class,
            [],
            '',
            false,
            false,
            true,
            ['getDiscountAmount']
        );
        $childOneItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($childOneDiscountAmount);

        $childTwoDiscountAmount = 50;
        $childTwoItemMock = $this->getMockForAbstractClass(
            AbstractItem::class,
            [],
            '',
            false,
            false,
            true,
            ['getDiscountAmount']
        );
        $childTwoItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($childTwoDiscountAmount);

        $valueHasNoEffect = 0;

        $data = [
            'no_children' => [
                10,
                [],
                false,
                10,
            ],
            'kids_but_not_calculated' => [
                10,
                [$childOneItemMock],
                false,
                10,
            ],
            'one_kid' => [
                $childOneDiscountAmount,
                [$childOneItemMock],
                true,
                $valueHasNoEffect,
            ],
            'two_kids' => [
                $childOneDiscountAmount + $childTwoDiscountAmount,
                [$childOneItemMock, $childTwoItemMock],
                true,
                $valueHasNoEffect,
            ],
        ];
        return $data;
    }
}
