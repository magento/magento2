<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

use Magento\Framework\Pricing\Adjustment\Factory;
use Magento\Framework\Pricing\Adjustment\Pool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    public $model;

    protected function setUp(): void
    {
        $adjustmentsData = [
            'adj1' => ['className' => 'adj1_class', 'sortOrder' => 10],
            'adj2' => ['className' => 'adj2_class', 'sortOrder' => 20],
            'adj3' => ['className' => 'adj3_class', 'sortOrder' => 5],
            'adj4' => ['className' => 'adj4_class', 'sortOrder' => null],
            'adj5' => ['className' => 'adj5_class'],
        ];

        /** @var Factory|MockObject $adjustmentFactory */
        $adjustmentFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $adjustmentFactory->expects($this->any())->method('create')->willReturnCallback(
            function ($className, $data) {
                return $className . '|' . $data['sortOrder'];
            }
        );

        $this->model = new Pool($adjustmentFactory, $adjustmentsData);
    }

    public function testGetAdjustments()
    {
        $expectedResult = [
            'adj1' => 'adj1_class|10',
            'adj2' => 'adj2_class|20',
            'adj3' => 'adj3_class|5',
            'adj4' => 'adj4_class|' . Pool::DEFAULT_SORT_ORDER,
            'adj5' => 'adj5_class|' . Pool::DEFAULT_SORT_ORDER,
        ];

        $result = $this->model->getAdjustments();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getAdjustmentByCodeDataProvider
     */
    public function testGetAdjustmentByCode($code, $expectedResult)
    {
        $result = $this->model->getAdjustmentByCode($code);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function getAdjustmentByCodeDataProvider()
    {
        return [
            ['adj1', 'adj1_class|10'],
            ['adj2', 'adj2_class|20'],
            ['adj3', 'adj3_class|5'],
            ['adj4', 'adj4_class|' . Pool::DEFAULT_SORT_ORDER],
            ['adj5', 'adj5_class|' . Pool::DEFAULT_SORT_ORDER],
        ];
    }

    public function testGetAdjustmentByNotExistingCode()
    {
        $this->expectException('InvalidArgumentException');
        $this->model->getAdjustmentByCode('not_existing_code');
    }
}
