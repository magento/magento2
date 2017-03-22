<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

use \Magento\Framework\Pricing\Adjustment\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\Pool
     */
    protected $adjustmentPool;

    /**
     * @var [][]
     */
    protected $adjustmentsData;

    protected function setUp()
    {
        $adj1 = $this->getMock(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class);
        $adj1->expects($this->any())
            ->method('getSortOrder')
            ->will($this->returnValue(10));
        $adj2 = $this->getMock(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class);
        $adj2->expects($this->any())
            ->method('getSortOrder')
            ->will($this->returnValue(20));
        $adj3 = $this->getMock(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class);
        $adj3->expects($this->any())
            ->method('getSortOrder')
            ->will($this->returnValue(5));
        $adj4 = $this->getMock(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class);
        $adj4->expects($this->any())
            ->method('getSortOrder')
            ->will($this->returnValue(\Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER));

        $adjustmentsData = [
            'adj1' => $adj1,
            'adj2' => $adj2,
            'adj3' => $adj3,
            'adj4' => $adj4,
        ];

        /** @var \Magento\Framework\Pricing\Adjustment\Pool|\PHPUnit_Framework_MockObject_MockObject $adjustmentPool */
        $adjustmentPool = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\Pool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdjustmentByCode'])
            ->getMock();
        $adjustmentPool->expects($this->any())->method('getAdjustmentByCode')->will(
            $this->returnCallback(
                function ($code) use ($adjustmentsData) {
                    if (!isset($adjustmentsData[$code])) {
                        $this->fail(sprintf('Adjustment "%s" not found', $code));
                    }
                    return $adjustmentsData[$code];
                }
            )
        );

        $this->adjustmentPool = $adjustmentPool;
        $this->adjustmentsData = $adjustmentsData;
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

        $this->assertEquals($expectedResult, $item->getAdjustmentCode());
    }

    public function getItemByCodeDataProvider()
    {
        return [
            [['adj1'], 'adj1', $this->adjustmentsData['adj1']],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj1', $this->adjustmentsData['adj1']],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj2', $this->adjustmentsData['adj2']],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj3', $this->adjustmentsData['adj3']],
            [['adj1', 'adj2', 'adj3', 'adj4'], 'adj4', $this->adjustmentsData['adj4']],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetItemByNotExistingCode()
    {
        $adjustments = ['adj1'];
        $collection = new Collection($this->adjustmentPool, $adjustments);
        $collection->getItemByCode('not_existing_code');
    }
}
