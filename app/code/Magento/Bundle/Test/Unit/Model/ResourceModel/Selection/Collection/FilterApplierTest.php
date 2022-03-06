<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Selection\Collection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;
use Zend_Db_Select_Exception;

/**
 * Test selection collection filter applier
 */
class FilterApplierTest extends TestCase
{
    /**
     * @var FilterApplier
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new FilterApplier();
    }

    /**
     * @param $field
     * @param $value
     * @param $conditionType
     * @param $expectedCondition
     * @param $expectedValue
     * @dataProvider applyDataProvider
     * @throws Zend_Db_Select_Exception
     */
    public function testApply($field, $value, $conditionType, $expectedCondition, $expectedValue): void
    {
        $tableName = 'catalog_product_bundle_selection';
        $select = $this->createMock(Select::class);
        $collection = $this->createMock(Collection::class);
        $collection->method('getSelect')
            ->willReturn($select);
        $collection->method('getTable')
            ->willReturnArgument(0);
        $select->method('getPart')
            ->willReturnMap(
                [
                    [
                        'from',
                        [
                            'selection' => [
                                'tableName' => $tableName
                            ]
                        ]
                    ]
                ]
            );
        $select->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with($expectedCondition, $expectedValue);
        $this->model->apply($collection, $field, $value, $conditionType);
    }

    /**
     * @return array
     */
    public function applyDataProvider(): array
    {
        return [
            [
                'id',
                1,
                'eq',
                'selection.id = ?',
                1
            ],
            [
                'id',
                [1, 3],
                'in',
                'selection.id IN (?)',
                [1, 3]
            ]
        ];
    }
}
