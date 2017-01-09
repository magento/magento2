<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Converter;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ToDataModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleDataFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\ConditionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $conditionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleLabelFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesRule;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToDataModel
     */
    protected $model;

    protected function setUp()
    {
        $this->ruleFactory = $this->getMockBuilder(\Magento\SalesRule\Model\RuleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->ruleDataFactory = $this->getMockBuilder(\Magento\SalesRule\Api\Data\RuleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->conditionDataFactory = $this->getMockBuilder(
            \Magento\SalesRule\Api\Data\ConditionInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->dataObjectProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->ruleLabelFactory = $this->getMockBuilder(\Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->salesRule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['_construct', 'getData', 'getConditionsSerialized', 'getActionsSerialized'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\SalesRule\Model\Converter\ToDataModel::class,
            [
                'ruleFactory' =>  $this->ruleFactory,
                'ruleDataFactory' => $this->ruleDataFactory,
                'conditionDataFactory' => $this->conditionDataFactory,
                'ruleLabelFactory' => $this->ruleLabelFactory,
                'dataObjectProcessor' => $this->dataObjectProcessor,
            ]
        );
    }

    public function testToDataModel()
    {
        $array = [
            'rule_id' => '1',
            'name' => 'testrule',
            'is_active' => '1',
            'conditions_serialized' =>
                'a:7:{s:4:"type";s:46:"Magento\SalesRule\Model\Rule\Condition\Combine";s:9:"attribute";N;'
                . 's:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";'
                . 's:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:46:"Magento\SalesRule\Model\Rule\Condition\Address";'
                . 's:9:"attribute";s:13:"base_subtotal";s:8:"operator";s:2:">=";s:5:"value";s:3:"100";'
                . 's:18:"is_value_processed";b:0;}}}',
            'actions_serialized' =>
                'a:7:{s:4:"type";s:54:"Magento\SalesRule\Model\Rule\Condition\Product\Combine";s:9:"attribute";N;'
                . 's:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";'
                . 's:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:46:"Magento\SalesRule\Model\Rule\Condition\Product";'
                . 's:9:"attribute";s:16:"attribute_set_id";s:8:"operator";s:2:"==";s:5:"value";s:1:"4";'
                . 's:18:"is_value_processed";b:0;}}}',
            'coupon_type' => '1',
            'coupon_code' => '',
            'store_labels' => [
                0 => 'TestRule',
                1 => 'TestRuleForDefaultStore',
            ],
        ];

        $dataModel = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Rule::class)
        ->disableOriginalConstructor()
        ->setMethods(['create', 'getStoreLabels', 'setStoreLabels', 'getCouponType', 'setCouponType'])
        ->getMock();

        $dataLabel = $this->getMockBuilder(\Magento\SalesRule\Api\Data\RuleLabel::class)
            ->setMethods(['setStoreId', 'setStoreLabel', 'setStoreLabels'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataCondition = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Condition::class)
            ->setMethods(['setData'])
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
            ->willReturn(\Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setCouponType');

        $return = $this->model->toDataModel($this->salesRule);

        $this->assertSame($dataModel, $return);
    }

    public function testArrayToConditionDataModel()
    {

        $array=[
            'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => 1,
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                    'attribute' => 'base_subtotal',
                    'operator' => '>=',
                    'value' => 100,
                    'is_value_processed' => null,
                ],
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                    'attribute' => 'total_qty',
                    'operator' => '>',
                    'value' => 2,
                    'is_value_processed' => null
                ],
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => 1,
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' => [
                             [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                    'attribute' => 'category_ids',
                                    'operator' => '==',
                                    'value' => 3,
                                    'is_value_processed' => null
                             ]

                        ]

                ],
            ]

        ];

        $dataCondition = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Condition::class)
            ->setMethods(['setData'])
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
