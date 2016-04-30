<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CartPriceRulesFixture;

class CartPriceRulesFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\CartPriceRulesFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new CartPriceRulesFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->getMock('\Magento\Store\Model\Website', [], [], '', false);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $abstractDbMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\ResourceModel\Db\AbstractDb',
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

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $modelMock = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $modelFactoryMock = $this->getMock('\Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);
        $modelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($modelMock);

        $objectValueMap = [
            ['Magento\SalesRule\Model\RuleFactory', $modelFactoryMock],
            ['Magento\Catalog\Model\Category', $categoryMock]
        ];

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap($objectValueMap));

        $valueMap = [
            ['cart_price_rules', 0, 1],
            ['cart_price_rules_floor', 3, 3],
            ['cart_price_rules_advanced_type', false, false]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo('Magento\SalesRule\Model\Rule'))
            ->willReturn($ruleMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
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
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'category_ids',
                'operator'  => '==',
                'value'     => null,
            ];

            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1'=> [
                        'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
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
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'region',
                'operator'  => '==',
                'value'     => $regions[($ruleId / 4) % 50],
            ];

            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
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
