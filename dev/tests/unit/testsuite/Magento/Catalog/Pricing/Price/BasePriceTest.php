<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Pricing\Price;

/**
 * Base price test
 */
class BasePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $basePrice;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regularPriceMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\GroupPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupPriceMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\SpecialPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $specialPriceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $prices;

    /**
     * Set up
     */
    public function setUp()
    {
        $qty = 1;
        $this->saleableItemMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->regularPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\RegularPrice', [], [], '', false);
        $this->groupPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\GroupPrice', [], [], '', false);
        $this->specialPriceMock = $this->getMock('Magento\Catalog\Pricing\Price\SpecialPrice', [], [], '', false);
        $this->calculatorMock = $this->getMock('Magento\Framework\Pricing\Adjustment\Calculator', [], [], '', false);

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->prices = [
            'regular_price' => $this->regularPriceMock,
            'group_price' => $this->groupPriceMock,
            'special_price' => $this->specialPriceMock,
        ];

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->basePrice = $helper->getObject('Magento\Catalog\Pricing\Price\BasePrice',
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => $qty,
                'calculator' => $this->calculatorMock
            ]
        );
    }

    /**
     * test method getValue
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($specialPriceValue, $expectedResult)
    {
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue($this->prices));
        $this->regularPriceMock->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValue(100));
        $this->groupPriceMock->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue(99));
        $this->specialPriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($specialPriceValue));
        $this->assertSame($expectedResult, $this->basePrice->getValue());
    }

    public function getValueDataProvider()
    {
        return [[77, 77], [0, 0], [false, 99]];
    }
}
