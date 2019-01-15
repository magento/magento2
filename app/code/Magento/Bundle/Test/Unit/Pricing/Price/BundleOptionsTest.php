<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BundleOptions
     */
    private $bundleOptions;

    /**
     * @var AdjustmentCalculator|MockObject
     */
    private $baseCalculator;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Product|MockObject
     */
    private $saleableItemMock;

    /**
     * @var BundleAdjustmentCalculator|MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var BundleSelectionFactory|MockObject
     */
    private $selectionFactoryMock;

    /**
     * @var AmountFactory|MockObject
     */
    private $amountFactory;

    /**
     * @var BasePriceInfo|MockObject
     */
    private $priceInfoMock;

    protected function setUp()
    {
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
        $factoryCallback = $this->returnCallback(
            function ($fullAmount, $adjustments) {
                return $this->createAmountMock(['amount' => $fullAmount, 'adjustmentAmounts' => $adjustments]);
            }
        );
        $this->amountFactory->expects($this->any())->method('create')->will($factoryCallback);
        $this->baseCalculator = $this->getMockBuilder(AdjustmentCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxData = $this->getMockBuilder(TaxHelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundleCalculatorMock = $this->getMockBuilder(BundleAdjustmentCalculator::class)
            ->setConstructorArgs(
                [$this->baseCalculator, $this->amountFactory, $this->selectionFactoryMock, $taxData, $priceCurrency]
            )
            ->setMethods(['getOptionsAmount'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptions = $this->objectManagerHelper->getObject(
            BundleOptions::class,
            [
                'calculator' => $this->bundleCalculatorMock,
                'bundleSelectionFactory' => $this->selectionFactoryMock,
            ]
        );
    }

    /**
     * @dataProvider getOptionsDataProvider
     * @param array $selectionCollection
     *
     * @return void
     */
    public function testGetOptions(array $selectionCollection)
    {
        $this->prepareOptionMocks($selectionCollection);
        $this->bundleOptions->getOptions($this->saleableItemMock);
        $this->assertSame($selectionCollection, $this->bundleOptions->getOptions($this->saleableItemMock));
    }

    /**
     * @param array $selectionCollection
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
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider() : array
    {
        return [
            [
                ['1', '2'],
            ],
        ];
    }

    /**
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
    }

    /**
     * @return array
     */
    public function selectionAmountDataProvider(): array
    {
        return [
            [1., 50.5, false],
            [2.2, false, true],
        ];
    }

    /**
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

        return $amount;
    }

    /**
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
     * Create selection product mock.
     *
     * @param array $selectionData
     * @return Product|MockObject
     */
    private function createSelectionMock(array $selectionData)
    {
        $selection = $this->getMockBuilder(Product::class)
            ->setMethods(['isSalable', 'getAmount', 'getQuantity', 'getProduct', '__wakeup'])
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

        $innerProduct = $this->getMockBuilder(Product::class)
            ->setMethods(['getSelectionCanChangeQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $innerProduct->expects($this->any())->method('getSelectionCanChangeQty')->willReturn(true);
        $selection->expects($this->any())->method('getProduct')->willReturn($innerProduct);

        return $selection;
    }

    /**
     * @dataProvider getTestDataForCalculation
     * @param array $optionList
     * @param array $expected
     *
     * @return void
     */
    public function testCalculation(array $optionList, array $expected)
    {
        $storeId = 1;
        $this->saleableItemMock->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->selectionFactoryMock->expects($this->any())->method('create')->willReturnArgument(1);

        $this->baseCalculator->expects($this->atLeastOnce())->method('getAmount')
            ->willReturn($this->createAmountMock(['amount' => 0.]));

        $options = [];
        foreach ($optionList as $optionData) {
            $options[] = $this->createOptionMock($optionData);
        }
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

        $this->assertEquals($expected['min'], $this->bundleOptions->calculateOptions($this->saleableItemMock));
        $this->assertEquals($expected['max'], $this->bundleOptions->calculateOptions($this->saleableItemMock, false));
    }

    /**
     * @return array
     */
    public function getTestDataForCalculation(): array
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
                                'amount' => ['amount' => 80],
                            ],
                            [
                                'data' => ['price' => 50.],
                                'amount' => ['amount' => 50],
                            ],
                        ],
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
                        ],
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
                                'amount' => ['amount' => 20],
                            ],
                            [
                                'data' => ['price' => 60.],
                                'amount' => ['amount' => 60],
                            ],
                        ],
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
                        'selections' => [],
                    ],
                ],
                'expected' => ['min' => 70, 'max' => 220],
            ],
        ];
    }
}
