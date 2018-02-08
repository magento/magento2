<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartPriceRulesFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Setup\Fixtures\CartPriceRulesFixture
     */
    private $model;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->fixtureModel = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleFactory = $this->getMockBuilder(\Magento\SalesRule\Model\RuleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            \Magento\Setup\Fixtures\CartPriceRulesFixture::class,
            ['fixtureModel' => $this->fixtureModel, 'ruleFactory' => $this->ruleFactory]
        );
    }

    public function testExecute()
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->atLeastOnce())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->atLeastOnce())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $abstractDbMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->atLeastOnce())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->atLeastOnce())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $objectValueMap = [
            [\Magento\Catalog\Model\Category::class, $categoryMock]
        ];

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->will($this->returnValueMap($objectValueMap));

        $valueMap = [
            ['cart_price_rules', 0, 1],
            ['cart_price_rules_floor', 3, 3],
            ['cart_price_rules_advanced_type', false, false]
        ];

        $this->fixtureModel
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->fixtureModel
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($rule);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo(\Magento\SalesRule\Model\Rule::class))
            ->willReturn($ruleMock);

        $this->fixtureModel
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModel
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    /**
     * @param int $ruleId
     * @param array $categoriesArray
     * @param int $ruleCount
     * @dataProvider dataProviderGenerateAdvancedCondition
     */
    public function testGenerateAdvancedCondition($ruleId, $categoriesArray, $ruleCount)
    {
        $reflection = new \ReflectionClass($this->model);
        $reflectionProperty = $reflection->getProperty('cartPriceRulesCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $ruleCount);

        $result = $this->model->generateAdvancedCondition($ruleId, $categoriesArray);
        if ($ruleId < ($ruleCount - 200)) {
            $firstCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                'attribute' => 'category_ids',
                'operator'  => '==',
                'value'     => null,
            ];

            $secondCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1'=> [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                ]
            ];
        } else {
            // Shipping Region
            $regions = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
                        'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois',
                        'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts',
                        'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
                        'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota',
                        'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
                        'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
                        'Wisconsin', 'Wyoming'];
            $firstCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'region',
                'operator'  => '==',
                'value'     => $regions[($ruleId / 4) % 50],
            ];

            $secondCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                ]
            ];
        }
        $this->assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function dataProviderGenerateAdvancedCondition()
    {
        return [
            [1, [0], 1],
            [1, [0], 300]
        ];
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating cart price rules', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'cart_price_rules' => 'Cart Price Rules'
        ], $this->model->introduceParamLabels());
    }
}
