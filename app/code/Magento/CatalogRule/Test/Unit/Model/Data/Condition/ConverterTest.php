<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Data\Condition;

use Magento\CatalogRule\Api\Data\ConditionInterface;
use Magento\CatalogRule\Api\Data\ConditionInterfaceFactory;
use Magento\CatalogRule\Model\Data\Condition\Converter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var MockObject|ConditionInterfaceFactory
     */
    protected $conditionFactoryMock;

    /**
     * @var Converter
     */
    protected $model;

    protected function setUp(): void
    {
        $this->conditionFactoryMock = $this->createPartialMock(
            ConditionInterfaceFactory::class,
            ['create']
        );
        $this->model = new Converter($this->conditionFactoryMock);
    }

    public function testDataModelToArray()
    {
        $childConditionMock = $this->getMockForAbstractClass(ConditionInterface::class);
        $childConditionMock->expects($this->once())->method('getType')->willReturn('child-type');
        $childConditionMock->expects($this->once())->method('getAttribute')->willReturn('child-attr');
        $childConditionMock->expects($this->once())->method('getOperator')->willReturn('child-operator');
        $childConditionMock->expects($this->once())->method('getValue')->willReturn('child-value');
        $childConditionMock->expects($this->once())->method('getIsValueParsed')->willReturn(true);
        $childConditionMock->expects($this->once())->method('getAggregator')->willReturn('all');
        $childConditionMock->expects($this->once())->method('getConditions')->willReturn([]);

        $dataModelMock = $this->getMockForAbstractClass(ConditionInterface::class);
        $dataModelMock->expects($this->once())->method('getType')->willReturn('type');
        $dataModelMock->expects($this->once())->method('getAttribute')->willReturn('attr');
        $dataModelMock->expects($this->once())->method('getOperator')->willReturn('operator');
        $dataModelMock->expects($this->once())->method('getValue')->willReturn('value');
        $dataModelMock->expects($this->once())->method('getIsValueParsed')->willReturn(true);
        $dataModelMock->expects($this->once())->method('getAggregator')->willReturn('all');
        $dataModelMock->expects($this->once())->method('getConditions')->willReturn([$childConditionMock]);

        $expectedResult = [
            'type' => 'type',
            'attribute' => 'attr',
            'operator' => 'operator',
            'value' => 'value',
            'is_value_processed' => true,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => 'child-type',
                    'attribute' => 'child-attr',
                    'operator' => 'child-operator',
                    'value' => 'child-value',
                    'is_value_processed' => 1,
                    'aggregator' => 'all',
                ]
            ]
        ];
        $this->assertEquals($expectedResult, $this->model->dataModelToArray($dataModelMock));
    }

    public function testArrayToDataModel()
    {
        $array = [
            'type' => 'type',
            'attribute' => 'attr',
            'operator' => 'operator',
            'value' => 'value',
            'is_value_parsed' => true,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => 'child-type',
                    'attribute' => 'child-attr',
                    'operator' => 'child-operator',
                    'value' => 'child-value',
                    'is_value_parsed' => false,
                    'aggregator' => 'any',
                ]
            ]
        ];

        $conditionMock = $this->getMockForAbstractClass(ConditionInterface::class);
        $conditionChildMock = $this->getMockForAbstractClass(ConditionInterface::class);

        $this->conditionFactoryMock->expects($this->at(0))->method('create')->willReturn($conditionMock);
        $this->conditionFactoryMock->expects($this->at(1))->method('create')->willReturn($conditionChildMock);

        $conditionMock->expects($this->once())->method('setType')->with('type')->willReturnSelf();
        $conditionMock->expects($this->once())->method('setAggregator')->with('all')->willReturnSelf();
        $conditionMock->expects($this->once())->method('setAttribute')->with('attr')->willReturnSelf();
        $conditionMock->expects($this->once())->method('setOperator')->with('operator')->willReturnSelf();
        $conditionMock->expects($this->once())->method('setIsValueParsed')->with(true)->willReturnSelf();
        $conditionMock->expects($this->once())->method('setConditions')->with([$conditionChildMock])->willReturnSelf();

        $conditionChildMock->expects($this->once())->method('setType')->with('child-type')->willReturnSelf();
        $conditionChildMock->expects($this->once())->method('setAggregator')->with('any')->willReturnSelf();
        $conditionChildMock->expects($this->once())->method('setAttribute')->with('child-attr')->willReturnSelf();
        $conditionChildMock->expects($this->once())->method('setOperator')->with('child-operator')->willReturnSelf();
        $conditionChildMock->expects($this->once())->method('setIsValueParsed')->with(false)->willReturnSelf();

        $this->assertEquals($conditionMock, $this->model->arrayToDataModel($array));
    }
}
