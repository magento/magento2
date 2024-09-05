<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Converter;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Magento\SalesRule\Api\Data\RuleExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleLabelInterface;
use Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory;
use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ToDataModelTest extends TestCase
{
    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactory;

    /**
     * @var RuleInterfaceFactory|MockObject
     */
    protected $ruleDataFactory;

    /**
     * @var ConditionInterfaceFactory|MockObject
     */
    protected $conditionDataFactory;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var RuleLabelInterfaceFactory|MockObject
     */
    protected $ruleLabelFactory;

    /**
     * @var Rule|MockObject
     */
    protected $salesRule;

    /**
     * @var ToDataModel
     */
    protected $model;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var RuleExtensionFactory|MockObject
     */
    private $extensionFactoryMock;

    protected function setUp(): void
    {
        $this->ruleFactory = $this->getMockBuilder(RuleFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleDataFactory = $this->getMockBuilder(RuleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->conditionDataFactory = $this->getMockBuilder(
            ConditionInterfaceFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->dataObjectProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleLabelFactory = $this->getMockBuilder(RuleLabelInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->salesRule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->addMethods(['getConditionsSerialized', 'getActionsSerialized'])
            ->onlyMethods(['_construct', 'getData'])
            ->getMock();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->addMethods([])
            ->getMock();

        $this->extensionFactoryMock = $this->getMockBuilder(RuleExtensionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            ToDataModel::class,
            [
                'ruleFactory' =>  $this->ruleFactory,
                'ruleDataFactory' => $this->ruleDataFactory,
                'conditionDataFactory' => $this->conditionDataFactory,
                'ruleLabelFactory' => $this->ruleLabelFactory,
                'dataObjectProcessor' => $this->dataObjectProcessor,
                'serializer' => $this->serializer,
                'extensionFactory' => $this->extensionFactoryMock,
            ]
        );
    }

    /**
     * @return array
     */
    private function getArrayData()
    {
        return [
            'rule_id' => '1',
            'name' => 'testrule',
            'is_active' => '1',
            'conditions_serialized' => json_encode([
                'type' => Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => Address::class,
                        'attribute' => 'base_subtotal',
                        'operator' => '>=',
                        'value' => '100',
                        'is_value_processed' => false,
                    ],
                ],
            ]),
            'actions_serialized' => json_encode([
                'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => Product::class,
                        'attribute' => 'attribute_set_id',
                        'operator' => '==',
                        'value' => '4',
                        'is_value_processed' => false,
                    ],
                ],
            ]),
            'coupon_type' => '1',
            'coupon_code' => '',
            'store_labels' => [
                0 => 'TestRule',
                1 => 'TestRuleForDefaultStore',
            ],
            'extension_attributes' => [
                'some_extension_attributes' => 123,
            ],
        ];
    }

    public function testToDataModel()
    {
        $array = $this->getArrayData();
        $arrayAttributes = $array;

        /** @var RuleExtensionInterface|MockObject $attributesMock */
        $attributesMock = $this->getMockBuilder(RuleExtensionInterface::class)
            ->getMock();
        $arrayAttributes['extension_attributes'] = $attributesMock;

        $this->extensionFactoryMock->expects($this->any())
            ->method('create')
            ->with(['data' => $array['extension_attributes']])
            ->willReturn($attributesMock);

        $dataModel = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Rule::class)
            ->disableOriginalConstructor()
            ->addMethods(['create'])
            ->onlyMethods(['getStoreLabels', 'setStoreLabels', 'getCouponType', 'setCouponType'])
            ->getMock();

        $dataLabel = $this->getMockBuilder(RuleLabelInterface::class)
            ->addMethods(['setStoreLabels'])
            ->onlyMethods(['setStoreId', 'setStoreLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $dataCondition = $this->getMockBuilder(Condition::class)
            ->onlyMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleLabelFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dataLabel);

        $this->conditionDataFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dataCondition);

        $this->ruleDataFactory
            ->expects($this->any())
            ->method('create')
            ->with(['data' => $arrayAttributes])
            ->willReturn($dataModel);

        $this->salesRule
            ->expects($this->any())
            ->method('getData')
            ->willReturn($array);

        $this->salesRule
            ->expects($this->once())
            ->method('getConditionsSerialized')
            ->willReturn($array['conditions_serialized']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn($array['store_labels']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setStoreLabels');

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getCouponType')
            ->willReturn(Rule::COUPON_TYPE_NO_COUPON);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setCouponType');

        $return = $this->model->toDataModel($this->salesRule);

        $this->assertSame($dataModel, $return);
    }

    public function testArrayToConditionDataModel()
    {
        $array=[
            'type' => Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => 1,
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => Address::class,
                    'attribute' => 'base_subtotal',
                    'operator' => '>=',
                    'value' => 100,
                    'is_value_processed' => null,
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'total_qty',
                    'operator' => '>',
                    'value' => 2,
                    'is_value_processed' => null
                ],
                [
                    'type' => Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => 1,
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' => [
                        [
                            'type' => Product::class,
                            'attribute' => 'category_ids',
                            'operator' => '==',
                            'value' => 3,
                            'is_value_processed' => null
                        ]

                    ]

                ],
            ]

        ];

        $dataCondition = $this->getMockBuilder(Condition::class)
            ->onlyMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionDataFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($dataCondition);

        $return = $this->model->arrayToConditionDataModel($array);

        $this->assertEquals($dataCondition, $return);
    }
}
