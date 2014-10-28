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
namespace Magento\Sales\Model\Order\Email\Sender;

class ShipmentSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderBuilderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $identityContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentResource;

    protected function setUp()
    {
        $this->senderBuilderFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\SenderBuilderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->templateContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\Template',
            ['setTemplateVars'],
            [],
            '',
            false
        );
        $this->paymentHelper = $this->getMock('\Magento\Payment\Helper\Data', ['getInfoBlockHtml'], [], '', false);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->shipmentResource = $this->getMock(
            '\Magento\Sales\Model\Resource\Order\Shipment',
            [],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\ShipmentIdentity',
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [
                'getStore', 'getBillingAddress', 'getPayment',
                '__wakeup', 'getCustomerIsGuest', 'getCustomerName',
                'getCustomerEmail'
            ],
            [],
            '',
            false
        );

        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $paymentInfoMock = $this->getMock(
            '\Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->shipmentMock = $this->getMock(
            '\Magento\Sales\Model\Order\Shipment',
            ['getStore', '__wakeup', 'getOrder'],
            [],
            '',
            false
        );
        $this->shipmentMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->shipmentMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->sender = new ShipmentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->paymentHelper,
            $this->shipmentResource
        );
    }

    public function testSendFalse()
    {
        $result = $this->sender->send($this->shipmentMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = 'billing_address';
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'shipment' => $this->shipmentMock,
                        'comment' => $comment,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'store' => $this->storeMock
                    ]
                )
            );
        $paymentInfoMock = $this->getMock(
            '\Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));


        $senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );
        $senderMock->expects($this->once())
            ->method('send');
        $senderMock->expects($this->never())
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));

        $result = $this->sender->send($this->shipmentMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = 'billing_address';
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));

        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'shipment' => $this->shipmentMock,
                        'billing' => $billingAddress,
                        'payment_html' => 'payment',
                        'comment' => $comment,
                        'store' => $this->storeMock
                    ]
                )
            );
        $senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );
        $senderMock->expects($this->never())
            ->method('send');
        $senderMock->expects($this->once())
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));

        $result = $this->sender->send($this->shipmentMock, false, $comment);
        $this->assertTrue($result);
    }
}
