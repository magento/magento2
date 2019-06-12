<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
<<<<<<< HEAD

/**
=======
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Adjustment\Calculator as AdjustmentCalculator;
use Magento\Framework\Pricing\PriceInfo\Base as BasePriceInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\Base as BaseAmount;
use Magento\Bundle\Pricing\Price\BundleOptions;
use Magento\Bundle\Pricing\Price\BundleSelectionPrice;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Bundle\Pricing\Adjustment\Calculator as BundleAdjustmentCalculator;
use Magento\Bundle\Model\Option as BundleOption;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Bundle\Model\ResourceModel\Option\Collection as BundleOptionCollection;
use Magento\Catalog\Model\Product;
use Magento\Tax\Helper\Data as TaxHelperData;

/**
 * Test for Magento\Bundle\Pricing\Price\BundleOptions
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Price\BundleOptions
=======
     * @var BundleOptions
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleOptions;

    /**
<<<<<<< HEAD
     * @var \PHPUnit_Framework_MockObject_MockObject
=======
     * @var AdjustmentCalculator|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $baseCalculator;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var Product|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $saleableItemMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var BundleAdjustmentCalculator|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleCalculatorMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var BundleSelectionFactory|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $selectionFactoryMock;

    /**
<<<<<<< HEAD
     * @var \PHPUnit_Framework_MockObject_MockObject
=======
     * @var AmountFactory|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $amountFactory;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var BasePriceInfo|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $priceInfoMock;

    protected function setUp()
    {
<<<<<<< HEAD
        $this->priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->saleableItemMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));
        $this->selectionFactoryMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactory = $this->createMock(\Magento\Framework\Pricing\Amount\AmountFactory::class);
=======
        $this->priceInfoMock = $this->getMockBuilder(BasePriceInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saleableItemMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)->getMock();
        $priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);

        $this->selectionFactoryMock = $this->getMockBuilder(BundleSelectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactory = $this->getMockBuilder(AmountFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $factoryCallback = $this->returnCallback(
            function ($fullAmount, $adjustments) {
                return $this->createAmountMock(['amount' => $fullAmount, 'adjustmentAmounts' => $adjustments]);
            }
        );
        $this->amountFactory->expects($this->any())->method('create')->will($factoryCallback);
<<<<<<< HEAD
        $this->baseCalculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);

        $taxData = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundleCalculatorMock = $this->getMockBuilder(\Magento\Bundle\Pricing\Adjustment\Calculator::class)
=======
        $this->baseCalculator = $this->getMockBuilder(AdjustmentCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxData = $this->getMockBuilder(TaxHelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundleCalculatorMock = $this->getMockBuilder(BundleAdjustmentCalculator::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->setConstructorArgs(
                [$this->baseCalculator, $this->amountFactory, $this->selectionFactoryMock, $taxData, $priceCurrency]
            )
            ->setMethods(['getOptionsAmount'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptions = $this->objectManagerHelper->getObject(
<<<<<<< HEAD
            \Magento\Bundle\Pricing\Price\BundleOptions::class,
            [
                'calculator' => $this->bundleCalculatorMock,
                'bundleSelectionFactory' => $this->selectionFactoryMock
=======
            BundleOptions::class,
            [
                'calculator' => $this->bundleCalculatorMock,
                'bundleSelectionFactory' => $this->selectionFactoryMock,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
    }

    /**
     * @dataProvider getOptionsDataProvider
<<<<<<< HEAD
     */
    public function testGetOptions(string $selectionCollection)
=======
     * @param array $selectionCollection
     *
     * @return void
     */
    public function testGetOptions(array $selectionCollection)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->prepareOptionMocks($selectionCollection);
        $this->bundleOptions->getOptions($this->saleableItemMock);
        $this->assertSame($selectionCollection, $this->bundleOptions->getOptions($this->saleableItemMock));
