<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Quote\Item;

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
            '\Magento\Sales\Model\Quote\Item\AbstractItem',
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
            '\Magento\Sales\Model\Quote\Item\AbstractItem',
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
            '\Magento\Sales\Model\Quote\Item\AbstractItem',
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
                10
            ],
            'kids_but_not_calculated' => [
                10,
                [$childOneItemMock],
                false,
                10
            ],
            'one_kid' => [
                $childOneDiscountAmount,
                [$childOneItemMock],
                true,
                $valueHasNoEffect
            ],
            'two_kids' => [
                $childOneDiscountAmount + $childTwoDiscountAmount,
                [$childOneItemMock, $childTwoItemMock],
                true,
                $valueHasNoEffect
            ]
        ];
        return $data;
    }
}
