<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Item;

/**
 * Class AbstractItemTest
 */
class AbstractItemTest extends \PHPUnit_Framework_TestCase
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
            'Magento\Quote\Model\Quote\Item\AbstractItem',
            [],
            '',
            false,
            false,
            true,
            ['getChildren', 'isChildrenCalculated', 'getDiscountAmount']
        );
        $abstractItemMock->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue($children));
        $abstractItemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->will($this->returnValue($calculated));
        $abstractItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->will($this->returnValue($myDiscountAmount));

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
            'Magento\Quote\Model\Quote\Item\AbstractItem',
            [],
            '',
            false,
            false,
            true,
            ['getDiscountAmount']
        );
        $childOneItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->will($this->returnValue($childOneDiscountAmount));

        $childTwoDiscountAmount = 50;
        $childTwoItemMock = $this->getMockForAbstractClass(
            'Magento\Quote\Model\Quote\Item\AbstractItem',
            [],
            '',
            false,
            false,
            true,
            ['getDiscountAmount']
        );
        $childTwoItemMock->expects($this->any())
            ->method('getDiscountAmount')
            ->will($this->returnValue($childTwoDiscountAmount));

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
