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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class OrderGetTest
 */
class OrderMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Data\OrderMapper
     */
    protected $orderMapper;
    /**
     * @var \Magento\Sales\Service\V1\Data\OrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderBuilderMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\OrderItemMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMapperMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\OrderPaymentMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentMapperMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\OrderAddressMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressMapperMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentMock;
    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->orderBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderBuilder',
            ['populateWithArray', 'setItems', 'setPayments', 'setBillingAddress', 'setShippingAddress', 'create'],
            [],
            '',
            false
        );
        $this->orderItemMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderItemMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->orderPaymentMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderPaymentMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->orderAddressMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderAddressMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->orderItemMock = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            [],
            [],
            '',
            false
        );
        $this->orderPaymentMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            [],
            [],
            '',
            false
        );
        $this->orderAddressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            [],
            [],
            '',
            false
        );
        $this->orderMapper = new \Magento\Sales\Service\V1\Data\OrderMapper(
            $this->orderBuilderMock,
            $this->orderItemMapperMock,
            $this->orderPaymentMapperMock,
            $this->orderAddressMapperMock
        );
    }

    /**
     * test order mapper
     */
    public function testInvoke()
    {
        $this->orderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(['field-1' => 'value-1']));
        $this->orderMock->expects($this->once())
            ->method('getItemsCollection')
            ->will($this->returnValue([$this->orderItemMock]));
        $this->orderMock->expects($this->once())
            ->method('getPaymentsCollection')
            ->will($this->returnValue([$this->orderPaymentMock]));
        $this->orderMock->expects($this->exactly(2))
            ->method('getBillingAddress')
            ->will($this->returnValue($this->orderAddressMock));
        $this->orderMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->will($this->returnValue($this->orderAddressMock));
        $this->orderBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($this->equalTo(['field-1' => 'value-1']))
            ->will($this->returnSelf());
        $this->orderItemMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->orderItemMock))
            ->will($this->returnValue('item-1'));
        $this->orderPaymentMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->orderPaymentMock))
            ->will($this->returnValue('payment-1'));
        $this->orderAddressMapperMock->expects($this->exactly(2))
            ->method('extractDto')
            ->with($this->equalTo($this->orderAddressMock))
            ->will($this->returnValue('address-1'));
        $this->orderBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo(['item-1']))
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setPayments')
            ->with($this->equalTo(['payment-1']))
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($this->equalTo('address-1'))
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->equalTo('address-1'))
            ->will($this->returnSelf());
        $this->orderBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('data-object-with-order'));
        $this->assertEquals('data-object-with-order', $this->orderMapper->extractDto($this->orderMock));
    }
}
