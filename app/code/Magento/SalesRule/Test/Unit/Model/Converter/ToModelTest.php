<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Converter;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ToModelTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactory;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var ToModel
     */
    protected $model;

    protected function setUp(): void
    {
        $this->ruleFactory = $this->getMockBuilder(RuleFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->dataObjectProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildOutputDataArray'])
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            ToModel::class,
            [
                'ruleFactory' =>  $this->ruleFactory,
                'dataObjectProcessor' => $this->dataObjectProcessor,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDataModelToArray()
    {
        $array = [
            'type' => 'conditionType',
            'value' => 'value',
            'attribute' => 'getAttributeName',
            'operator' => 'getOperator',
            'aggregator' => 'getAggregatorType',
            'conditions' => [
                [
                    'type' => null,
                    'value' => null,
                    'attribute' => null,
                    'operator' => null,
                ],
                [
                    'type' => null,
                    'value' => null,
                    'attribute' => null,
                    'operator' => null,
                ],
            ],
        ];

        /**
         * @var Condition $dataCondition
         */
        $dataCondition = $this->createPartialMockWithReflection(
            Condition::class,
            [
                'create',
                'load',
                'getConditionType',
                'getValue',
                'getAttributeName',
                'getOperator',
                'getAggregatorType',
                'getConditions'
            ]
        );

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getConditionType')
            ->willReturn('conditionType');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('value');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getAttributeName')
            ->willReturn('getAttributeName');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getOperator')
            ->willReturn('getOperator');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getAggregatorType')
            ->willReturn('getAggregatorType');

        $dataCondition1 = $this->createPartialMockWithReflection(
            Condition::class,
            [
                'create',
                'load',
                'getConditionType',
                'getValue',
                'getAttributeName',
                'getOperator',
                'getAggregatorType',
                'getConditions'
            ]
        );

        $dataCondition2 = $this->createPartialMockWithReflection(
            Condition::class,
            [
                'create',
                'load',
                'getConditionType',
                'getValue',
                'getAttributeName',
                'getOperator',
                'getAggregatorType',
                'getConditions'
            ]
        );

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getConditions')
            ->willReturn([$dataCondition1, $dataCondition2]);

        $result = $this->model->dataModelToArray($dataCondition);

        $this->assertEquals($array, $result);
    }

    public function testToModel()
    {
        /**
         * @var Rule $dataModel
         */
        $dataModel = $this->createPartialMockWithReflection(
            Rule::class,
            [
                'create',
                'load',
                'getData',
                'getRuleId',
                'getCondition',
                'getActionCondition',
                'getStoreLabels'
            ]
        );
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getRuleId')
            ->willReturn(1);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getActionCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([]);

        $ruleModel = $this->createPartialMockWithReflection(
            SalesRule::class,
            ['create', 'load', 'getId', 'getData']
        );

        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($ruleModel);
        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(['data_1'=>1]);

        $this->dataObjectProcessor
            ->expects($this->any())
            ->method('buildOutputDataArray')
            ->willReturn(['data_2'=>2]);

        $this->ruleFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($ruleModel);

        $result = $this->model->toModel($dataModel);
        $this->assertEquals($ruleModel, $result);
    }

    #[DataProvider('expectedDatesProvider')]
    public function testFormattingDate($data)
    {
        /**
         * @var Rule|MockObject $dataModel
         */
        $dataModel = $this->createPartialMockWithReflection(
            Rule::class,
            [
                'create',
                'load',
                'getData',
                'getRuleId',
                'getCondition',
                'getActionCondition',
                'getStoreLabels',
                'getFromDate',
                'setFromDate',
                'getToDate',
                'setToDate',
            ]
        );
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getRuleId')
            ->willReturn(null);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getActionCondition')
            ->willReturn(false);
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([]);
        $ruleModel = $this->createPartialMockWithReflection(
            SalesRule::class,
            ['create', 'load', 'getId', 'getData']
        );
        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(['data_1'=>1]);

        $this->dataObjectProcessor
            ->expects($this->any())
            ->method('buildOutputDataArray')
            ->willReturn(['data_2'=>2]);

        $this->ruleFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($ruleModel);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getFromDate')
            ->willReturn($data['from_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getToDate')
            ->willReturn($data['to_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setFromDate')
            ->with($data['expected_from_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setToDate')
            ->with($data['expected_to_date']);

        $this->model->toModel($dataModel);
    }

    /**
     * @return array
     */
    public static function expectedDatesProvider()
    {
        return [
            'mm/dd/yyyy to yyyy-mm-dd' => [
                [
                    'from_date' => '03/24/2016',
                    'to_date' => '03/25/2016',
                    'expected_from_date' => '2016-03-24T00:00:00',
                    'expected_to_date' => '2016-03-25T00:00:00',
                ]
            ],
            'yyyy-mm-dd to yyyy-mm-dd' => [
                [
                    'from_date' => '2016-03-24',
                    'to_date' => '2016-03-25',
                    'expected_from_date' => '2016-03-24T00:00:00',
                    'expected_to_date' => '2016-03-25T00:00:00',
                ]
            ],
            'yymmdd to yyyy-mm-dd' => [
                [
                    'from_date' => '20160324',
                    'to_date' => '20160325',
                    'expected_from_date' => '2016-03-24T00:00:00',
                    'expected_to_date' => '2016-03-25T00:00:00',
                ]
            ],
        ];
    }
}
