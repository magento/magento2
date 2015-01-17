<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\PriceInfoInterface;

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

    /**
     * Test getValue()
     */
    public function testGetValue()
    {
        $price = 100;
        $minPrice = 10;
        $optionId = 1;
        $type = 'select';

        $optionValueMax = $this->getOptionValueMock($price);
        $optionValueMin = $this->getOptionValueMock($minPrice);

        $optionItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getValues', '__wakeup', 'getIsRequire', 'getId', 'getType'])
            ->getMock();
        $optionItemMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($optionId));
        $optionItemMock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($type));
        $optionItemMock->expects($this->once())
            ->method('getIsRequire')
            ->will($this->returnValue(true));
        $optionItemMock->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue([$optionValueMax, $optionValueMin]));
        $options = [$optionItemMock];
        $this->product->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $result = $this->object->getValue();
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('option_id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('min', $result[0]);
        $this->assertEquals($minPrice, $result[0]['min']);
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
        $optionValueMock->expects($this->once())
            ->method('getPriceType')
            ->will($this->returnValue('percent'));
        $optionValueMock->expects($this->once())
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
        $optionIds = new \Magento\Framework\Object(['value' => '1,2']);

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
