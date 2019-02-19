<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

/**
 * Class AbstractSenderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractSenderTest extends \PHPUnit\Framework\TestCase
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
        $this->senderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Sender::class,
            ['send', 'sendCopyTo']
        );

        $this->senderBuilderFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\SenderBuilderFactory::class,
            ['create']
        );
        $this->templateContainerMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Container\Template::class,
            ['setTemplateVars']
        );

        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId', '__wakeup']);

        $this->orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [
                'getStore', 'getBillingAddress', 'getPayment',
                '__wakeup', 'getCustomerIsGuest', 'getCustomerName',
                'getCustomerEmail', 'getShippingAddress', 'setSendEmail',
                'setEmailSent'
            ]);
        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $paymentInfoMock = $this->createMock(\Magento\Payment\Model\Info::class);
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentInfoMock));

        $this->addressRenderer = $this->createMock(\Magento\Sales\Model\Order\Address\Renderer::class);
        $this->addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);

        $this->paymentHelper = $this->createPartialMock(\Magento\Payment\Helper\Data::class, ['getInfoBlockHtml']);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->will($this->returnValue('payment'));

        $this->globalConfig = $this->createPartialMock(\Magento\Framework\App\Config::class, ['getValue']);

        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
    }

    /**
     * @param $billingAddress
     * @param bool $isVirtual
     */
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

    /**
     * @param $identityMockClassName
     */
    public function stepIdentityContainerInit($identityMockClassName)
    {
        $this->identityContainerMock = $this->createPartialMock(
            $identityMockClassName,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $sendExpects
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $sendCopyToExpects
     */
    protected function stepSend(
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $sendExpects,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $sendCopyToExpects
    ) {
        $senderMock = $this->createPartialMock(\Magento\Sales\Model\Order\Email\Sender::class, ['send', 'sendCopyTo']);
        $senderMock->expects($sendExpects)
            ->method('send');
        $senderMock->expects($sendCopyToExpects)
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($senderMock));
    }
}
