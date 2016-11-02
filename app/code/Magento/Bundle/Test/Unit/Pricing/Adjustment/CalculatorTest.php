<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Pricing\Adjustment;

use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use \Magento\Bundle\Pricing\Adjustment\Calculator;

use Magento\Bundle\Model\Product\Price as ProductPrice;
use Magento\Bundle\Pricing\Price;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Bundle\Pricing\Adjustment\Calculator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $priceMocks = [];

    /**
     * @var float
     */
    protected $baseAmount = 50.;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseCalculator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxData;

    /**
     * @var Calculator
     */
    protected $model;

    protected function setUp()
    {
        $this->saleableItem = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', 'getPriceType', '__wakeup', 'getStore', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();
        $priceInfo = $this->getMock(\Magento\Framework\Pricing\PriceInfo\Base::class, [], [], '', false);
        $priceInfo->expects($this->any())->method('getPrice')->will(
            $this->returnCallback(
                function ($type) {
                    if (!isset($this->priceMocks[$type])) {
                        throw new \PHPUnit_Framework_ExpectationFailedException('Unexpected type of price model');
                    }
                    return $this->priceMocks[$type];
                }
            )
        );
        $this->saleableItem->expects($this->any())->method('getPriceInfo')->will($this->returnValue($priceInfo));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));

        $this->saleableItem->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->baseCalculator = $this->getMock(
            \Magento\Framework\Pricing\Adjustment\Calculator::class,
            [],
            [],
            '',
            false
        );
        $this->amountFactory = $this->getMock(
            \Magento\Framework\Pricing\Amount\AmountFactory::class,
            [],
            [],
            '',
            false
        );

        $this->selectionFactory = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionFactory->expects($this->any())->method('create')->will($this->returnArgument(1));

        $this->taxData = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(\Magento\Bundle\Pricing\Adjustment\Calculator::class,
            [
                'calculator' => $this->baseCalculator,
                'amountFactory' => $this->amountFactory,
                'bundleSelectionFactory' => $this->selectionFactory,
                'taxHelper' => $this->taxData,
                'priceCurrency' => $priceCurrency,
            ]
        );
    }

    public function testEmptySelectionPriceList()
    {
        $option = $this->getMock(\Magento\Bundle\Model\Option::class, ['getSelections', '__wakeup'], [], '', false);
        $option->expects($this->any())->method('getSelections')
            ->will($this->returnValue(null));
        $bundleProduct = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->assertSame([], $this->model->createSelectionPriceList($option, $bundleProduct));
    }

    /**
     * @dataProvider dataProviderForGetterAmount
     */
    public function testGetterAmount($amountForBundle, $optionList, $expectedResult)
    {
        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->with($this->baseAmount, $this->saleableItem)
            ->will($this->returnValue($this->createAmountMock($amountForBundle)));

        $options = [];
        foreach ($optionList as $optionData) {
            $options[] = $this->createOptionMock($optionData);
        }
        $typeInstance  = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saleableItem->expects($this->any())->method('getTypeInstance')->willReturn($typeInstance);

        $optionsCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionsCollection->expects($this->atLeastOnce())->method('getIterator')
            ->willReturn(new \ArrayIterator($options));
        $optionsCollection->expects($this->atLeastOnce())->method('addFilter')
            ->willReturnSelf();
        $optionsCollection->expects($this->atLeastOnce())->method('getSize')
            ->willReturn(count($options));

        foreach ($options as $option) {
            $selectionsCollection = $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
            $selectionsCollection->expects($this->any())->method('getIterator')
                ->willReturn(new \ArrayIterator($option->getSelections()));
            $selectionsCollection->expects($this->atLeastOnce())->method('getFirstItem')
                ->willReturn($option->getSelections()[0]);

            $typeInstance->expects($this->atLeastOnce())->method('getSelectionsCollection')
                ->willReturn($selectionsCollection);
        }

        $typeInstance->expects($this->atLeastOnce())->method('getOptionsCollection')->willReturn($optionsCollection);

        $price = $this->getMock(\Magento\Bundle\Pricing\Price\BundleOptionPrice::class, [], [], '', false);
        $price->expects($this->atLeastOnce())->method('getOptions')->will($this->returnValue($options));

        $this->priceMocks[Price\BundleOptionPrice::PRICE_CODE] = $price;

        // Price type of saleable items
        $this->saleableItem->expects($this->any())->method('getPriceType')->will(
            $this->returnValue(
                ProductPrice::PRICE_TYPE_DYNAMIC
            )
        );

        $this->amountFactory->expects($this->atLeastOnce())->method('create')
            ->with($expectedResult['fullAmount'], $expectedResult['adjustments']);
        if ($expectedResult['isMinAmount']) {
            $this->model->getAmount($this->baseAmount, $this->saleableItem);
        } else {
            $this->model->getMaxAmount($this->baseAmount, $this->saleableItem);
        }
    }

    /**
     * @return array
     */
    public function dataProviderForGetterAmount()
    {
        return [
            // first case with minimal amount
            'case with getting minimal amount' => $this->getCaseWithMinAmount(),
            // second case with maximum amount
            'case with getting maximum amount' => $this->getCaseWithMaxAmount(),
            // third case without saleable items
            'case without saleable items' => $this->getCaseWithoutSaleableItems(),
            // fourth case without require options
            'case without required options' => $this->getCaseMinAmountWithoutRequiredOptions(),
        ];
    }

    protected function tearDown()
    {
        $this->priceMocks = [];
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
        $amount = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->setMethods(['getAdjustmentAmounts', 'getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $amount->expects($this->any())->method('getAdjustmentAmounts')
            ->will($this->returnValue($amountData['adjustmentsAmounts']));
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
        $option = $this->getMock(\Magento\Bundle\Model\Option::class, ['isMultiSelection', '__wakeup'], [], '', false);
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $selection */
        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['isSalable', 'getQuantity', 'getAmount', 'getProduct', '__wakeup'])
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
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->will($this->returnValue(false));
        $selection->expects($this->any())->method('getProduct')->will($this->returnValue($innerProduct));

        return $selection;
    }

    /**
     * Array for data provider dataProviderForGetterAmount for case 'case with getting minimal amount'
     *
     * @return array
     */
    protected function getCaseWithMinAmount()
    {
        return [
            'amountForBundle' => [
                'adjustmentsAmounts' => ['tax' => 102],
                'amount' => 782,
            ],
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
                        'first product selection' => [
                            'data' => ['price' => 70.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8, 'weee' => 10],
                                'amount' => 18,
                            ],
                        ],
                        'second product selection' => [
                            'data' => ['price' => 80.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 18],
                                'amount' => 28,
                            ],
                        ],
                        'third product selection with the lowest price' => [
                            'data' => ['price' => 50.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8, 'weee' => 10],
                                'amount' => 8,
                            ],
                        ],
                    ]
                ],
            ],
            'expectedResult' => [
                'isMinAmount' => true,
                'fullAmount' => 790.,
                'adjustments' => ['tax' => 110, 'weee' => 10],
            ]
        ];
    }

    /**
     * Array for data provider dataProviderForGetterAmount for case 'case with getting maximum amount'
     *
     * @return array
     */
    protected function getCaseWithMaxAmount()
    {
        return [
            'amountForBundle' => [
                'adjustmentsAmounts' => ['tax' => 102],
                'amount' => 782,
            ],
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
                        'first product selection' => [
                            'data' => ['price' => 50.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8, 'weee' => 10],
                                'amount' => 8,
                            ],
                        ],
                        'second product selection' => [
                            'data' => ['price' => 80.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 18],
                                'amount' => 8,
                            ],
                        ],
                    ]
                ],
                // second option with multiselection
                [
                    'isMultiSelection' => true,
                    'data' => [
                        'title' => 'test option 2',
                        'default_title' => 'test option 2',
                        'type' => 'select',
                        'option_id' => '2',
                        'position' => '1',
                        'required' => '1',
                    ],
                    'selections' => [
                        'first product selection' => [
                            'data' => ['price' => 20.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8],
                                'amount' => 8,
                            ],
                        ],
                        'second product selection' => [
                            'data' => ['price' => 110.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 28],
                                'amount' => 28,
                            ],
                        ],
                        'third product selection' => [
                            'data' => ['price' => 50.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 18],
                                'amount' => 18,
                            ],
                        ],
                    ]
                ],
            ],
            'expectedResult' => [
                'isMinAmount' => false,
                'fullAmount' => 844.,
                'adjustments' => ['tax' => 164, 'weee' => 10],
            ]
        ];
    }

    /**
     * Array for data provider dataProviderForGetterAmount for case 'case without saleable items'
     *
     * @return array
     */
    protected function getCaseWithoutSaleableItems()
    {
        return [
            'amountForBundle' => [
                'adjustmentsAmounts' => ['tax' => 102],
                'amount' => 782,
            ],
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
                    'selections' => []
                ],
            ],
            'expectedResult' => [
                'isMinAmount' => true,
                'fullAmount' => 782.,
                'adjustments' => ['tax' => 102],
            ]
        ];
    }

    /**
     * Array for data provider dataProviderForGetterAmount for case 'case without required options'
     *
     * @return array
     */
    protected function getCaseMinAmountWithoutRequiredOptions()
    {
        return [
            'amountForBundle' => [
                'adjustmentsAmounts' => [],
                'amount' => null,
            ],
            'optionList' => [
                // first option
                [
                    'isMultiSelection' => false,
                    'data' => [
                        'title' => 'test option 1',
                        'default_title' => 'test option 1',
                        'type' => 'select',
                        'option_id' => '1',
                        'position' => '0',
                        'required' => '0',
                    ],
                    'selections' => [
                        'first product selection' => [
                            'data' => ['price' => 20.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8],
                                'amount' => 8,
                            ],
                        ],
                        'second product selection' => [
                            'data' => ['price' => 30.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 10],
                                'amount' => 12,
                            ],
                        ],
                    ]
                ],
                // second option
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
                        'first product selection' => [
                            'data' => ['price' => 25.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 8],
                                'amount' => 9,
                            ],
                        ],
                        'second product selection' => [
                            'data' => ['price' => 35.],
                            'amount' => [
                                'adjustmentsAmounts' => ['tax' => 10],
                                'amount' => 10,
                            ],
                        ],
                    ]
                ],
            ],
            'expectedResult' => [
                'isMinAmount' => true,
                'fullAmount' => 8.,
                'adjustments' => ['tax' => 8],
            ]
        ];
    }

    public function testGetAmountWithoutOption()
    {
        $amount = 1;
        $result = 5;

        /** @var $calculatorMock Calculator|PHPUnit_Framework_MockObject_MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateBundleAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('calculateBundleAmount')
            ->with($amount, $this->saleableItem, [])
            ->will($this->returnValue($result));

        $this->assertEquals($result, $calculatorMock->getAmountWithoutOption($amount, $this->saleableItem));
    }

    public function testGetMinRegularAmount()
    {
        $amount = 1;
        $expectedResult = 5;

        $exclude = 'false';

        /** @var $calculatorMock Calculator|PHPUnit_Framework_MockObject_MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItem, $exclude, true, $amount, true)
            ->will($this->returnValue($expectedResult));

        $result = $calculatorMock->getMinRegularAmount($amount, $this->saleableItem, $exclude);

        $this->assertEquals($expectedResult, $result, 'Incorrect result');
    }

    public function testGetMaxRegularAmount()
    {
        $amount = 1;
        $expectedResult = 5;

        $exclude = 'false';

        /** @var $calculatorMock Calculator|PHPUnit_Framework_MockObject_MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItem, $exclude, false, $amount, true)
            ->will($this->returnValue($expectedResult));

        $result = $calculatorMock->getMaxRegularAmount($amount, $this->saleableItem, $exclude);

        $this->assertEquals($expectedResult, $result, 'Incorrect result');
    }

    /**
     * @dataProvider getOptionsAmountDataProvider
     */
    public function testGetOptionsAmount($searchMin, $useRegularPrice)
    {
        $amount = 1;
        $expectedResult = 5;

        $exclude = 'false';

        /** @var $calculatorMock Calculator|PHPUnit_Framework_MockObject_MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateBundleAmount', 'getSelectionAmounts'])
            ->getMock();

        $selections[] = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleSelectionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $calculatorMock->expects($this->once())
            ->method('getSelectionAmounts')
            ->with($this->saleableItem, $searchMin, $useRegularPrice)
            ->will($this->returnValue($selections));

        $calculatorMock->expects($this->once())
            ->method('calculateBundleAmount')
            ->with($amount, $this->saleableItem, $selections, $exclude)
            ->will($this->returnValue($expectedResult));

        $result = $calculatorMock->getOptionsAmount(
            $this->saleableItem,
            $exclude, $searchMin,
            $amount,
            $useRegularPrice
        );

        $this->assertEquals($expectedResult, $result, 'Incorrect result');
    }

    public function getOptionsAmountDataProvider()
    {
        return [
            'true, true' => [
                'searchMin' => true,
                'useRegularPrice' => true,
            ],
            'true, false' => [
                'searchMin' => true,
                'useRegularPrice' => false,
            ],
            'false, true' => [
                'searchMin' => false,
                'useRegularPrice' => true,
            ],
            'false, false' => [
                'searchMin' => false,
                'useRegularPrice' => false,
            ],
        ];
    }
}
