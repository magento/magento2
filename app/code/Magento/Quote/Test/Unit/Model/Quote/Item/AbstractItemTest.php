<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
        $finalChildMock = [];
        foreach ($children as $child) {
            $finalChildMock[] = $child($this);
        }
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
            ->willReturn($finalChildMock);
        $abstractItemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn($calculated);
        $abstractItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($myDiscountAmount);

        $totalDiscountAmount = $abstractItemMock->getTotalDiscountAmount();
        $this->assertEquals($expectedDiscountAmount, $totalDiscountAmount);
    }

    protected function getMockForAbstractItem($childDiscountAmount)
    {
        $childItemMock = $this->getMockForAbstractClass(
            AbstractItem::class,
            [],
            '',
            false,
            false,
            true,
            ['getDiscountAmount']
        );
        $childItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn($childDiscountAmount);

        return $childItemMock;
    }

    /**
     * @return array
     */
    public static function dataProviderGetTotalDiscountAmount()
    {
        $childOneDiscountAmount = 1000;

        $childOneItemMock = static fn (self $testCase) =>
        $testCase->getMockForAbstractItem($childOneDiscountAmount);

        $childTwoDiscountAmount = 50;
        $childTwoItemMock = static fn (self $testCase) =>
        $testCase->getMockForAbstractItem($childTwoDiscountAmount);

        $valueHasNoEffect = 0;
        $parentDiscountAmount = 10;

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
                $childOneDiscountAmount + $parentDiscountAmount,
                [$childOneItemMock],
                true,
                $parentDiscountAmount,
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