<<<<<<< HEAD
        $this->assertSame($selectionCollection, $this->bundleOptions->getOptions($this->saleableItemMock));
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @param array $selectionCollection
<<<<<<< HEAD
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
=======
     *
     * @return void
     */
    private function prepareOptionMocks(array $selectionCollection)
    {
        $this->saleableItemMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);
        $priceTypeMock = $this->getMockBuilder(BundleProductType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('setStoreFilter')
            ->with(1, $this->saleableItemMock)
            ->willReturnSelf();
        $optionIds = ['41', '55'];
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getOptionsIds')
            ->with($this->saleableItemMock)
            ->willReturn($optionIds);
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getSelectionsCollection')
            ->with($optionIds, $this->saleableItemMock)
            ->willReturn($selectionCollection);
        $collection = $this->getMockBuilder(BundleOptionCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())
            ->method('appendSelections')
            ->with($selectionCollection, true, false)
            ->willReturn($selectionCollection);
        $priceTypeMock->expects($this->atLeastOnce())
            ->method('getOptionsCollection')
            ->with($this->saleableItemMock)
            ->willReturn($collection);
        $this->saleableItemMock->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->willReturn($priceTypeMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function getOptionsDataProvider()
    {
        return [
            ['1', '2']
=======
    public function getOptionsDataProvider() : array
    {
        return [
            [
                ['1', '2'],
            ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }

    /**
<<<<<<< HEAD
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
=======
     * @dataProvider selectionAmountDataProvider
     *
     * @param float $selectionQty
     * @param float|bool $selectionAmount
     * @param bool $useRegularPrice
     *
     * @return void
     */
    public function testGetOptionSelectionAmount(float $selectionQty, $selectionAmount, bool $useRegularPrice)
    {
        $selection = $this->createPartialMock(Product::class, ['getSelectionQty', '__wakeup']);
        $amountInterfaceMock = $this->getMockBuilder(AmountInterface::class)
            ->getMockForAbstractClass();
        $amountInterfaceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($selectionAmount);
        $selection->expects($this->once())
            ->method('getSelectionQty')
            ->willReturn($selectionQty);
        $priceMock = $this->getMockBuilder(BundleSelectionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountInterfaceMock);
        $this->selectionFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->saleableItemMock, $selection, $selectionQty)
            ->willReturn($priceMock);
        $optionSelectionAmount = $this->bundleOptions->getOptionSelectionAmount(
            $this->saleableItemMock,
            $selection,
            $useRegularPrice
        );
        $this->assertSame($selectionAmount, $optionSelectionAmount->getValue());
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @return array
     */
    public function selectionAmountDataProvider(): array
    {
        return [
            [1., 50.5, false],
<<<<<<< HEAD
            [2.2, false, true]
=======
            [2.2, false, true],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }

    /**
<<<<<<< HEAD
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
=======
     * Create amount mock.
     *
     * @param array $amountData
     * @return BaseAmount|MockObject
     */
    private function createAmountMock(array $amountData)
    {
        /** @var BaseAmount|MockObject $amount */
        $amount = $this->getMockBuilder(BaseAmount::class)
            ->disableOriginalConstructor()
            ->getMock();
        $amount->expects($this->any())->method('getAdjustmentAmounts')
            ->willReturn($amountData['adjustmentAmounts'] ?? []);
        $amount->expects($this->any())->method('getValue')->willReturn($amountData['amount']);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $amount;
    }

    /**
<<<<<<< HEAD
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
=======
     * Create option mock.
     *
     * @param array $optionData
     * @return BundleOption|MockObject
     */
    private function createOptionMock(array $optionData)
    {
        /** @var BundleOption|MockObject $option */
        $option = $this->createPartialMock(BundleOption::class, ['isMultiSelection', '__wakeup']);
        $option->expects($this->any())->method('isMultiSelection')
            ->willReturn($optionData['isMultiSelection']);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $selections = [];
        foreach ($optionData['selections'] as $selectionData) {
            $selections[] = $this->createSelectionMock($selectionData);
        }
        foreach ($optionData['data'] as $key => $value) {
            $option->setData($key, $value);
        }
        $option->setData('selections', $selections);
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $option;
    }

    /**
<<<<<<< HEAD
     * Create selection product mock
     *
     * @param array $selectionData
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSelectionMock($selectionData)
    {
        $selection = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
=======
     * Create selection product mock.
     *
     * @param array $selectionData
     * @return Product|MockObject
     */
    private function createSelectionMock(array $selectionData)
    {
        $selection = $this->getMockBuilder(Product::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->setMethods(['isSalable', 'getAmount', 'getQuantity', 'getProduct', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        // All items are saleable
<<<<<<< HEAD
        $selection->expects($this->any())->method('isSalable')->will($this->returnValue(true));
=======
        $selection->expects($this->any())->method('isSalable')->willReturn(true);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        foreach ($selectionData['data'] as $key => $value) {
            $selection->setData($key, $value);
        }
        $amountMock = $this->createAmountMock($selectionData['amount']);
<<<<<<< HEAD
        $selection->expects($this->any())->method('getAmount')->will($this->returnValue($amountMock));
        $selection->expects($this->any())->method('getQuantity')->will($this->returnValue(1));

        $innerProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSelectionCanChangeQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->will($this->returnValue(true));
        $selection->expects($this->any())->method('getProduct')->will($this->returnValue($innerProduct));
=======
        $selection->expects($this->any())->method('getAmount')->willReturn($amountMock);
        $selection->expects($this->any())->method('getQuantity')->willReturn(1);

        $innerProduct = $this->getMockBuilder(Product::class)
            ->setMethods(['getSelectionCanChangeQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->willReturn(true);
        $selection->expects($this->any())->method('getProduct')->willReturn($innerProduct);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        return $selection;
    }

    /**
     * @dataProvider getTestDataForCalculation
<<<<<<< HEAD
=======
     * @param array $optionList
     * @param array $expected
     *
     * @return void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testCalculation(array $optionList, array $expected)
    {
        $storeId = 1;
<<<<<<< HEAD
        $this->saleableItemMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->selectionFactoryMock->expects($this->any())->method('create')->will($this->returnArgument(1));

        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->will($this->returnValue($this->createAmountMock(['amount' => 0.])));
=======
        $this->saleableItemMock->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->selectionFactoryMock->expects($this->any())->method('create')->willReturnArgument(1);

        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->willReturn($this->createAmountMock(['amount' => 0.]));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $options = [];
        foreach ($optionList as $optionData) {
            $options[] = $this->createOptionMock($optionData);
        }
<<<<<<< HEAD
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
=======
        /** @var BundleOptionCollection|MockObject $optionsCollection */
        $optionsCollection = $this->getMockBuilder(BundleOptionCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionsCollection->expects($this->atLeastOnce())->method('appendSelections')->willReturn($options);

        /** @var \Magento\Catalog\Model\Product\Type\AbstractType|MockObject $typeMock */
        $typeMock = $this->getMockBuilder(BundleProductType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->any())->method('setStoreFilter')
            ->with($storeId, $this->saleableItemMock);
        $typeMock->expects($this->any())->method('getOptionsCollection')
            ->with($this->saleableItemMock)
            ->willReturn($optionsCollection);
        $this->saleableItemMock->expects($this->any())->method('getTypeInstance')->willReturn($typeMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->assertEquals($expected['min'], $this->bundleOptions->calculateOptions($this->saleableItemMock));
        $this->assertEquals($expected['max'], $this->bundleOptions->calculateOptions($this->saleableItemMock, false));
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function getTestDataForCalculation()
=======
    public function getTestDataForCalculation(): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                                'amount' => ['amount' => 80]
                            ],
                            [
                                'data' => ['price' => 50.],
                                'amount' => ['amount' => 50]
                            ],
                        ]
=======
                                'amount' => ['amount' => 80],
                            ],
                            [
                                'data' => ['price' => 50.],
                                'amount' => ['amount' => 50],
                            ],
                        ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                        ]
=======
                        ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                                'amount' => ['amount' => 20]
                            ],
                            [
                                'data' => ['price' => 60.],
                                'amount' => ['amount' => 60]
                            ],
                        ]
=======
                                'amount' => ['amount' => 20],
                            ],
                            [
                                'data' => ['price' => 60.],
                                'amount' => ['amount' => 60],
                            ],
                        ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                        'selections' => []
                    ],
                ],
                'expected' => ['min' => 70, 'max' => 220],
            ]
=======
                        'selections' => [],
                    ],
                ],
                'expected' => ['min' => 70, 'max' => 220],
            ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }
}
