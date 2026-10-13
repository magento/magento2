<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Info;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractSenderTestCase extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Sender|MockObject
     */
    protected $senderMock;

    /**
     * @var MockObject
     */
    protected $senderBuilderFactoryMock;

    /**
     * @var MockObject
     */
    protected $templateContainerMock;

    /**
     * @var MockObject
     */
    protected $identityContainerMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $orderMock;

    /**
     * @var MockObject
     */
    protected $paymentHelper;

    /**
     * @var Renderer|MockObject
     */
    protected $addressRenderer;

    /**
     * Global configuration storage mock.
     *
     * @var ScopeConfigInterface|MockObject
     */
    protected $globalConfig;

    /**
     * @var Address|MockObject
     */
    protected $addressMock;

    /**
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $loggerMock;

    /**
     * @var Emulation|MockObject
     */
    protected $appEmulator;

    public function stepMockSetup()
    {
        $this->senderMock = $this->createPartialMockWithReflection(
            Sender::class,
            ['send', 'sendCopyTo']
        );

        $this->senderBuilderFactoryMock = $this->createPartialMock(
            SenderBuilderFactory::class,
            ['create']
        );
        $this->templateContainerMock = $this->createPartialMock(
            Template::class,
            ['setTemplateVars']
        );

        $this->storeMock = $this->createPartialMockWithReflection(Store::class, ['getStoreId']);

        $this->orderMock = $this->createPartialMockWithReflection(
            Order::class,
            [
                'setSendEmail', 'getId', 'getStore', 'getBillingAddress', 'getPayment',
                'getCustomerIsGuest', 'getCustomerName', 'getCustomerEmail', 'getShippingAddress',
                'setEmailSent', 'getCreatedAtFormatted', 'getIsNotVirtual', 'getEmailCustomerNote',
                'getFrontendStatusLabel'
            ]
        );
        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $paymentInfoMock = $this->createMock(Info::class);
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentInfoMock);

        $this->addressRenderer = $this->createMock(Renderer::class);
        $this->addressMock = $this->createMock(Address::class);
        $this->eventManagerMock = $this->createMock(Manager::class);

        $this->paymentHelper = $this->createPartialMock(Data::class, ['getInfoBlockHtml']);
        $this->paymentHelper->expects($this->any())
            ->method('getInfoBlockHtml')
            ->willReturn('payment');

        $this->globalConfig = $this->createPartialMock(Config::class, ['getValue']);

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->appEmulator = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['startEnvironmentEmulation', 'stopEnvironmentEmulation'])
            ->getMock();
    }

    /**
     * @param $billingAddress
     * @param bool $isVirtual
     */
    public function stepAddressFormat($billingAddress, $isVirtual = false)
    {
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        if ($isVirtual) {
            $this->orderMock->expects($this->never())
                ->method('getShippingAddress');
        } else {
            $this->orderMock->expects($this->once())
                ->method('getShippingAddress')
                ->willReturn($billingAddress);
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
        $this->identityContainerMock = $this->getMockBuilder($identityMockClassName)
            ->disableOriginalConstructor()
            ->onlyMethods(
                ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId', 'getCopyMethod']
            )
            ->getMock();
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * @param InvokedCount $sendExpects
     * @param InvokedCount $sendCopyToExpects
     */
    protected function stepSend(
        InvokedCount $sendExpects,
        InvokedCount $sendCopyToExpects
    ) {
        $senderMock = $this->createPartialMockWithReflection(
            Sender::class,
            ['send', 'sendCopyTo']
        );
        $senderMock->expects($sendExpects)
            ->method('send');
        $senderMock->expects($sendCopyToExpects)
            ->method('sendCopyTo');

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($senderMock);
    }
}
