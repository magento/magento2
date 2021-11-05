<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Info;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Creditmemo\Sender\EmailSender;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for email notification sender for Creditmemo.
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSenderTest extends TestCase
{
    /**
     * @var EmailSender
     */
    private $subject;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Sender|MockObject
     */
    private $senderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CreditmemoInterface|MockObject
     */
    private $creditmemoMock;

    /**
     * @var CreditmemoCommentCreationInterface|MockObject
     */
    private $commentMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $globalConfigMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Payment\Model\Info|MockObject
     */
    private $paymentInfoMock;

    /**
     * @var Data|MockObject
     */
    private $paymentHelperMock;

    /**
     * @var Creditmemo|MockObject
     */
    private $creditmemoResourceMock;

    /**
     * @var Renderer|MockObject
     */
    private $addressRendererMock;

    /**
     * @var Template|MockObject
     */
    private $templateContainerMock;

    /**
     * @var CreditmemoIdentity|MockObject
     */
    private $identityContainerMock;

    /**
     * @var SenderBuilderFactory|MockObject
     */
    private $senderBuilderFactoryMock;

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->senderMock = $this->getMockBuilder(Sender::class)
            ->disableOriginalConstructor()
            ->addMethods(['send', 'sendCopyTo'])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setEmailSent'])
            ->addMethods(['setSendEmail'])
            ->getMock();

        $this->commentMock = $this->getMockBuilder(CreditmemoCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->commentMock->expects($this->any())
            ->method('getComment')
            ->willReturn('Comment text');

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
        $this->orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->globalConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentInfoMock = $this->getMockBuilder(Info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentHelperMock->expects($this->any())
            ->method('getInfoBlockHtml')
            ->with($this->paymentInfoMock, 1)
            ->willReturn('Payment Info Block');

        $this->creditmemoResourceMock = $this->getMockBuilder(
            Creditmemo::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->addressRendererMock = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRendererMock->expects($this->any())
            ->method('format')
            ->with($this->addressMock, 'html')
            ->willReturn('Formatted address');

        $this->templateContainerMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityContainerMock = $this->getMockBuilder(
            CreditmemoIdentity::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->senderBuilderFactoryMock = $this->getMockBuilder(SenderBuilderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->subject = new EmailSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRendererMock,
            $this->paymentHelperMock,
            $this->creditmemoResourceMock,
            $this->globalConfigMock,
            $this->eventManagerMock
        );
    }

    /**
     * @param int $configValue
     * @param bool $forceSyncMode
     * @param bool $isComment
     * @param bool $emailSendingResult
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider sendDataProvider
     */
    public function testSend(
        int $configValue,
        bool $forceSyncMode,
        bool $isComment,
        bool $emailSendingResult
    ): void {
        $this->globalConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn($configValue);

        if (!$isComment) {
            $this->commentMock = null;
        }

        $this->creditmemoMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        $this->orderMock->method('getCustomerName')->willReturn('Customer name');
        $this->orderMock->method('getIsNotVirtual')->willReturn(true);
        $this->orderMock->method('getEmailCustomerNote')->willReturn(null);
        $this->orderMock->method('getFrontendStatusLabel')->willReturn('Pending');

        if (!$configValue || $forceSyncMode) {
            $transport = [
                'order' => $this->orderMock,
                'creditmemo' => $this->creditmemoMock,
                'comment' => $isComment ? 'Comment text' : '',
                'billing' => $this->addressMock,
                'payment_html' => 'Payment Info Block',
                'store' => $this->storeMock,
                'formattedShippingAddress' => 'Formatted address',
                'formattedBillingAddress' => 'Formatted address',
                'order_data' => [
                    'customer_name' => 'Customer name',
                    'is_not_virtual' => true,
                    'email_customer_note' => null,
                    'frontend_status_label' => 'Pending'
                ]
            ];
            $transport = new DataObject($transport);

            $this->eventManagerMock->expects($this->once())
                ->method('dispatch')
                ->with(
                    'email_creditmemo_set_template_vars_before',
                    [
                        'sender' => $this->subject,
                        'transport' => $transport->getData(),
                        'transportObject' => $transport
                    ]
                );

            $this->templateContainerMock->expects($this->once())
                ->method('setTemplateVars')
                ->with($transport->getData());

            $this->identityContainerMock->expects($this->exactly(2))
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $this->identityContainerMock->expects($this->once())
                    ->method('getCopyMethod')
                    ->willReturn('copy');

                $this->senderBuilderFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($this->senderMock);

                $this->senderMock->expects($this->once())
                    ->method('send');

                $this->senderMock->expects($this->once())
                    ->method('sendCopyTo');

                $this->creditmemoMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->creditmemoResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->creditmemoMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->subject->send(
                        $this->orderMock,
                        $this->creditmemoMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            } else {
                $this->creditmemoResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->creditmemoMock, 'send_email');

                $this->assertFalse(
                    $this->subject->send(
                        $this->orderMock,
                        $this->creditmemoMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            }
        } else {
            $this->creditmemoMock->expects($this->once())
                ->method('setEmailSent')
                ->with(null);

            $this->creditmemoResourceMock
                ->method('saveAttribute')
                ->withConsecutive(
                    [$this->creditmemoMock, 'email_sent'],
                    [$this->creditmemoMock, 'send_email']
                );

            $this->assertFalse(
                $this->subject->send(
                    $this->orderMock,
                    $this->creditmemoMock,
                    $this->commentMock,
                    $forceSyncMode
                )
            );
        }
    }

    /**
     * @return array
     */
    public function sendDataProvider(): array
    {
        return [
            'Successful sync sending with comment' => [0, false, true, true],
            'Successful sync sending without comment' => [0, false, false, true],
            'Failed sync sending with comment' => [0, false, true, false],
            'Successful forced sync sending with comment' => [1, true, true, true],
            'Async sending' => [1, false, false, false]
        ];
    }
}
