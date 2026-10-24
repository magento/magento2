<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Option\Type\Select;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomOptionPriceTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var CustomOptionPrice
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $product;

    /**
     * @var Base|MockObject
     */
    protected $priceInfo;

    /**
     * @var Calculator|MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|MockObject
     */
    protected $amount;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->product = $this->createPartialMock(
            Product::class,
            ['getOptionById', 'getPriceInfo', 'getOptions']
        );

        $this->priceInfo = $this->createMock(Base::class);

        $this->product->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->calculator = $this->createMock(Calculator::class);

        $this->amount = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $customOptionPriceCalculator = $this->createMock(CustomOptionPriceCalculator::class);
        // Configure the calculator to return the price from the option value
        $customOptionPriceCalculator->method('getOptionPriceByPriceCode')
            ->willReturnCallback(function ($optionValue) {
                return $optionValue->getData(Value::KEY_PRICE);
            });
        $this->object = new CustomOptionPrice(
            $this->product,
            PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT,
            $this->calculator,
            $this->priceCurrencyMock,
            null,
            $customOptionPriceCalculator
        );
    }

    /**
     * @param array $optionsData
     *
     * @return array
     */
    protected function setupOptions(array $optionsData): array
    {
        $options = [];
        foreach ($optionsData as $optionData) {
            $optionValueMax = $this->getOptionValueMock($optionData['max_option_price']);
            $optionValueMin = $this->getOptionValueMock($optionData['min_option_price']);

            $optionItemMock = $this->createPartialMock(
                Option::class,
                ['getValues', 'getIsRequire', 'getId', 'getType']
            );
            $optionItemMock->method('getId')->willReturn($optionData['id']);
            $optionItemMock->method('getType')->willReturn($optionData['type']);
            $optionItemMock->method('getIsRequire')->willReturn($optionData['is_require']);
            $optionItemMock->method('getValues')->willReturn([$optionValueMax, $optionValueMin]);
            $options[] = $optionItemMock;
        }

        return $options;
    }

    /**
     * @param array $optionsData
     *
     * @return array
     */
    protected function setupSingleValueOptions(array $optionsData): array
    {
        $options = [];

        foreach ($optionsData as $optionData) {
            $optionItemMock = $this->createPartialMock(
                Option::class,
                [
                    'getValues',
                    'getIsRequire',
                    'getId',
                    'getType',
                    'getPriceType',
                    'getPrice'
                ]
            );
            $optionItemMock->method('getId')->willReturn($optionData['id']);
            $optionItemMock->method('getType')->willReturn($optionData['type']);
            $optionItemMock->method('getIsRequire')->willReturn($optionData['is_require']);
            $optionItemMock->method('getValues')->willReturn(null);
            $optionItemMock->method('getPriceType')->willReturn($optionData['price_type']);
            $optionItemMock->expects($this->any())
                ->method('getPrice')
                ->with($optionData['price_type'] == Value::TYPE_PERCENT)
                ->willReturn($optionData['price']);
            $options[] = $optionItemMock;
        }

        return $options;
    }

    /**
     * Test getValue().
     *
     * @return void
     */
    public function testGetValue(): void
    {
        $option1Id = 1;
        $option1MaxPrice = 100;
        $option1MinPrice = 10;
        $option1Type = 'select';

        $option2Id = 2;
        $option2MaxPrice = 200;
        $option2MinPrice = 20;
        $option2Type = ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX;

        $optionsData = [
            [
                'id' => $option1Id,
                'type' => $option1Type,
                'max_option_price' => $option1MaxPrice,
                'min_option_price' => $option1MinPrice,
                'is_require' => true
            ],
            [
                'id' => $option2Id,
                'type' => $option2Type,
                'max_option_price' => $option2MaxPrice,
                'min_option_price' => $option2MinPrice,
                'is_require' => false
            ]
        ];

        $singleValueOptionId = 3;
        $singleValueOptionPrice = '50';
        $singleValueOptionType = 'text';

        $singleValueOptions = $this->setupSingleValueOptions(
            [
                [
                    'id' => $singleValueOptionId,
                    'type' => $singleValueOptionType,
                    'price' => $singleValueOptionPrice,
                    'price_type' => 'fixed',
                    'is_require' => true
                ]
            ]
        );

        $options = $this->setupOptions($optionsData);
        $options[] = $singleValueOptions[0];
        $this->product->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        $expectedResult = [
            [
                'option_id' => $option1Id,
                'type' => $option1Type,
                'min' => $option1MinPrice,
                'max' => $option1MaxPrice
            ],
            [
                'option_id' => $option2Id,
                'type' => $option2Type,
                'min' => 0.,
                'max' => $option2MaxPrice + $option2MinPrice
            ],
            [
                'option_id' => $singleValueOptionId,
                'type' => $singleValueOptionType,
                'min' => $singleValueOptionPrice,
                'max' => $singleValueOptionPrice
            ]
        ];
        $result = $this->object->getValue();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testGetCustomOptionRange(): void
    {
        $option1Id = 1;
        $option1MaxPrice = 100;
        $option1MinPrice = 10;
        $option1Type = 'select';

        $option2Id = '2';
        $option2MaxPrice = 200;
        $option2MinPrice = 20;
        $option2Type = 'choice';

        $optionsData = [
            [
                'id' => $option1Id,
                'type' => $option1Type,
                'max_option_price' => $option1MaxPrice,
                'min_option_price' => $option1MinPrice,
                'is_require' => true
            ],
            [
                'id' => $option2Id,
                'type' => $option2Type,
                'max_option_price' => $option2MaxPrice,
                'min_option_price' => $option2MinPrice,
                'is_require' => false
            ]
        ];
        $options = $this->setupOptions($optionsData);

        $this->product->method('getOptions')->willReturn($options);

        $convertMinValue = $option1MinPrice / 2;
        $convertedMaxValue = ($option2MaxPrice + $option1MaxPrice) / 2;
        $optionMaxValue = $option2MaxPrice + $option1MaxPrice;
        $this->priceCurrencyMock
            ->method('convertAndRound')
            ->willReturnCallback(function ($arg1) use (
                $option1MinPrice,
                $convertMinValue,
                $optionMaxValue,
                $convertedMaxValue
            ) {
                if ($arg1 == $option1MinPrice) {
                    return $convertMinValue;
                } elseif ($arg1 == $optionMaxValue) {
                    return $convertedMaxValue;
                }
            });
        $this->assertEquals($option1MinPrice / 2, $this->object->getCustomOptionRange(true));
        $this->assertEquals($convertedMaxValue, $this->object->getCustomOptionRange(false));
    }

    /**
     * @param int $price
     * @param string $priceType
     *
     * @return MockObject
     */
    protected function getOptionValueMock($price, $priceType = 'fixed'): MockObject
    {
        $optionValueMock = $this->createPartialMock(
            Value::class,
            ['getPriceType', 'getPrice', 'getId', 'getOption', 'getData']
        );
        $optionValueMock->method('getPriceType')->willReturn($priceType);
        $optionValueMock->expects($this->any())
            ->method('getPrice')
            ->willReturn($price);

        $optionValueMock->expects($this->any())
            ->method('getData')
            ->with(Value::KEY_PRICE)
            ->willReturn($price);

        $optionMock = $this->createPartialMock(Option::class, ['getProduct']);

        $optionValueMock->method('getOption')->willReturn($optionMock);

        $optionMock->method('getProduct')->willReturn($this->product);

        $priceMock = $this->createPartialMockWithReflection(
            PriceInterface::class,
            ['getValue', 'setValue', 'getPriceCode', 'getAmount', 'getCustomAmount']
        );
        $priceValue = $price;
        $priceMock->method('setValue')->willReturnCallback(function ($value) use (&$priceValue) {
            $priceValue = $value;
        });
        $priceMock->method('getValue')->willReturnCallback(function () use (&$priceValue) {
            return $priceValue;
        });
        $priceMock->method('getPriceCode')->willReturn('base_price');
        $priceMock->method('getAmount')->willReturn($this->amount);
        $priceMock->method('getCustomAmount')->willReturn($price);

        $this->priceInfo->method('getPrice')->willReturn($priceMock);

        return $optionValueMock;
    }

    /**
     * Test getSelectedOptions().
     *
     * @return void
     */
    public function testGetSelectedOptions(): void
    {
        $optionId1 = 1;
        $optionId2 = 2;
        $optionValue = 10;
        $optionType = 'select';
        $optionValueMock = $this->createPartialMockWithReflection(
            DefaultType::class,
            ['getValue', 'setValue']
        );
        $optionValueData = $optionValue;
        $optionValueMock->method('setValue')->willReturnCallback(function ($value) use (&$optionValueData) {
            $optionValueData = $value;
        });
        $optionValueMock->method('getValue')->willReturnCallback(function () use (&$optionValueData) {
            return $optionValueData;
        });
        $groupMock = $this->createPartialMockWithReflection(
            Select::class,
            ['setOption', 'setConfigurationItemOption', 'getOptionPrice']
        );

        $groupMock->expects($this->once())
            ->method('setOption')
            ->with($this->callback(function ($arg) use ($optionId1) {
                return $arg->getId() === $optionId1;
            }))->willReturnSelf();
        
        $groupMock->expects($this->once())
            ->method('setConfigurationItemOption')
            ->with($optionValueMock)->willReturnSelf();
        
        $groupMock->expects($this->once())
            ->method('getOptionPrice')
            ->with($optionValue, 0.)
            ->willReturn($optionValue);
        
        $optionMock = $this->createPartialMock(Option::class, ['getId', 'getType', 'groupFactory']);
        $optionMock
            ->method('getId')
            ->willReturn($optionId1);
        $optionMock->expects($this->once())
            ->method('getType')
            ->willReturn($optionType);
        $optionMock->expects($this->once())
            ->method('groupFactory')
            ->with($optionType)
            ->willReturn($groupMock);
        
        $optionValueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($optionValue);
        
        $optionIds = new DataObject(['value' => '1,2']);

        $customOptions = ['option_ids' => $optionIds, 'option_1' => $optionValueMock, 'option_2' => null];
        $this->product->setCustomOptions($customOptions);
        $this->product
            ->method('getOptionById')
            ->willReturnCallback(function ($arg) use ($optionId1, $optionId2, $optionMock) {
                if ($arg == $optionId1) {
                    return $optionMock;
                } elseif ($arg == $optionId2) {
                    return null;
                }
            });
        // Return from cache
        $result = $this->object->getSelectedOptions();
        $this->assertEquals($optionValue, $result);
    }

    /**
     * Test getOptions().
     *
     * @return void
     */
    public function testGetOptions(): void
    {
        $price = 100;
        $displayValue = 120;
        $id = 1;
        $expected = [$id => [$price => ['base_amount' => $price, 'adjustment' => $displayValue]]];

        $this->amount->expects($this->once())
            ->method('getValue')
            ->willReturn(120);

        $this->calculator->expects($this->once())
            ->method('getAmount')
            ->willReturn($this->amount);

        $optionValueMock = $this->getOptionValueMock($price);
        $optionValueMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $optionItemMock = $this->createPartialMock(Option::class, ['getValues']);
        $optionItemMock->method('getValues')->willReturn([$optionValueMock]);
        $options = [$optionItemMock];
        $this->product->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);
        $result = $this->object->getOptions();
        $this->assertEquals($expected, $result);
        $result = $this->object->getOptions();
        $this->assertEquals($expected, $result);
    }
}
