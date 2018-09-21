<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;

/**
 * @covers \Magento\SalesRule\Model\Quote\ChildrenValidationLocator
 */
class ChildrenValidationLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @var QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var array
     */
    private $productTypeChildrenValidationMap = [
        'simple' => false,
        'bundle' => true,
    ];

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->itemMock = $this->createMock(QuoteItem::class);
        $this->productMock = $this->createMock(Product::class);
        $this->childrenValidationLocator = new ChildrenValidationLocator($this->productTypeChildrenValidationMap);
    }

    /**
     * Test isChildrenValidationRequired method
     *
     * @dataProvider childrenValidationDataProvider
     *
     * @param string $typeId
     * @param bool $isValidationRequired
     *
     * @return void
     */
    public function testIsChildrenValidationRequired($typeId, $isValidationRequired)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->itemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $actual = $this->childrenValidationLocator->isChildrenValidationRequired($this->itemMock);
        $expected = $isValidationRequired;
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function childrenValidationDataProvider()
    {
        return [
            ['simple', $this->productTypeChildrenValidationMap['simple']],
            ['bundle', $this->productTypeChildrenValidationMap['bundle']],
            ['configurable', true],
        ];
    }
}
