<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Pricing\Price;

/**
 * Group price test
 */
class GroupPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Price\GroupPrice
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

        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $this->groupPrice = new \Magento\Catalog\Pricing\Price\GroupPrice(
            $this->productMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->customerSessionMock
        );
    }

    /**
     * test get group price, customer group in session
     */
    public function testGroupPriceCustomerGroupInSession()
    {
        $groupPrice = 80;
        $convertedValue = 56.24;
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
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with(
                $this->equalTo('group_price'),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                [
                    [
                        'cust_group' => 3,
                        'website_price' => $groupPrice,
                    ],
                ]

            ));
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($groupPrice)
            ->will($this->returnValue($convertedValue));
        $this->assertEquals($convertedValue, $this->groupPrice->getValue());
    }

    /**
     * test get group price, customer group in session
     */
    public function testGroupPriceCustomerGroupInProduct()
    {
        $groupPrice = 80;
        $convertedPrice = 56.23;
        $this->productMock->expects($this->exactly(2))
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
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with(
                $this->equalTo('group_price'),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                [
                    [
                        'cust_group' => 3,
                        'website_price' => $groupPrice,
                    ],
                ]

            ));
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($groupPrice)
            ->will($this->returnValue($convertedPrice));
        $this->assertEquals($convertedPrice, $this->groupPrice->getValue());
    }

    /**
     * test get group price, attribut is noy srt
     */
    public function testGroupPriceAttributeIsNotSet()
    {
        $this->productMock->expects($this->exactly(2))
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
