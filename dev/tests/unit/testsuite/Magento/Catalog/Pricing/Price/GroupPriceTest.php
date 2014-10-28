<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Groupprice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendMock;

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
            'Magento\Catalog\Model\Product\Attribute\Backend\Groupprice',
            [],
            [],
            '',
            false
        );

        $this->groupPrice = new \Magento\Catalog\Pricing\Price\GroupPrice(
            $this->productMock,
            1,
            $this->calculatorMock,
            $this->customerSessionMock
        );
    }

    /**
     * test get group price, customer group in session
     */
    public function testGroupPriceCustomerGroupInSession()
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
                        'website_price' => 80
                    ]
                ]

            ));
        $this->assertEquals(80, $this->groupPrice->getValue());
    }

    /**
     * test get group price, customer group in session
     */
    public function testGroupPriceCustomerGroupInProduct()
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
                        'website_price' => 80
                    ]
                ]

            ));
        $this->assertEquals(80, $this->groupPrice->getValue());
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
