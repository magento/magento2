<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

/**
 * Class CustomOptionTest
 */
class AttributePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModifier;

    /**
     * @var \Magento\Framework\Pricing\Amount\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\AttributePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regularPriceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute|
     * \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * Test setUp
     */
    protected function setUp()
    {
        $qty = 1;
        $this->saleableItemMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getTypeInstance',
                'setParentId',
                'hasPreconfiguredValues',
                'getPreconfiguredValues',
                '__wakeup',
                'getPriceInfo'
            ],
            [],
            '',
            false
        );
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->amountMock = $this->getMock('Magento\Framework\Pricing\Amount\Base', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);
        $this->regularPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\RegularPrice', [], [], '', false);
        $this->priceModifier = $this->getMock(
            'Magento\Catalog\Model\Product\PriceModifierInterface',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', ['getStore'], [], '', false);
        $this->attributeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $this->attribute = new AttributePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculatorMock,
            $this->priceCurrency,
            $this->priceModifier,
            $this->storeManagerMock
        );
    }

    public function testInstanceOf()
    {
        $qty = 100;
        $object = new AttributePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculatorMock,
            $this->priceCurrency,
            $this->priceModifier,
            $this->storeManagerMock
        );
        $this->assertInstanceOf('Magento\ConfigurableProduct\Pricing\Price\AttributePrice', $object);
    }

    public function testPrepareJsonAttributes()
    {
        $options = [];
        $attributeId = 1;
        $attributeCode = 'test_attribute';
        $attributeLabel = 'Test';
        $pricingValue = 100;
        $amount = 120;
        $modifiedValue = 140;
        $valueIndex = 2;
        $optionId = 1;

        $expected = [
            'priceOptions' => [
                    $attributeId => [
                            'id' => $attributeId,
                            'code' => $attributeCode,
                            'label' => $attributeLabel,
                            'options' => [
                                    0 => [
                                            'id' => $valueIndex,
                                            'label' => $attributeLabel,
                                            'prices' => [
                                                'oldPrice' => [
                                                    'amount' => $modifiedValue,
                                                ],
                                                'basePrice' => [
                                                    'amount' => $pricingValue,
                                                ],
                                                'finalPrice' => [
                                                    'amount' => $modifiedValue,
                                                ],
                                            ],
                                            'products' => [],
                                        ],
                                ],
                        ],
                ],
            'defaultValues' => [
                    $attributeId => $optionId,
                ],
        ];
        $attributePrices = [
            [
                'is_percent' => false,
                'pricing_value' => $pricingValue,
                'value_index' => $valueIndex,
                'label' => $attributeLabel,
            ],
        ];

        $configurableAttributes = [
            $this->getAttributeMock($attributeId, $attributeCode, $attributeLabel, $attributePrices),
        ];
        $configuredValueMock = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();
        $configuredValueMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($optionId));
        $configurableProduct = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->getMock();
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($configurableAttributes));
        $this->saleableItemMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($configurableProduct));
        $this->saleableItemMock->expects($this->any())
            ->method('setParentId');
        $this->saleableItemMock->expects($this->any())
            ->method('hasPreconfiguredValues')
            ->will($this->returnValue(true));
        $this->saleableItemMock->expects($this->any())
            ->method('getPreconfiguredValues')
            ->will($this->returnValue($configuredValueMock));

        $this->priceModifier->expects($this->once())
            ->method('modifyPrice')
            ->with($this->equalTo($pricingValue), $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($amount));
        $this->calculatorMock->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($this->getModifiedAmountMock($modifiedValue, $pricingValue)));
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        // don't do any actual conversions; just return whatever was passed in
        $this->priceCurrency->expects($this->any())
            ->method('convertAndRound')
            ->will($this->returnArgument(0));

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $result = $this->attribute->prepareAttributes($options);
        $this->assertEquals($expected, $result);
    }

    protected function getModifiedAmountMock($modifiedValue, $pricingValue)
    {
        $modifiedAmountMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $modifiedAmountMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($modifiedValue));
        $modifiedAmountMock->expects($this->once())
            ->method('getBaseAmount')
            ->will($this->returnValue($pricingValue));

        return $modifiedAmountMock;
    }

    protected function getAttributeMock($attributeId, $attributeCode, $attributeLabel, $attributePrices)
    {
        $productAttributeMock = $this->getMockBuilder('Magento\Catalog\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', '__wakeup', 'getAttributeCode', 'getId', 'getAttributeLabel'])
            ->getMock();
        $productAttributeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($attributeId));
        $productAttributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $attributeMock = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', '__wakeup', 'getLabel', 'getPrices'])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getProductAttribute')
            ->will($this->returnValue($productAttributeMock));
        $attributeMock->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($attributeLabel));
        $attributeMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue($attributePrices));

        return $attributeMock;
    }

    /**
     * test method testGetOptionValueModified with option is_percent = true
     */
    public function testGetOptionValueModifiedIsPercent()
    {
        $finalPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\RegularPrice', [], [], '', false);
        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->saleableItemMock->expects($this->once())
            ->method('setParentId')
            ->with($this->equalTo(true))
            ->will($this->returnValue($this->returnSelf()));
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE))
            ->will($this->returnValue($finalPriceMock));
        $finalPriceMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(50));
        $this->priceModifier->expects($this->once())
            ->method('modifyPrice')
            ->with($this->equalTo(50), $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue(55));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with(
                $this->equalTo(55),
                $this->equalTo($this->saleableItemMock),
                null,
                [\Magento\Catalog\Pricing\Price\CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true]
            )
            ->will($this->returnValue(57.55));
        $this->assertEquals(
            57.55,
            $this->attribute->getOptionValueModified(
                [
                    'is_percent' => true,
                    'pricing_value' => 100,
                ]
            )
        );
    }

    /**
     * test method testGetOptionValueModified with option is_percent = false
     */
    public function testGetOptionValueModifiedIsNotPercent()
    {
        $this->saleableItemMock->expects($this->once())
            ->method('setParentId')
            ->with($this->equalTo(true))
            ->will($this->returnValue($this->returnSelf()));
        $this->priceModifier->expects($this->once())
            ->method('modifyPrice')
            ->with($this->equalTo(77.33), $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue(77.67));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with(
                $this->equalTo(77.67),
                $this->equalTo($this->saleableItemMock),
                null,
                [\Magento\Catalog\Pricing\Price\CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true]
            )
            ->will($this->returnValue(80.99));
        $this->priceCurrency->expects($this->once())
            ->method('convertAndRound')
            ->will($this->returnArgument(0));
        $this->assertEquals(
            80.99,
            $this->attribute->getOptionValueModified(
                [
                    'is_percent' => false,
                    'pricing_value' => 77.33,
                ]
            )
        );
    }
}
