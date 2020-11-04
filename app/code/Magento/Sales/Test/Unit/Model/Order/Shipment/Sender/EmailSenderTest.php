<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Info;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\Order\Shipment\Sender\EmailSender;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for email notification sender for Shipment.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSenderTest extends TestCase
{
    private const SHIPMENT_ID = 1;

    private const ORDER_ID = 1;

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
     * @var ShipmentInterface|MockObject
     */
    private $shipmentMock;

    /**
     * @var ShipmentCommentCreationInterface|MockObject
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
     * @var Info|MockObject
     */
    private $paymentInfoMock;

    /**
     * @var Data|MockObject
     */
    private $paymentHelperMock;

    /**
     * @var Shipment|MockObject
     */
    private $shipmentResourceMock;

    /**
     * @var Renderer|MockObject
     */
    private $addressRendererMock;

    /**
     * @var Template|MockObject
     */
    private $templateContainerMock;

    /**
     * @var ShipmentIdentity|MockObject
     */
    private $identityContainerMock;

    /**
     * @var SenderBuilderFactory|MockObject
     */
    private $senderBuilderFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getStoreId'])
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
            ->setMethods(['send', 'sendCopyTo'])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->shipmentMock = $this->getMockBuilder(Order\Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSendEmail', 'setEmailSent', 'getId'])
            ->getMock();

        $this->commentMock = $this->getMockBuilder(ShipmentCommentCreationInterface::class)
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
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::ORDER_ID);

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

        $this->shipmentResourceMock = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
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
            ShipmentIdentity::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->senderBuilderFactoryMock = $this->getMockBuilder(
            SenderBuilderFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subject = new EmailSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRendererMock,
            $this->paymentHelperMock,
            $this->shipmentResourceMock,
            $this->globalConfigMock,
            $this->eventManagerMock
        );
    }

    /**
     * @param int $configValue
     * @param bool $forceSyncMode
     * @param bool $isComment
     * @param bool $emailSendingResult
     * @param array $orderData
     *
     * @dataProvider sendDataProvider
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Exception
     */
    public function testSend($configValue, $forceSyncMode, $isComment, $emailSendingResult, $orderData)
    {
        $this->globalConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn($configValue);

        $this->orderMock->expects($this->any())
            ->method('getId')
            ->willReturn($orderData['order_id']);
        $this->orderMock->expects($this->any())
            ->method('getCustomerName')
            ->willReturn($orderData['customer_name']);
        $this->orderMock->expects($this->any())
            ->method('getIsNotVirtual')
            ->willReturn($orderData['is_not_virtual']);
        $this->orderMock->expects($this->any())
            ->method('getEmailCustomerNote')
            ->willReturn($orderData['email_customer_note']);
        $this->orderMock->expects($this->any())
            ->method('getFrontendStatusLabel')
            ->willReturn($orderData['frontend_status_label']);
        if (!$isComment) {
            $this->commentMock = null;
        }

        $this->shipmentMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::SHIPMENT_ID);
        $this->shipmentMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        if (!$configValue || $forceSyncMode) {
            $transport = [
                'order' => $this->orderMock,
                'order_id' => self::ORDER_ID,
                'shipment' => $this->shipmentMock,
                'shipment_id' => self::SHIPMENT_ID,
                'comment' => $isComment ? 'Comment text' : '',
                'billing' => $this->addressMock,
                'payment_html' => 'Payment Info Block',
                'store' => $this->storeMock,
                'formattedShippingAddress' => 'Formatted address',
                'formattedBillingAddress' => 'Formatted address',
                'order_data' => [
                    'customer_name' => $orderData['customer_name'],
                    'is_not_virtual' => $orderData['is_not_virtual'],
                    'email_customer_note' => $orderData['email_customer_note'],
                    'frontend_status_label' => $orderData['frontend_status_label']
                ]
            ];
            $transport = new DataObject($transport);

            $this->eventManagerMock->expects($this->once())
                ->method('dispatch')
                ->with(
                    'email_shipment_set_template_vars_before',
                    [
                        'sender' => $this->subject,
                        'transport' => $transport->getData(),
                        'transportObject' => $transport,
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

                $this->shipmentMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->shipmentResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->shipmentMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->subject->send(
                        $this->orderMock,
                        $this->shipmentMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            } else {
                $this->shipmentResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->shipmentMock, 'send_email');

                $this->assertFalse(
                    $this->subject->send(
                        $this->orderMock,
                        $this->shipmentMock,
                        $this->commentMock,
                        $forceSyncMode
                    )
                );
            }
        } else {
            $this->shipmentMock->expects($this->once())
                ->method('setEmailSent')
                ->with(null);

            $this->shipmentResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->shipmentMock, 'email_sent');
            $this->shipmentResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->shipmentMock, 'send_email');

            $this->assertFalse(
                $this->subject->send(
                    $this->orderMock,
                    $this->shipmentMock,
                    $this->commentMock,
                    $forceSyncMode
                )
            );
        }
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function sendDataProvider()
    {
        return [
            'Successful sync sending with comment' => [
                0, false, true, true,
                [
                    'order_id' => 1,
                    'shipment_id' => 1,
                    'customer_name' => 'test customer',
                    'is_not_virtual' => true,
                    'email_customer_note' => 1,
                    'frontend_status_label' => 'email_sent'
                ]
            ],
            'Successful sync sending without comment' => [
                0, false, false, true,
                [
                    'order_id' => 2,
                    'shipment_id' => 2,
                    'customer_name' => 'test customer 1',
                    'is_not_virtual' => true,
                    'email_customer_note' => 1,
                    'frontend_status_label' => 'email_sent'
                ]
            ],
            'Failed sync sending with comment' => [
                0, false, true, false,
                [
                    'order_id' => 3,
                    'shipment_id' => 3,
                    'customer_name' => 'test customer 2',
                    'is_not_virtual' => true,
                    'email_customer_note' => 1,
                    'frontend_status_label' => 'send_email'
                ]
            ],
            'Successful forced sync sending with comment' => [
                1, true, true, true,
                [
                    'order_id' => 4,
                    'shipment_id' => 4,
                    'customer_name' => 'test customer 3',
                    'is_not_virtual' => true,
                    'email_customer_note' => 1,
                    'frontend_status_label' => 'email_sent'
                ]
            ],
            'Async sending' => [
                1, false, false, false,
                [
                    'order_id' => 5,
                    'shipment_id' => 5,
                    'customer_name' => 'test customer 4',
                    'is_not_virtual' => true,
                    'email_customer_note' => 1,
                    'frontend_status_label' => 'send_email'
                ]
            ],
        ];
    }
}
