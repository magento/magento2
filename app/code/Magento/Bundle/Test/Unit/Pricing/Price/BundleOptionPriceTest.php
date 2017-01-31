<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BundleOptionPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptionPrice
     */
    protected $bundleOptionPrice;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseCalculator;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleCalculatorMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    protected function setUp()
    {
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->saleableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));

        $this->saleableItemMock->expects($this->once())
            ->method('setQty')
            ->will($this->returnSelf());

        $this->saleableItemMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->selectionFactoryMock = $this->getMockBuilder('Magento\Bundle\Pricing\Price\BundleSelectionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactory = $this->getMock('Magento\Framework\Pricing\Amount\AmountFactory', [], [], '', false);
        $factoryCallback = $this->returnCallback(
            function ($fullAmount, $adjustments) {
                return $this->createAmountMock(['amount' => $fullAmount, 'adjustmentAmounts' => $adjustments]);
            }
        );
        $this->amountFactory->expects($this->any())->method('create')->will($factoryCallback);
        $this->baseCalculator = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);

        $taxData = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundleCalculatorMock = $this->getMockBuilder('Magento\Bundle\Pricing\Adjustment\Calculator')
            ->setConstructorArgs(
                [$this->baseCalculator, $this->amountFactory, $this->selectionFactoryMock, $taxData, $priceCurrency]
            )
            ->setMethods(['getOptionsAmount'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionPrice = $this->objectManagerHelper->getObject(
            'Magento\Bundle\Pricing\Price\BundleOptionPrice',
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
                'bundleSelectionFactory' => $this->selectionFactoryMock
            ]
        );
    }

    /**
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions($selectionCollection)
    {
        $this->prepareOptionMocks($selectionCollection);
        $this->assertSame($selectionCollection, $this->bundleOptionPrice->getOptions());
        $this->assertSame($selectionCollection, $this->bundleOptionPrice->getOptions());
    }

    /**
     * @param array $selectionCollection
     * @return void
     */
    protected function prepareOptionMocks($selectionCollection)
    {
        $this->saleableItemMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $priceTypeMock = $this->getMock('Magento\Bundle\Model\Product\Type', [], [], '', false);
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

        $collection = $this->getMock('Magento\Bundle\Model\ResourceModel\Option\Collection', [], [], '', false);
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
     * @dataProvider selectionAmountDataProvider
     */
    public function testGetOptionSelectionAmount($selectionQty, $selectionAmount)
    {
        $selection = $this->getMock('Magento\Catalog\Model\Product', ['getSelectionQty', '__wakeup'], [], '', false);
        $selection->expects($this->once())
            ->method('getSelectionQty')
            ->will($this->returnValue($selectionQty));
        $priceMock = $this->getMock('Magento\Bundle\Pricing\Price\BundleSelectionPrice', [], [], '', false);
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($selectionAmount));
        $this->selectionFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($this->saleableItemMock), $this->equalTo($selection), $this->equalTo($selectionQty))
            ->will($this->returnValue($priceMock));
        $this->assertSame($selectionAmount, $this->bundleOptionPrice->getOptionSelectionAmount($selection));
    }

    /**
     * @return array
     */
    public function selectionAmountDataProvider()
    {
        return [
            [1., 50.5],
            [2.2, false]
        ];
    }

    public function testGetAmount()
    {
        $amountMock = $this->getMock('Magento\Framework\Pricing\Amount\AmountInterface');
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($amountMock));
        $this->assertSame($amountMock, $this->bundleOptionPrice->getAmount());
    }

    /**
     * Create amount mock
     *
     * @param array $amountData
     * @return \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAmountMock($amountData)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\Amount\Base $amount */
        $amount = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $amount->expects($this->any())->method('getAdjustmentAmounts')->will(
            $this->returnValue(isset($amountData['adjustmentAmounts']) ? $amountData['adjustmentAmounts'] : [])
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
    protected function createOptionMock($optionData)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Option $option */
        $option = $this->getMock('Magento\Bundle\Model\Option', ['isMultiSelection', '__wakeup'], [], '', false);
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
    protected function createSelectionMock($selectionData)
    {
        $selection = $this->getMockBuilder('Magento\Catalog\Model\Product')
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

        $innerProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
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
    public function testCalculation($optionList, $expected)
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
        $optionsCollection = $this->getMock('Magento\Bundle\Model\ResourceModel\Option\Collection', [], [], '', false);
        $optionsCollection->expects($this->atLeastOnce())->method('appendSelections')->will($this->returnSelf());
        $optionsCollection->expects($this->atLeastOnce())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($options)));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Type\AbstractType $typeMock */
        $typeMock = $this->getMock('Magento\Bundle\Model\Product\Type', [], [], '', false);
        $typeMock->expects($this->any())->method('setStoreFilter')->with($storeId, $this->saleableItemMock);
        $typeMock->expects($this->any())->method('getOptionsCollection')->with($this->saleableItemMock)
            ->will($this->returnValue($optionsCollection));
        $this->saleableItemMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($typeMock));

        $this->assertEquals($expected['min'], $this->bundleOptionPrice->getValue());
        $this->assertEquals($expected['max'], $this->bundleOptionPrice->getMaxValue());
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
