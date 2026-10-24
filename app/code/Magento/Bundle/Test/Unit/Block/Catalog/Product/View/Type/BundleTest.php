<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Catalog\Product\View\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleBlock;
use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\PriceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Bundle\Pricing\Price\TierPrice;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Json\Encoder;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var PriceFactory|MockObject
     */
    private $bundleProductPriceFactory;

    /**
     * @var Encoder|MockObject
     */
    private $jsonEncoder;

    /**
     * @var Product|MockObject
     */
    private $catalogProduct;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var BundleBlock
     */
    private $bundleBlock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Directory\Model\PriceCurrency|MockObject
     */
    private $priceCurrency;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);

        $this->bundleProductPriceFactory = $this->createPartialMock(PriceFactory::class, ['create']);

        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            ['getTypeInstance', 'getStoreId', 'getPriceInfo']
        );
        $registry = $this->createPartialMock(Registry::class, ['registry']);
        $registry->method('registry')->willReturn($this->product);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->jsonEncoder = $this->createMock(Encoder::class);
        $this->catalogProduct = $this->createMock(ProductHelper::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->priceCurrency = $this->createMock(\Magento\Directory\Model\PriceCurrency::class);

        /** @var BundleBlock $bundleBlock */
        $this->bundleBlock = $objectHelper->getObject(
            BundleBlock::class,
            [
                'registry' => $registry,
                'eventManager' => $this->eventManager,
                'jsonEncoder' => $this->jsonEncoder,
                'productPrice' => $this->bundleProductPriceFactory,
                'catalogProduct' => $this->catalogProduct,
                'escaper' => $this->escaperMock,
                'priceCurrency' => $this->priceCurrency
            ]
        );

        $ruleProcessor = $this->createMock(
            CollectionProcessor::class
        );
        $objectHelper->setBackwardCompatibleProperty(
            $this->bundleBlock,
            'catalogRuleProcessor',
            $ruleProcessor
        );
    }

    /**
     * @return void
     */
    public function testGetOptionHtmlNoRenderer(): void
    {
        $option = $this->createPartialMock(Option::class, ['getType']);
        $option->method('getType')->willReturn('checkbox');
        $this->escaperMock->expects($this->once())->method('escapeHtml')->willReturn('checkbox');
        $expected='There is no defined renderer for "checkbox" option type.';
        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->method('getChildName')->willReturn(false);
        $this->bundleBlock->setLayout($layout);
        $this->assertEquals(
            $expected,
            $this->bundleBlock->getOptionHtml($option)
        );
    }

    /**
     * @return void
     */
    public function testGetOptionHtml(): void
    {
        $option = $this->createPartialMock(Option::class, ['getType']);
        $option->expects($this->once())->method('getType')->willReturn('checkbox');

        $optionBlock = $this->createPartialMock(Checkbox::class, ['setOption', 'toHtml']);
        $optionBlock->expects($this->any())->method('setOption')->willReturnSelf();
        $optionBlock->method('toHtml')->willReturn('option html');
        $layout = $this->createPartialMock(Layout::class, ['getChildName', 'getBlock']);
        $layout->method('getChildName')->willReturn('name');
        $layout->method('getBlock')->willReturn($optionBlock);
        $this->bundleBlock->setLayout($layout);

        $this->assertEquals('option html', $this->bundleBlock->getOptionHtml($option));
    }

    /**
     * @return void
     */
    public function testGetJsonConfigFixedPriceBundleNoOption(): void
    {
        $options = [];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100
                    ]
                )
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new DataObject(
                    [
                        'value' => 110,
                        'base_amount' => 110
                    ]
                )
            ]
        );
        $prices = [
            FinalPrice::PRICE_CODE => $finalPriceMock,
            RegularPrice::PRICE_CODE => $regularPriceMock
        ];
        $priceInfo = $this->getPriceInfoMock($prices);

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
    }

    /**
     * @return void
     */
    public function testGetJsonConfigFixedPriceBundle(): void
    {
        $optionId = 1;
        $optionQty = 2;
        $baseAmount = 123;
        $basePriceValue = 123123;
        $selections = [
            $this->createOptionSelection(
                1123,
                'Selection 1',
                23,
                [
                    ['price' => new DataObject(
                        ['base_amount' => $baseAmount, 'value' => $basePriceValue]
                    )]
                ],
                true,
                true
            )
        ];
        $bundleProductPrice = $this->createPartialMock(Price::class, ['getLowestPrice']);
        $this->product->setLowestPriceCallback(function ($arg1, $arg2) use ($baseAmount, $basePriceValue) {
            if ($arg1 == $this->product && $arg2==$baseAmount) {
                return 999;
            } elseif ($arg1 == $this->product && $arg2==$basePriceValue) {
                return 888;
            }
            return null;
        });
        $this->bundleProductPriceFactory->expects($this->once())
            ->method('create')
            ->willReturn($bundleProductPrice);
        $options = [$this->createOption($optionId, 'Title `1', $selections)];
        $finalPriceMock = $this->getPriceMock(
            [
                'getPriceWithoutOption' => new DataObject(
                    [
                        'value' => 100,
                        'base_amount' => 100
                    ]
                )
            ]
        );
        $regularPriceMock = $this->getPriceMock(
            [
                'getAmount' => new DataObject(
                    [
                        'value' => 110,
                        'base_amount' => 110
                    ]
                )
            ]
        );

        $this->priceCurrency->expects($this->exactly(3))
            ->method('roundPrice')
            ->willReturn($basePriceValue);

        $bundleOptionPriceMock = $this->getBundleOptionPriceMock(
            $baseAmount,
            $baseAmount,
            [['item' => $selections[0], 'value' => $basePriceValue, 'base_amount' => 321321]]
        );
        $prices = [
            'bundle_option' => $bundleOptionPriceMock,
            'bundle_option_regular_price' => $bundleOptionPriceMock,
            FinalPrice::PRICE_CODE => $finalPriceMock,
            RegularPrice::PRICE_CODE => $regularPriceMock
        ];
        $priceInfo = $this->getPriceInfoMock($prices);
        $this->product->setHasPreconfiguredValues(true);
        $preconfiguredValues = new DataObject(
            [
                'bundle_option' => [
                    $optionId => [123123111]
                ],
                'bundle_option_qty' => [
                    $optionId => $optionQty
                ]
            ]
        );
        $this->product->setPreconfiguredValues($preconfiguredValues);

        $this->updateBundleBlock(
            $options,
            $priceInfo,
            Price::PRICE_TYPE_FIXED
        );
        $jsonConfig = $this->bundleBlock->getJsonConfig();
        $this->assertEquals(110, $jsonConfig['prices']['oldPrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['basePrice']['amount']);
        $this->assertEquals(100, $jsonConfig['prices']['finalPrice']['amount']);
        $this->assertEquals([$optionId], $jsonConfig['positions']);
        $this->assertEquals($optionQty, $jsonConfig['options'][$optionId]['selections'][1123]['qty']);
    }

    /**
     * @param array $options
     * @param Base|MockObject $priceInfo
     * @param int $priceType
     *
     * @return void
     */
    private function updateBundleBlock(array $options, Base $priceInfo, int $priceType): void
    {
        $this->eventManager->method('dispatch')->willReturn(true);
        $optionCollection = $this->createMock(Collection::class);
        $optionCollection->method('appendSelections')->willReturn($options);

        $selectionCollection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class);
        $selectionCollection->expects($this->once())->method('addTierPriceData');

        $typeInstance = $this->createMock(Type::class);
        $typeInstance->method('getOptionsCollection')->willReturn($optionCollection);
        $typeInstance->method('getStoreFilter')->willReturn(true);
        $typeInstance->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn($selectionCollection);

        $this->product->setTypeInstance($typeInstance);
        $this->product->method('getTypeInstance')->willReturn($typeInstance);
        $this->product->method('getStoreId')->willReturn(0);
        $this->product->setPriceInfo($priceInfo);
        $this->product->method('getPriceInfo')->willReturn($priceInfo);
        $this->product->setPriceType($priceType);
        $this->jsonEncoder->expects($this->any())
            ->method('encode')
            ->willReturnArgument(0);
    }

    /**
     * @param $price
     *
     * @return MockObject|Base
     */
    private function getPriceInfoMock($price): Base
    {
        $priceInfoMock = $this->createPartialMock(Base::class, ['getPrice']);

        if (is_array($price)) {
            $withArgs = $willReturnArgs = [];

            foreach ($price as $priceType => $priceValue) {
                $withArgs[] = [$priceType];
                $willReturnArgs[] = $priceValue;
            }
            $priceInfoMock
                ->method('getPrice')
                ->willReturnCallback(function ($withArgs) use ($willReturnArgs) {
                    static $callCount = 0;
                    $returnValue = $willReturnArgs[$callCount] ?? null;
                    $callCount++;
                    return $returnValue;
                });
        } else {
            $priceInfoMock->method('getPrice')->willReturn($price);
        }
        return $priceInfoMock;
    }

    /**
     * @param $prices
     *
     * @return MockObject
     */
    private function getPriceMock($prices)
    {
        $priceMock = $this->createPartialMockWithReflection(BasePrice::class, array_keys($prices));

        foreach ($prices as $methodName => $amount) {
            $priceMock->method($methodName)->willReturn($amount);
        }

        return $priceMock;
    }

    /**
     * Create a BundleOptionPrice mock with getOptionSelectionAmount method
     *
     * @param float $value
     * @param mixed $baseAmount
     * @param array $selectionAmounts
     * @return MockObject
     */
    private function getBundleOptionPriceMock($value, $baseAmount, array $selectionAmounts): MockObject
    {
        $bundleOptionPrice = $this->createMock(BundleOptionPrice::class);

        // Mock getOptionSelectionAmount to return AmountInterface with proper values
        $bundleOptionPrice->method('getOptionSelectionAmount')->willReturnCallback(
            function ($selection) use ($selectionAmounts, $value, $baseAmount) {
                // Check if this selection has specific amounts configured
                foreach ($selectionAmounts as $selectionAmount) {
                    if ($selection === $selectionAmount['item']) {
                        return $this->createAmountMock($selectionAmount['value'], $selectionAmount['base_amount']);
                    }
                }

                // Default amount for other selections
                return $this->createAmountMock($value, $baseAmount);
            }
        );

        return $bundleOptionPrice;
    }

    /**
     * @param int $id
     * @param string $title
     * @param Product[]|MockObject[] $selections
     * @param int|string $type
     * @param bool $isRequired
     *
     * @return MockObject
     * @internal param bool $isDefault
     */
    private function createOption(
        $id,
        $title,
        array $selections = [],
        $type = 'checkbox',
        $isRequired = false
    ) {
        // Use partial mock - all setters work via magic methods
        $option = $this->createPartialMock(Option::class, []);
        $option->setId($id);
        $option->setTitle($title);
        $option->setSelections($selections);
        $option->setType($type);
        $option->setRequired($isRequired);

        return $option;
    }

    /**
     * @param int $id
     * @param string $name
     * @param float $qty
     * @param array $tierPriceList
     * @param bool $isCanChangeQty
     * @param bool $isDefault
     * @param bool $isSalable
     *
     * @return Product|MockObject
     */
    private function createOptionSelection(
        $id,
        $name,
        $qty,
        array $tierPriceList = [],
        $isCanChangeQty = true,
        $isDefault = false,
        $isSalable = true
    ) {
        $selection = $this->createPartialMockWithReflection(Product::class, ['getPriceInfo', 'isSalable']);
        $tierPrice = $this->createPartialMock(TierPrice::class, ['getTierPriceList']);
        $tierPrice->method('getTierPriceList')->willReturn($tierPriceList);
        $priceInfo = $this->createPartialMock(Base::class, ['getPrice']);
        $priceInfo->method('getPrice')->willReturn($tierPrice);
        $selection->setSelectionId($id);
        $selection->setName($name);
        $selection->setSelectionQty($qty);
        $selection->setPriceInfo($priceInfo);
        $selection->method('getPriceInfo')->willReturn($priceInfo);
        $selection->setSelectionCanChangeQty($isCanChangeQty);
        $selection->setIsDefault($isDefault);
        $selection->setIsSalable($isSalable);
        $selection->method('isSalable')->willReturn($isSalable);

        return $selection;
    }

    /**
     * @param bool $stripSelection
     *
     * @return void
     */
    #[DataProvider('getOptionsDataProvider')]
    public function testGetOptions(bool $stripSelection): void
    {
        $newOptions = ['option_1', 'option_2'];

        $optionCollection = $this->createMock(Collection::class);
        $selectionConnection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class);
        $typeInstance = $this->createMock(Type::class);

        $optionCollection->expects($this->any())->method('appendSelections')
            ->with($selectionConnection, $stripSelection, true)
            ->willReturn($newOptions);
        $typeInstance->expects($this->any())->method('setStoreFilter')->with(0, $this->product)
            ->willReturn($optionCollection);
        $typeInstance->method('getStoreFilter')->willReturn(true);
        $typeInstance->method('getOptionsCollection')->willReturn($optionCollection);
        $typeInstance->method('getOptionsIds')->willReturn([1, 2]);
        $typeInstance->expects($this->once())->method('getSelectionsCollection')->with([1, 2], $this->product)
            ->willReturn($selectionConnection);
        $this->product->setTypeInstance($typeInstance);
        $this->product->method('getTypeInstance')->willReturn($typeInstance);
        $this->product->method('getStoreId')->willReturn(0);
        $this->product->setStoreId(0);
        $this->catalogProduct->expects($this->once())->method('getSkipSaleableCheck')->willReturn(true);

        $this->assertEquals($newOptions, $this->bundleBlock->getOptions($stripSelection));
    }

    /**
     * @return array
     */
    public static function getOptionsDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Create a properly mocked AmountInterface with all required methods
     *
     * @param float $value
     * @param float $baseAmount
     * @return AmountInterface|MockObject
     */
    private function createAmountMock($value, $baseAmount)
    {
        $amount = $this->createMock(AmountInterface::class);
        $amount->method('getValue')->willReturn($value);
        $amount->method('getBaseAmount')->willReturn($baseAmount);
        $amount->method('__toString')->willReturn((string)$value);
        $amount->method('getAdjustmentAmount')->willReturn(0);
        $amount->method('getTotalAdjustmentAmount')->willReturn(0);
        $amount->method('getAdjustmentAmounts')->willReturn([]);
        $amount->method('hasAdjustment')->willReturn(false);
        return $amount;
    }
}
