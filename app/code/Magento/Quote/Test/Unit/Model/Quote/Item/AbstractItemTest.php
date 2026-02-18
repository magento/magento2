<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AbstractItemTest extends TestCase
{
    use MockCreationTrait;
    /**
     * Test the getTotalDiscountAmount function
     *
     * @param float|int $expectedDiscountAmount
     * @param array     $children
     * @param bool      $calculated
     * @param float|int $myDiscountAmount
     */
    #[DataProvider('dataProviderGetTotalDiscountAmount')]
    public function testGetTotalDiscountAmount($expectedDiscountAmount, $children, $calculated, $myDiscountAmount)
    {
        $finalChildMock = [];
        foreach ($children as $child) {
            $finalChildMock[] = $child($this);
        }
        $abstractItemMock = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode', 'isChildrenCalculated', 'getChildren', 'getDiscountAmount']
        );
        $abstractItemMock->method('isChildrenCalculated')->willReturn($calculated);
        $abstractItemMock->method('getChildren')->willReturn($finalChildMock);
        $abstractItemMock->method('getDiscountAmount')->willReturn($myDiscountAmount);

        $totalDiscountAmount = $abstractItemMock->getTotalDiscountAmount();
        $this->assertEquals($expectedDiscountAmount, $totalDiscountAmount);
    }

    protected function getMockForAbstractItem($childDiscountAmount)
    {
        $childItemMock = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode', 'getDiscountAmount']
        );
        $childItemMock->method('getDiscountAmount')->willReturn($childDiscountAmount);

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
