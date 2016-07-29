<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Sender;

/**
 * Unit test for email notification sender for Invoice.
 */
class EmailSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\Sender\EmailSender
     */
    private $subject;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceCommentCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentMock;

    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $globalConfigMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Payment\Model\Info|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoMock;

    /**
     * @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentHelperMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRendererMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateContainerMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $identityContainerMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\SenderBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderBuilderFactoryMock;

    protected function setUp()
    {
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getStoreId'],
            [],
            '',
            false
        );
        $this->storeMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $this->orderMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->senderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender',
            ['send', 'sendCopyTo'],
            [],
            '',
            false
        );

        $this->loggerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['setSendEmail', 'setEmailSent'],
            [],
            '',
            false
        );

        $this->commentMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\Data\InvoiceCommentCreationInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->commentMock->expects($this->any())
            ->method('getComment')
            ->willReturn('Comment text');

        $this->addressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
        $this->orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->globalConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->paymentInfoMock = $this->getMock(
            'Magento\Payment\Model\Info',
            [],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentInfoMock);

        $this->paymentHelperMock = $this->getMock(
            'Magento\Payment\Helper\Data',
            [],
            [],
            '',
            false
        );
        $this->paymentHelperMock->expects($this->any())
            ->method('getInfoBlockHtml')
            ->with($this->paymentInfoMock, 1)
            ->willReturn('Payment Info Block');

        $this->invoiceResourceMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Invoice',
            [],
            [],
            '',
            false
        );

        $this->addressRendererMock = $this->getMock(
            'Magento\Sales\Model\Order\Address\Renderer',
            [],
            [],
            '',
            false
        );
        $this->addressRendererMock->expects($this->any())
            ->method('format')
            ->with($this->addressMock, 'html')
            ->willReturn('Formatted address');

        $this->templateContainerMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Container\Template',
            [],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Container\InvoiceIdentity',
            [],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->senderBuilderFactoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\SenderBuilderFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->subject = new \Magento\Sales\Model\Order\Invoice\Sender\EmailSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRendererMock,
            $this->paymentHelperMock,
            $this->invoiceResourceMock,
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
     * @dataProvider sendDataProvider
     *
     * @return void
     */
    public function testSend($configValue, $forceSyncMode, $isComment, $emailSendingResult)
    {
        $this->globalConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn($configValue);

        if (!$isComment) {
            $this->commentMock = null;
        }

        $this->invoiceMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        if (!$configValue || $forceSyncMode) {
            $transport = [
                'order' => $this->orderMock,
                'invoice' => $this->invoiceMock,
                'comment' => $isComment ? 'Comment text' : '',
                'billing' => $this->addressMock,
                'payment_html' => 'Payment Info Block',
                'store' => $this->storeMock,
                'formattedShippingAddress' => 'Formatted address',
                'formattedBillingAddress' => 'Formatted address'
            ];

            $this->eventManagerMock->expects($this->once())
                ->method('dispatch')
                ->with(
                    'email_invoice_set_template_vars_before',
                    [
                        'sender' => $this->subject,
                        'transport' => $transport
                    ]
                );

            $this->templateContainerMock->expects($this->once())
                ->method('setTemplateVars')
                ->with($transport);

            $this->identityContainerMock->expects($this->once())
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $this->senderBuilderFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($this->senderMock);

                $this->senderMock->expects($this->once())
                    ->method('send');

                $this->senderMock->expects($this->once())
                    ->method('sendCopyTo');

                $this->invoiceMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->invoiceResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->invoiceMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->subject->send(
                        $this->orderMock,
                        $this->invoiceMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            } else {
                $this->invoiceResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->invoiceMock, 'send_email');

                $this->assertFalse(
                    $this->subject->send(
                        $this->orderMock,
                        $this->invoiceMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            }
        } else {
            $this->invoiceMock->expects($this->once())
                ->method('setEmailSent')
                ->with(null);

            $this->invoiceResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->invoiceMock, 'email_sent');
            $this->invoiceResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->invoiceMock, 'send_email');

            $this->assertFalse(
                $this->subject->send(
                    $this->orderMock,
                    $this->invoiceMock,
                    $this->commentMock,
                    $forceSyncMode
                )
            );
        }
    }

    /**
     * @return array
     */
    public function sendDataProvider()
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
