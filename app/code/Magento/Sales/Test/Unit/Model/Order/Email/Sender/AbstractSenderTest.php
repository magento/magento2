<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

/**
 * Class AbstractSenderTest
 */
abstract class AbstractSenderTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\Sales\Model\Order\Address\Renderer | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRendererMock;

    /**
     * @var \Magento\Sales\Model\Order\Address | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    public function stepMockSetup()
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

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );

        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));


        $this->addressRendererMock = $this->getMock('Magento\Sales\Model\Order\Address\Renderer', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Sales\Model\Order\Address', [], [], '', false);
        $this->addressRendererMock->expects($this->any())->method('format')->willReturn(1);
    }

    public function stepAddressFormat($billingAddress)
    {
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));
        $this->orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($billingAddress));
    }

    public function stepSendWithoutSendCopy()
    {
        $this->stepSend($this->once(), $this->never());
    }

    public function stepSendWithCallSendCopyTo()
    {
        $this->stepSend($this->never(), $this->once());
    }

    public function stepIdentityContainerInit($identityMockClassName)
    {
        $this->identityContainerMock = $this->getMock(
            $identityMockClassName,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
    }

    protected function stepSend(
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $sendExpects,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $sendCopyToExpects
    ) {
        $senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );
        $senderMock->expects($sendExpects)
            ->method('send');
        $senderMock->expects($sendCopyToExpects)
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));
    }
}
