<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Pricing\Price;

use \Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Model\Product\Option;

use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Catalog\Model\Product\Option\Value;

/**
 * Class OptionPriceTest
 */
class CustomOptionPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\CustomOptionPrice
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getOptionById', '__wakeup', 'getPriceInfo', 'getOptions'],
            [],
            '',
            false
        );

        $this->priceInfo = $this->getMock(
            'Magento\Framework\Pricing\PriceInfo\Base',
            [],
            [],
            '',
            false
        );

        $this->product->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $this->calculator = $this->getMock(
            'Magento\Framework\Pricing\Adjustment\Calculator',
            [],
            [],
            '',
            false
        );

        $this->amount = $this->getMock(
            'Magento\Framework\Pricing\Amount\Base',
            [],
            [],
            '',
            false
        );

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->object = new CustomOptionPrice(
            $this->product,
            PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT,
            $this->calculator,
            $this->priceCurrencyMock
        );
    }

    protected function setupOptions(array $optionsData)
    {
        $options = [];
        foreach ($optionsData as $optionData) {
            $optionValueMax = $this->getOptionValueMock($optionData['max_option_price']);
            $optionValueMin = $this->getOptionValueMock($optionData['min_option_price']);

            $optionItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
                ->disableOriginalConstructor()
                ->setMethods(['getValues', '__wakeup', 'getIsRequire', 'getId', 'getType'])
                ->getMock();
            $optionItemMock->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($optionData['id']));
            $optionItemMock->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($optionData['type']));
            $optionItemMock->expects($this->any())
                ->method('getIsRequire')
                ->will($this->returnValue($optionData['is_require']));
            $optionItemMock->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue([$optionValueMax, $optionValueMin]));
            $options[] = $optionItemMock;
        }
        return $options;
    }

    protected function setupSingleValueOptions($optionsData)
    {
        $options = [];
        foreach ($optionsData as $optionData) {
            $optionItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
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
                ->will($this->returnValue($optionData['id']));
            $optionItemMock->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($optionData['type']));
            $optionItemMock->expects($this->any())
                ->method('getIsRequire')
                ->will($this->returnValue($optionData['is_require']));
            $optionItemMock->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue(null));
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
        $option2Type = Option::OPTION_TYPE_CHECKBOX;

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
            ->will($this->returnValue($options));

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
            ->will($this->returnValue($options));

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOptionValueMock($price)
    {
        $optionValueMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option\Value')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceType', 'getPrice', 'getId', '__wakeup'])
            ->getMock();
        $optionValueMock->expects($this->any())
            ->method('getPriceType')
            ->will($this->returnValue('percent'));
        $optionValueMock->expects($this->any())
            ->method('getPrice')
            ->with($this->equalTo(true))
            ->will($this->returnValue($price));
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
        $optionValueMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option\DefaultType')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $optionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getType', 'groupFactory', '__wakeup'])
            ->getMock();
        $groupMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option\Type\Select')
            ->disableOriginalConstructor()
            ->setMethods(['setOption', 'setConfigurationItemOption', 'getOptionPrice'])
            ->getMock();

        $groupMock->expects($this->once())
            ->method('setOption')
            ->with($this->equalTo($optionMock))
            ->will($this->returnSelf());
        $groupMock->expects($this->once())
            ->method('setConfigurationItemOption')
            ->with($this->equalTo($optionValueMock))
            ->will($this->returnSelf());
        $groupMock->expects($this->once())
            ->method('getOptionPrice')
            ->with($this->equalTo($optionValue), $this->equalTo(0.))
            ->will($this->returnValue($optionValue));
        $optionMock->expects($this->at(0))
            ->method('getId')
            ->will($this->returnValue($optionId1));
        $optionMock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($optionType));
        $optionMock->expects($this->once())
            ->method('groupFactory')
            ->with($this->equalTo($optionType))
            ->will($this->returnValue($groupMock));
        $optionValueMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($optionValue));
        $optionIds = new \Magento\Framework\DataObject(['value' => '1,2']);

        $customOptions = ['option_ids' => $optionIds, 'option_1' => $optionValueMock, 'option_2' => null];
        $this->product->setCustomOptions($customOptions);
        $this->product->expects($this->at(0))
            ->method('getOptionById')
            ->with($this->equalTo($optionId1))
            ->will($this->returnValue($optionMock));
        $this->product->expects($this->at(1))
            ->method('getOptionById')
            ->with($this->equalTo($optionId2))
            ->will($this->returnValue(null));

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
            ->will($this->returnValue(120));

        $this->calculator->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue($this->amount));

        $optionValueMock = $this->getOptionValueMock($price);
        $optionValueMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $optionItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getValues', '__wakeup'])
            ->getMock();
        $optionItemMock->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue([$optionValueMock]));
        $options = [$optionItemMock];
        $this->product->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));
        $result = $this->object->getOptions();
        $this->assertEquals($expected, $result);
        $result = $this->object->getOptions();
        $this->assertEquals($expected, $result);
    }
}
