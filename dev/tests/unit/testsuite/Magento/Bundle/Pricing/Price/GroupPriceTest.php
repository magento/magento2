<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Pricing\Price;

class GroupPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\GroupPrice
     */
    protected $groupPrice;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \Magento\Catalog\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regularPrice;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up test case
     */
    public function setUp()
    {
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'getCustomerGroupId', 'getPriceInfo', 'getResource', 'getData'],
            [],
            '',
            false
        );
        $this->productResourceMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            [],
            [],
            '',
            false
        );
        $this->calculatorMock = $this->getMock(
            'Magento\Framework\Pricing\Adjustment\Calculator',
            [],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(
            'Magento\Customer\Model\Session',
            [],
            [],
            '',
            false
        );
        $this->customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            [],
            [],
            '',
            false
        );
        $this->attributeMock = $this->getMock(
            'Magento\Catalog\Model\Entity\Attribute',
            [],
            [],
            '',
            false
        );
        $this->backendMock = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice',
            [],
            [],
            '',
            false
        );
        $this->priceInfoMock = $this->getMock(
            'Magento\Framework\Pricing\PriceInfo\Base',
            ['getPrice'],
            [],
            '',
            false
        );
        $this->regularPrice = $this->getMock(
            'Magento\Catalog\Pricing\Price\RegularPrice',
            [],
            [],
            '',
            false
        );
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->groupPrice = new \Magento\Bundle\Pricing\Price\GroupPrice(
            $this->productMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->customerSessionMock
        );
    }

    public function testGetValue()
    {
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo('regular_price'))
            ->will($this->returnValue($this->regularPrice));
        $this->regularPrice->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(100));
        $this->productMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue(null));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue(3));
        $this->productMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($this->productResourceMock));
        $this->productResourceMock->expects($this->once())
            ->method('getAttribute')
            ->with($this->equalTo('group_price'))
            ->will($this->returnValue($this->attributeMock));
        $this->attributeMock->expects($this->once())
            ->method('getBackend')
            ->will($this->returnValue($this->backendMock));
        $this->backendMock->expects($this->once())
            ->method('afterLoad')
            ->with($this->equalTo($this->productMock))
            ->will($this->returnValue($this->backendMock));
        $this->priceCurrencyMock->expects($this->never())
            ->method('convertAndRound');
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with(
                $this->equalTo('group_price'),
                $this->equalTo(null)
            )
            ->will(
                $this->returnValue(
                    [
                        [
                            'cust_group' => 3,
                            'website_price' => 80,
                        ],
                    ]

                )
            );
        $this->assertEquals(20, $this->groupPrice->getValue());
        $this->assertEquals(20, $this->groupPrice->getValue());
    }

    public function testGetValueNotGroupPrice()
    {
        $this->productMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue(null));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue(3));
        $this->productMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($this->productResourceMock));
        $this->productResourceMock->expects($this->once())
            ->method('getAttribute')
            ->with($this->equalTo('group_price'))
            ->will($this->returnValue(null));

        $this->assertFalse($this->groupPrice->getValue());
    }
}
