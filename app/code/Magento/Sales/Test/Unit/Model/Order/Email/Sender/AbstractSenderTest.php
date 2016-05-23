<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

/**
 * Class AbstractSenderTest
 */
abstract class AbstractSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderMock;

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
    protected $paymentHelper;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRenderer;

    /**
     * Global configuration storage mock.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $globalConfig;

    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function stepMockSetup()
    {
        $this->senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );

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
            [
                'getStore', 'getBillingAddress', 'getPayment',
                '__wakeup', 'getCustomerIsGuest', 'getCustomerName',
                'getCustomerEmail', 'getShippingAddress', 'setSendEmail',
                'setEmailSent'
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
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->addressRenderer = $this->getMock('Magento\Sales\Model\Order\Address\Renderer', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Sales\Model\Order\Address', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);

        $this->paymentHelper = $this->getMock('\Magento\Payment\Helper\Data', ['getInfoBlockHtml'], [], '', false);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->globalConfig = $this->getMock(
            'Magento\Framework\App\Config',
            ['getValue'],
            [],
            '',
            false
        );

        $this->loggerMock = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );
    }

    public function stepAddressFormat($billingAddress, $isVirtual = false)
    {
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));
        if ($isVirtual) {
            $this->orderMock->expects($this->never())
                ->method('getShippingAddress');
        } else {
            $this->orderMock->expects($this->once())
                ->method('getShippingAddress')
                ->will($this->returnValue($billingAddress));
        }

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
