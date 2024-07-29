<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit;

use Magento\Framework\Api\SortOrder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Framework\Api\SortOrder
 */
class SortOrderTest extends TestCase
{
    /**
     * @var SortOrder
     */
    private $sortOrder;

    protected function setUp(): void
    {
        $this->sortOrder = new SortOrder();
    }

    public function testItReturnsNullIfNoOrderIsSet()
    {
        $this->assertNull($this->sortOrder->getDirection());
    }

    /**
     * @dataProvider sortOrderDirectionProvider
     */
    public function testItReturnsTheCorrectValuesIfSortOrderIsSet($sortOrder)
    {
        $this->sortOrder->setDirection($sortOrder);
        $this->assertSame($sortOrder, $this->sortOrder->getDirection());
    }

    /**
     * @return array
     */
    public static function sortOrderDirectionProvider()
    {
        return [[SortOrder::SORT_ASC], [SortOrder::SORT_DESC]];
    }

    /**
     * @param mixed $invalidDirection
     * @dataProvider invalidSortDirectionProvider
     */
    public function testItThrowsAnExceptionIfAnInvalidSortOrderIsSet($invalidDirection)
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->sortOrder->setDirection($invalidDirection);
    }

    /**
     * @return array
     */
    public static function invalidSortDirectionProvider()
    {
        return [
            [-1],
            [1],
            [0],
            [true],
            [false],
            [[]],
        ];
    }

    public function testTheSortDirectionCanBeSpecifiedCaseInsensitive()
    {
        $this->sortOrder->setDirection(strtolower(SortOrder::SORT_ASC));
        $this->assertSame(SortOrder::SORT_ASC, $this->sortOrder->getDirection());
        $this->sortOrder->setDirection(strtoupper(SortOrder::SORT_ASC));
        $this->assertSame(SortOrder::SORT_ASC, $this->sortOrder->getDirection());

        $this->sortOrder->setDirection(strtolower(SortOrder::SORT_DESC));
        $this->assertSame(SortOrder::SORT_DESC, $this->sortOrder->getDirection());
        $this->sortOrder->setDirection(strtoupper(SortOrder::SORT_DESC));
        $this->assertSame(SortOrder::SORT_DESC, $this->sortOrder->getDirection());
    }

    public function testItValidatesADirectionAssignedDuringInstantiation()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->sortOrder = new SortOrder([
            SortOrder::DIRECTION => 'not-asc-or-desc'
        ]);
    }

    public function testValidateField()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->sortOrder = new SortOrder([
            SortOrder::FIELD => 'invalid field (value);'
        ]);
    }
}
