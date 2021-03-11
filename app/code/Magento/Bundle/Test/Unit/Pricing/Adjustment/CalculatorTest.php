<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected $priceMocks = [];

    /**
     * @var float
     */
    protected $baseAmount = 50.;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $baseCalculator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $amountFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectionFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $selectionPriceListProvider;

    /**
     * @var Calculator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->saleableItem = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', 'getPriceType', '__wakeup', 'getStore', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();
        $priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $priceInfo->expects($this->any())->method('getPrice')->willReturnCallback(
            
                function ($type) {
                    if (!isset($this->priceMocks[$type])) {
                        throw new \PHPUnit\Framework\ExpectationFailedException('Unexpected type of price model');
                    }
                    return $this->priceMocks[$type];
                }
            
        );
        $this->saleableItem->expects($this->any())->method('getPriceInfo')->willReturn($priceInfo);

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);

        $this->saleableItem->expects($this->any())->method('getStore')->willReturn($store);

        $this->baseCalculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);
        $this->amountFactory = $this->createMock(\Magento\Framework\Pricing\Amount\AmountFactory::class);

        $this->selectionFactory = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionFactory->expects($this->any())->method('create')->willReturnArgument(1);

        $this->taxData = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectionPriceListProvider = $this->getMockBuilder(
            \Magento\Bundle\Pricing\Adjustment\SelectionPriceListProviderInterface::class
        )->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Bundle\Pricing\Adjustment\Calculator::class,
            [
                'calculator' => $this->baseCalculator,
                'amountFactory' => $this->amountFactory,
                'bundleSelectionFactory' => $this->selectionFactory,
                'taxHelper' => $this->taxData,
                'priceCurrency' => $priceCurrency,
                'selectionPriceListProvider' => $this->selectionPriceListProvider
            ]
        );
    }

    public function testEmptySelectionPriceList()
    {
        $option = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['getSelections', '__wakeup']);
        $option->expects($this->any())->method('getSelections')
            ->willReturn(null);
        $bundleProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->assertSame([], $this->model->createSelectionPriceList($option, $bundleProduct));
    }

    /**
     * @dataProvider dataProviderForGetterAmount
     */
    public function testGetterAmount($amountForBundle, $optionList, $expectedResult)
    {
        $searchMin = $expectedResult['isMinAmount'];
        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->with($this->baseAmount, $this->saleableItem)
            ->willReturn($this->createAmountMock($amountForBundle));

        $options = [];
        foreach ($optionList as $optionData) {
            $options[] = $this->createOptionMock($optionData);
        }

        $optionSelections = [];
        foreach ($options as $option) {
            $optionSelections = array_merge($optionSelections, $option->getSelections());
        }
        $this->selectionPriceListProvider->expects($this->any())->method('getPriceList')->willReturn($optionSelections);

        $price = $this->createMock(\Magento\Bundle\Pricing\Price\BundleOptionPrice::class);
        $this->priceMocks[Price\BundleOptionPrice::PRICE_CODE] = $price;

        // Price type of saleable items
        $this->saleableItem->expects($this->any())->method('getPriceType')->willReturn(
            
                ProductPrice::PRICE_TYPE_DYNAMIC
            
        );

        $this->amountFactory->expects($this->atLeastOnce())->method('create')
            ->with($expectedResult['fullAmount'], $expectedResult['adjustments']);
        if ($searchMin) {
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

    protected function tearDown(): void
    {
        $this->priceMocks = [];
    }

    /**
     * Create amount mock
     *
     * @param array $amountData
     * @return \Magento\Framework\Pricing\Amount\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAmountMock($amountData)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Pricing\Amount\Base $amount */
        $amount = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->setMethods(['getAdjustmentAmounts', 'getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $amount->expects($this->any())->method('getAdjustmentAmounts')
            ->willReturn($amountData['adjustmentsAmounts']);
        $amount->expects($this->any())->method('getValue')->willReturn($amountData['amount']);
        return $amount;
    }

    /**
     * Create option mock
     *
     * @param array $optionData
     * @return \Magento\Bundle\Model\Option|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createOptionMock($optionData)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Bundle\Model\Option $option */
        $option = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['isMultiSelection', '__wakeup']);
        $option->expects($this->any())->method('isMultiSelection')
            ->willReturn($optionData['isMultiSelection']);
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
     * @return \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSelectionMock($selectionData)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product $selection */
        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['isSalable', 'getQuantity', 'getAmount', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        // All items are saleable
        $selection->expects($this->any())->method('isSalable')->willReturn(true);
        foreach ($selectionData['data'] as $key => $value) {
            $selection->setData($key, $value);
        }
        $amountMock = $this->createAmountMock($selectionData['amount']);
        $selection->expects($this->any())->method('getAmount')->willReturn($amountMock);
        $selection->expects($this->any())->method('getQuantity')->willReturn(1);

        $innerProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSelectionCanChangeQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->willReturn(false);
        $selection->expects($this->any())->method('getProduct')->willReturn($innerProduct);

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
                        'selection with the lowest price' => [
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

        /** @var $calculatorMock Calculator|PHPUnit\Framework\MockObject\MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateBundleAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('calculateBundleAmount')
            ->with($amount, $this->saleableItem, [])
            ->willReturn($result);

        $this->assertEquals($result, $calculatorMock->getAmountWithoutOption($amount, $this->saleableItem));
    }

    public function testGetMinRegularAmount()
    {
        $amount = 1;
        $expectedResult = 5;

        $exclude = 'false';

        /** @var $calculatorMock Calculator|PHPUnit\Framework\MockObject\MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItem, $exclude, true, $amount, true)
            ->willReturn($expectedResult);

        $result = $calculatorMock->getMinRegularAmount($amount, $this->saleableItem, $exclude);

        $this->assertEquals($expectedResult, $result, 'Incorrect result');
    }

    public function testGetMaxRegularAmount()
    {
        $amount = 1;
        $expectedResult = 5;

        $exclude = 'false';

        /** @var $calculatorMock Calculator|PHPUnit\Framework\MockObject\MockObject */
        $calculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsAmount'])
            ->getMock();

        $calculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItem, $exclude, false, $amount, true)
            ->willReturn($expectedResult);

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

        /** @var $calculatorMock Calculator|PHPUnit\Framework\MockObject\MockObject */
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
            ->willReturn($selections);

        $calculatorMock->expects($this->once())
            ->method('calculateBundleAmount')
            ->with($amount, $this->saleableItem, $selections, $exclude)
            ->willReturn($expectedResult);

        $result = $calculatorMock->getOptionsAmount(
            $this->saleableItem,
            $exclude,
            $searchMin,
            $amount,
            $useRegularPrice
        );

        $this->assertEquals($expectedResult, $result, 'Incorrect result');
    }

    /**
     * @return array
     */
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
