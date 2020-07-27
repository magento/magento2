<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Adjustment\Collection;
use Magento\Framework\Pricing\Adjustment\Pool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Pool
     */
    protected $adjustmentPool;

    /**
     * @var [][]
     */
    protected $adjustmentsData;

    protected function setUp(): void
    {
        $adj1 = $this->getMockForAbstractClass(AdjustmentInterface::class);
        $adj1->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(10);
        $adj2 = $this->getMockForAbstractClass(AdjustmentInterface::class);
        $adj2->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(20);
        $adj3 = $this->getMockForAbstractClass(AdjustmentInterface::class);
        $adj3->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(5);
        $adj4 = $this->getMockForAbstractClass(AdjustmentInterface::class);
        $adj4->expects($this->any())
            ->method('getSortOrder')
            ->willReturn(Pool::DEFAULT_SORT_ORDER);

        $adjustmentsData = [
            'adj1' => $adj1,
            'adj2' => $adj2,
            'adj3' => $adj3,
            'adj4' => $adj4,
        ];
        $this->adjustmentsData = $adjustmentsData;

        /** @var Pool|MockObject $adjustmentPool */
        $adjustmentPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdjustmentByCode'])
            ->getMock();
        $adjustmentPool->expects($this->any())->method('getAdjustmentByCode')->willReturnCallback(
            function ($code) use ($adjustmentsData) {
                if (!isset($adjustmentsData[$code])) {
                    $this->fail(sprintf('Adjustment "%s" not found', $code));
                }
                return $adjustmentsData[$code];
            }
        );
        $this->adjustmentPool = $adjustmentPool;
    }

    /**
     * @param string[] $adjustments
     * @param string[] $expectedResult
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems($adjustments, $expectedResult)
    {
        $collection = new Collection($this->adjustmentPool, $adjustments);

        $result = $collection->getItems();

        $this->assertEmpty(array_diff($expectedResult, array_keys($result)));
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return [
            [['adj1'], ['adj1']],
            [['adj4'], ['adj4']],
            [['adj1', 'adj4'], ['adj1', 'adj4']],
            [['adj1', 'adj2', 'adj3', 'adj4'], ['adj3', 'adj1', 'adj2', 'adj4']]
        ];
    }

    /**
     * @param string[] $adjustments
     * @param string $code
     * @param $expectedResult
     * @dataProvider getItemByCodeDataProvider
     */
    public function testGetItemByCode($adjustments, $code, $expectedResult)
    {
        $collection = new Collection($this->adjustmentPool, $adjustments);

        $item = $collection->getItemByCode($code);

        $this->assertEquals($expectedResult, $item->getSortOrder());
    }

    /**
     * @return array
     */
    public function getItemByCodeDataProvider()
    {
        return [
            [['adj1'], 'adj1', 10],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj1', 10],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj2', 20],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj3', 5],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj4', Pool::DEFAULT_SORT_ORDER],
        ];
    }

    public function testGetItemByNotExistingCode()
    {
        $this->expectException('InvalidArgumentException');
        $adjustments = ['adj1'];
        $collection = new Collection($this->adjustmentPool, $adjustments);
        $collection->getItemByCode('not_existing_code');
    }
}
