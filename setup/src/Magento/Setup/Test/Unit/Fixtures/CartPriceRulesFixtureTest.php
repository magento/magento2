<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Category;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\Setup\Fixtures\CartPriceRulesFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartPriceRulesFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var CartPriceRulesFixture
     */
    private $model;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|MockObject
     */
    private $ruleFactoryMock;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->createMock(FixtureModel::class);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->model = new CartPriceRulesFixture($this->fixtureModelMock, $this->ruleFactoryMock);
    }

    public function testExecute()
    {
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(2);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeMock]);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn('website_id');

        $contextMock = $this->createMock(Context::class);
        $abstractDbMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->willReturn([1]);

        $storeManagerMock = $this->createMock(StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/file');
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn('category_id');

        $objectValueMap = [
            [Category::class, $categoryMock]
        ];

        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($storeManagerMock);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnMap($objectValueMap);

        $valueMap = [
            ['cart_price_rules', 0, 1],
            ['cart_price_rules_floor', 3, 3],
            ['cart_price_rules_advanced_type', false, false]
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getValue')
            ->willReturnMap($valueMap);
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with(Rule::class)
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
                'type' => Product::class,
                'attribute' => 'category_ids',
                'operator' => '==',
                'value' => 0,
            ];

            $secondCondition = [
                'type' => Address::class,
                'attribute' => 'base_subtotal',
                'operator' => '>=',
                'value' => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => Found::class,
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
                'type' => Address::class,
                'attribute' => 'region',
                'operator' => '==',
                'value' => $regions[intdiv($ruleId, 4) % 50],
            ];

            $secondCondition = [
                'type' => Address::class,
                'attribute' => 'base_subtotal',
                'operator' => '>=',
                'value' => 5,
            ];
            $expected = [
                'conditions' => [
                    1 => [
                        'type' => Combine::class,
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
            [1, [[0]], 1],
            [1, [[0]], 300]
        ];
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating cart price rules', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame(
            [
                'cart_price_rules' => 'Cart Price Rules'
            ],
            $this->model->introduceParamLabels()
        );
    }
}
