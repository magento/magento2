<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product\Option\Value;

use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class OptionPriceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomOptionPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\CustomOptionPrice
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getOptionById', '__wakeup', 'getPriceInfo', 'getOptions']
        );

        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);

        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $this->calculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);

        $this->amount = $this->createMock(\Magento\Framework\Pricing\Amount\Base::class);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->object = new CustomOptionPrice(
            $this->product,
            PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT,
            $this->calculator,
            $this->priceCurrencyMock
        );
    }

    /**
     * @param array $optionsData
     * @return array
     */
    protected function setupOptions(array $optionsData)
    {
        $options = [];
        foreach ($optionsData as $optionData) {
            $optionValueMax = $this->getOptionValueMock($optionData['max_option_price']);
            $optionValueMin = $this->getOptionValueMock($optionData['min_option_price']);

            $optionItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
                ->disableOriginalConstructor()
                ->setMethods(['getValues', '__wakeup', 'getIsRequire', 'getId', 'getType'])
                ->getMock();
            $optionItemMock->expects($this->any())
                ->method('getId')
                ->willReturn($optionData['id']);
            $optionItemMock->expects($this->any())
                ->method('getType')
                ->willReturn($optionData['type']);
            $optionItemMock->expects($this->any())
                ->method('getIsRequire')
                ->willReturn($optionData['is_require']);
            $optionItemMock->expects($this->any())
                ->method('getValues')
                ->willReturn([$optionValueMax, $optionValueMin]);
            $options[] = $optionItemMock;
        }
        return $options;
    }

    /**
     * @param $optionsData
     * @return array
     */
    protected function setupSingleValueOptions($optionsData)
    {
        $options = [];
        foreach ($optionsData as $optionData) {
            $optionItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'getValues',
                    '__wakeup',
                    'getIsRequire',
                    'getId',
                    'getType',
                    'getPriceType',
                    'getPrice',
                ])
                ->getMock();
            $optionItemMock->expects($this->any())
                ->method('getId')
                ->willReturn($optionData['id']);
            $optionItemMock->expects($this->any())
                ->method('getType')
                ->willReturn($optionData['type']);
            $optionItemMock->expects($this->any())
                ->method('getIsRequire')
                ->willReturn($optionData['is_require']);
            $optionItemMock->expects($this->any())
                ->method('getValues')
                ->willReturn(null);
            $optionItemMock->expects($this->any())
                ->method('getPriceType')
                ->willReturn($optionData['price_type']);
            $optionItemMock->expects($this->any())
                ->method('getPrice')
                ->with($optionData['price_type'] == Value::TYPE_PERCENT)
                ->willReturn($optionData['price']);
            $options[] = $optionItemMock;
        }
        return $options;
    }

    /**
     * Test getValue()
     */
    public function testGetValue()
    {
        $option1Id = 1;
        $option1MaxPrice = 100;
        $option1MinPrice = 10;
        $option1Type = 'select';

        $option2Id = 2;
        $option2MaxPrice = 200;
        $option2MinPrice = 20;
        $option2Type = \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX;

        $optionsData = [
            [
                'id' => $option1Id,
                'type' => $option1Type,
                'max_option_price' => $option1MaxPrice,
                'min_option_price' => $option1MinPrice,
                'is_require' => true,
            ],
            [
                'id' => $option2Id,
                'type' => $option2Type,
                'max_option_price' => $option2MaxPrice,
                'min_option_price' => $option2MinPrice,
                'is_require' => false,
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
                    'is_require' => true,
                ],
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
                'max' => $option1MaxPrice,
            ],
            [
                'option_id' => $option2Id,
                'type' => $option2Type,
                'min' => 0.,
                'max' => $option2MaxPrice + $option2MinPrice,
            ],
            [
                'option_id' => $singleValueOptionId,
                'type' => $singleValueOptionType,
                'min' => $singleValueOptionPrice,
                'max' => $singleValueOptionPrice,
            ]
        ];
        $result = $this->object->getValue();
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetCustomOptionRange()
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
                'is_require' => true,
            ],
            [
                'id' => $option2Id,
                'type' => $option2Type,
                'max_option_price' => $option2MaxPrice,
                'min_option_price' => $option2MinPrice,
                'is_require' => false,
            ]
        ];
        $options = $this->setupOptions($optionsData);

        $this->product->expects($this->any())
            ->method('getOptions')
            ->willReturn($options);

        $convertMinValue = $option1MinPrice / 2;
        $convertedMaxValue = ($option2MaxPrice + $option1MaxPrice) / 2;
        $this->priceCurrencyMock->expects($this->at(0))
            ->method('convertAndRound')
            ->with($option1MinPrice)
            ->willReturn($convertMinValue);
        $this->priceCurrencyMock->expects($this->at(1))
            ->method('convertAndRound')
            ->with($option2MaxPrice + $option1MaxPrice)
            ->willReturn($convertedMaxValue);
        $this->assertEquals($option1MinPrice / 2, $this->object->getCustomOptionRange(true));
        $this->assertEquals($convertedMaxValue, $this->object->getCustomOptionRange(false));
    }

    /**
     * @param int $price
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getOptionValueMock($price)
    {
        $optionValueMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceType', 'getPrice', 'getId', '__wakeup', 'getOption', 'getData'])
            ->getMock();
        $optionValueMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn('percent');
        $optionValueMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(true))
            ->willReturn($price);

        $optionValueMock->expects($this->any())
            ->method('getData')
            ->with(\Magento\Catalog\Model\Product\Option\Value::KEY_PRICE)
            ->willReturn($price);

        $optionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $optionValueMock->expects($this->any())->method('getOption')->willReturn($optionMock);

        $optionMock->expects($this->any())->method('getProduct')->willReturn($this->product);

        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $priceMock->method('getValue')->willReturn($price);

        $this->priceInfo->method('getPrice')->willReturn($priceMock);

        return $optionValueMock;
    }

    /**
     * Test getSelectedOptions()
     */
    public function testGetSelectedOptions()
    {
        $optionId1 = 1;
        $optionId2 = 2;
        $optionValue = 10;
        $optionType = 'select';
        $optionValueMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\DefaultType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getType', 'groupFactory', '__wakeup'])
            ->getMock();
        $groupMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOption', 'setConfigurationItemOption', 'getOptionPrice'])
            ->getMock();

        $groupMock->expects($this->once())
            ->method('setOption')
            ->with($this->equalTo($optionMock))
            ->willReturnSelf();
        $groupMock->expects($this->once())
            ->method('setConfigurationItemOption')
            ->with($this->equalTo($optionValueMock))
            ->willReturnSelf();
        $groupMock->expects($this->once())
            ->method('getOptionPrice')
            ->with($this->equalTo($optionValue), $this->equalTo(0.))
            ->willReturn($optionValue);
        $optionMock->expects($this->at(0))
            ->method('getId')
            ->willReturn($optionId1);
        $optionMock->expects($this->once())
            ->method('getType')
            ->willReturn($optionType);
        $optionMock->expects($this->once())
            ->method('groupFactory')
            ->with($this->equalTo($optionType))
            ->willReturn($groupMock);
        $optionValueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($optionValue);
        $optionIds = new \Magento\Framework\DataObject(['value' => '1,2']);

        $customOptions = ['option_ids' => $optionIds, 'option_1' => $optionValueMock, 'option_2' => null];
        $this->product->setCustomOptions($customOptions);
        $this->product->expects($this->at(0))
            ->method('getOptionById')
            ->with($this->equalTo($optionId1))
            ->willReturn($optionMock);
        $this->product->expects($this->at(1))
            ->method('getOptionById')
            ->with($this->equalTo($optionId2))
            ->willReturn(null);

        // Return from cache
        $result = $this->object->getSelectedOptions();
        $this->equalTo($optionValue, $result);
    }

    /**
     * Test getOptions()
     */
    public function testGetOptions()
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
        $optionItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValues', '__wakeup'])
            ->getMock();
        $optionItemMock->expects($this->any())
            ->method('getValues')
            ->willReturn([$optionValueMock]);
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
