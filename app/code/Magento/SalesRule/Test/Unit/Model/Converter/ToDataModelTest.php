<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Converter;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Magento\SalesRule\Api\Data\RuleExtensionInterface;

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

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var RuleExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactoryMock;

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

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->setMethods(null)
            ->getMock();

        $this->extensionFactoryMock = $this->getMockBuilder(RuleExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
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
                'serializer' => $this->serializer,
                'extensionFactory' => $this->extensionFactoryMock,
            ]
        );
    }

    private function getArrayData()
    {
        return [
            'rule_id' => '1',
            'name' => 'testrule',
            'is_active' => '1',
            'conditions_serialized' => json_encode([
                'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
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
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
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

        /** @var RuleExtensionInterface|\PHPUnit_Framework_MockObject_MockObject $attributesMock */
        $attributesMock = $this->getMockBuilder(RuleExtensionInterface::class)
            ->getMock();
        $arrayAttributes['extension_attributes'] = $attributesMock;


        $this->extensionFactoryMock->expects($this->any())
            ->method('create')
            ->with(['data' => $array['extension_attributes']])
            ->willReturn($attributesMock);

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
