<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptions
     */
    private $bundleOptions;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $baseCalculator;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItemMock;

    /**
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $amountFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->saleableItemMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));
        $this->selectionFactoryMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactory = $this->createMock(\Magento\Framework\Pricing\Amount\AmountFactory::class);
        $factoryCallback = $this->returnCallback(
            function ($fullAmount, $adjustments) {
                return $this->createAmountMock(['amount' => $fullAmount, 'adjustmentAmounts' => $adjustments]);
            }
        );
        $this->amountFactory->expects($this->any())->method('create')->will($factoryCallback);
        $this->baseCalculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);

        $taxData = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundleCalculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->setConstructorArgs(
                [$this->baseCalculator, $this->amountFactory, $this->selectionFactoryMock, $taxData, $priceCurrency]
            )
            ->setMethods(['getOptionsAmount'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptions = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleOptions::class,
            [
                'calculator' => $this->bundleCalculatorMock,
                'bundleSelectionFactory' => $this->selectionFactoryMock
            ]
        );
    }

    /**
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(string $selectionCollection)
    {
        $this->prepareOptionMocks($selectionCollection);
        $this->bundleOptions->getOptions($this->saleableItemMock);
        $this->assertSame($selectionCollection, $this->bundleOptions->getOptions($this->saleableItemMock));
        $this->assertSame($selectionCollection, $this->bundleOptions->getOptions($this->saleableItemMock));
    }

    /**
     * @param array $selectionCollection
     * @return void
     */
    private function prepareOptionMocks($selectionCollection)
    {
        $this->saleableItemMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $priceTypeMock = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('setStoreFilter')
            ->with($this->equalTo(1), $this->equalTo($this->saleableItemMock))
            ->will($this->returnSelf());

        $optionIds = ['41', '55'];
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getOptionsIds')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($optionIds));

        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getSelectionsCollection')
            ->with($this->equalTo($optionIds), $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($selectionCollection));

        $collection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $collection->expects($this->atLeastOnce())
            ->method('appendSelections')
            ->with($this->equalTo($selectionCollection), $this->equalTo(true), $this->equalTo(false))
            ->will($this->returnValue($selectionCollection));

        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getOptionsCollection')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($collection));

        $this->saleableItemMock->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->will($this->returnValue($priceTypeMock));
    }

    public function getOptionsDataProvider()
    {
        return [
            ['1', '2']
        ];
    }

    /**
     * @param float $selectionQty
     * @param float|bool $selectionAmount
     * @param bool $useRegularPrice
     * @dataProvider selectionAmountDataProvider
     */
    public function testGetOptionSelectionAmount($selectionQty, $selectionAmount, $useRegularPrice)
    {
        $selection = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getSelectionQty', '__wakeup']);
        $selection->expects($this->once())
            ->method('getSelectionQty')
            ->will($this->returnValue($selectionQty));
        $priceMock = $this->createMock(\Magento\Bundle\Pricing\Price\BundleSelectionPrice::class);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($selectionAmount));
        $this->selectionFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($this->saleableItemMock), $this->equalTo($selection), $this->equalTo($selectionQty))
            ->will($this->returnValue($priceMock));
        $this->assertSame(
            $selectionAmount,
            $this->bundleOptions->getOptionSelectionAmount($this->saleableItemMock, $selection, $useRegularPrice)
        );
    }

    /**
     * @return array
     */
    public function selectionAmountDataProvider(): array
    {
        return [
            [1., 50.5, false],
            [2.2, false, true]
        ];
    }

    /**
     * Create amount mock
     *
     * @param array $amountData
     * @return \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAmountMock($amountData)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\Amount\Base $amount */
        $amount = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);
        $amount->expects($this->any())->method('getAdjustmentAmounts')->will(
            $this->returnValue($amountData['adjustmentAmounts'] ?? [])
        );
        $amount->expects($this->any())->method('getValue')->will($this->returnValue($amountData['amount']));
        return $amount;
    }

    /**
     * Create option mock
     *
     * @param array $optionData
     * @return \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createOptionMock($optionData)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Option $option */
        $option = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['isMultiSelection', '__wakeup']);
        $option->expects($this->any())->method('isMultiSelection')
            ->will($this->returnValue($optionData['isMultiSelection']));
        $selections = [];
        foreach ($optionData['selections'] as $selectionData) {
            $selections[] = $this->createSelectionMock($selectionData);
        }
        foreach ($optionData['data'] as $key => $value) {
            $option->setData($key, $value);
        }
        $option->setData('selections', $selections);
        return $option;
    }

    /**
     * Create selection product mock
     *
     * @param array $selectionData
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSelectionMock($selectionData)
    {
        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['isSalable', 'getAmount', 'getQuantity', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        // All items are saleable
        $selection->expects($this->any())->method('isSalable')->will($this->returnValue(true));
        foreach ($selectionData['data'] as $key => $value) {
            $selection->setData($key, $value);
        }
        $amountMock = $this->createAmountMock($selectionData['amount']);
        $selection->expects($this->any())->method('getAmount')->will($this->returnValue($amountMock));
        $selection->expects($this->any())->method('getQuantity')->will($this->returnValue(1));

        $innerProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSelectionCanChangeQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->will($this->returnValue(true));
        $selection->expects($this->any())->method('getProduct')->will($this->returnValue($innerProduct));

        return $selection;
    }

    /**
     * @dataProvider getTestDataForCalculation
     */
    public function testCalculation(array $optionList, array $expected)
    {
        $storeId = 1;
        $this->saleableItemMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->selectionFactoryMock->expects($this->any())->method('create')->will($this->returnArgument(1));

        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->will($this->returnValue($this->createAmountMock(['amount' => 0.])));

        $options = [];
        foreach ($optionList as $optionData) {
            $options[] = $this->createOptionMock($optionData);
        }
        /** @var \PHPUnit_Framework_MockObject_MockObject $optionsCollection */
        $optionsCollection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $optionsCollection->expects($this->atLeastOnce())->method('appendSelections')->will($this->returnSelf());
        $optionsCollection->expects($this->atLeastOnce())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($options)));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Type\AbstractType $typeMock */
        $typeMock = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $typeMock->expects($this->any())->method('setStoreFilter')->with($storeId, $this->saleableItemMock);
        $typeMock->expects($this->any())->method('getOptionsCollection')->with($this->saleableItemMock)
            ->will($this->returnValue($optionsCollection));
        $this->saleableItemMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($typeMock));

        $this->assertEquals($expected['min'], $this->bundleOptions->calculateOptions($this->saleableItemMock));
        $this->assertEquals($expected['max'], $this->bundleOptions->calculateOptions($this->saleableItemMock, false));
    }

    /**
     * @return array
     */
    public function getTestDataForCalculation()
    {
        return [
            'first case' => [
                'optionList' => [
                    // first option with single choice of product
                    [
                        'isMultiSelection' => false,
                        'data' => [
                            'title' => 'test option 1',
                            'default_title' => 'test option 1',
                            'type' => 'select',
                            'option_id' => '1',
                            'position' => '0',
                            'required' => '1',
                        ],
                        'selections' => [
                            [
                                'data' => ['price' => 70.],
                                'amount' => ['amount' => 70],
                            ],
                            [
                                'data' => ['price' => 80.],
                                'amount' => ['amount' => 80]
                            ],
                            [
                                'data' => ['price' => 50.],
                                'amount' => ['amount' => 50]
                            ],
                        ]
                    ],
                    // second not required option
                    [
                        'isMultiSelection' => false,
                        'data' => [
                            'title' => 'test option 2',
                            'default_title' => 'test option 2',
                            'type' => 'select',
                            'option_id' => '2',
                            'position' => '1',
                            'required' => '0',
                        ],
                        'selections' => [
                            [
                                'data' => ['value' => 20.],
                                'amount' => ['amount' => 20],
                            ],
                        ]
                    ],
                    // third with multi-selection
                    [
                        'isMultiSelection' => true,
                        'data' => [
                            'title' => 'test option 3',
                            'default_title' => 'test option 3',
                            'type' => 'select',
                            'option_id' => '3',
                            'position' => '2',
                            'required' => '1',
                        ],
                        'selections' => [
                            [
                                'data' => ['price' => 40.],
                                'amount' => ['amount' => 40],
                            ],
                            [
                                'data' => ['price' => 20.],
                                'amount' => ['amount' => 20]
                            ],
                            [
                                'data' => ['price' => 60.],
                                'amount' => ['amount' => 60]
                            ],
                        ]
                    ],
                    // fourth without selections
                    [
                        'isMultiSelection' => true,
                        'data' => [
                            'title' => 'test option 3',
                            'default_title' => 'test option 3',
                            'type' => 'select',
                            'option_id' => '4',
                            'position' => '3',
                            'required' => '1',
                        ],
                        'selections' => []
                    ],
                ],
                'expected' => ['min' => 70, 'max' => 220],
            ]
        ];
    }
}
