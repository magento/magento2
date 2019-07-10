<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class OrderSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $sender;

    /**
     * @var \Magento\Sales\Model\ResourceModel\EntityAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceMock;

    protected function setUp()
    {
        $this->stepMockSetup();

        $this->orderResourceMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order::class,
            ['saveAttribute']
        );

        $this->identityContainerMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Container\OrderIdentity::class,
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId']
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->sender = new OrderSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->addressRenderer,
            $this->paymentHelper,
            $this->orderResourceMock,
            $this->globalConfig,
            $this->eventManagerMock
        );
    }

    /**
     * @param int $configValue
     * @param bool|null $forceSyncMode
     * @param bool|null $emailSendingResult
     * @param $senderSendException
     * @return void
     * @dataProvider sendDataProvider
     */
    public function testSend($configValue, $forceSyncMode, $emailSendingResult, $senderSendException)
    {
        $address = 'address_test';
        $configPath = 'sales_email/general/async_sending';

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with($emailSendingResult);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $this->identityContainerMock->expects($this->exactly(2))
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);

                $this->addressRenderer->expects($this->any())
                    ->method('format')
                    ->with($addressMock, 'html')
                    ->willReturn($address);

                $this->orderMock->expects($this->any())
                    ->method('getBillingAddress')
                    ->willReturn($addressMock);

                $this->orderMock->expects($this->any())
                    ->method('getShippingAddress')
                    ->willReturn($addressMock);

                $this->templateContainerMock->expects($this->once())
                    ->method('setTemplateVars')
                    ->with(
                        [
                            'order' => $this->orderMock,
                            'billing' => $addressMock,
                            'payment_html' => 'payment',
                            'store' => $this->storeMock,
                            'formattedShippingAddress' => $address,
                            'formattedBillingAddress' => $address
                        ]
                    );

                $this->senderBuilderFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($this->senderMock);

                $this->senderMock->expects($this->once())->method('send');

                if ($senderSendException) {
                    $this->checkSenderSendExceptionCase();
                } else {
                    $this->senderMock->expects($this->once())->method('sendCopyTo');

                    $this->orderMock->expects($this->once())
                        ->method('setEmailSent')
                        ->with($emailSendingResult);

                    $this->orderResourceMock->expects($this->once())
                        ->method('saveAttribute')
                        ->with($this->orderMock, ['send_email', 'email_sent']);

                    $this->assertTrue(
                        $this->sender->send($this->orderMock)
                    );
                }
            } else {
                $this->orderResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, 'send_email');

                $this->assertFalse(
                    $this->sender->send($this->orderMock)
                );
            }
        } else {
            $this->orderResourceMock->expects($this->at(0))
                ->method('saveAttribute')
                ->with($this->orderMock, 'email_sent');
            $this->orderResourceMock->expects($this->at(1))
                ->method('saveAttribute')
                ->with($this->orderMock, 'send_email');

            $this->assertFalse(
                $this->sender->send($this->orderMock)
            );
        }
    }

    /**
     * Methods check case when method "send" in "senderMock" throw exception.
     *
     * @return void
     */
    protected function checkSenderSendExceptionCase()
    {
        $this->senderMock->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception('exception'));

        $this->orderResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, 'send_email');

        $this->assertFalse(
            $this->sender->send($this->orderMock)
        );
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            [0, 0, true, false],
            [0, 0, true, false],
            [0, 0, true, true],
            [0, 0, false, false],
            [0, 0, false, false],
            [0, 0, false, true],
            [0, 1, true, false],
            [0, 1, true, false],
            [0, 1, true, false],
            [1, null, null, false]
        ];
    }

    /**
     * @param bool $isVirtualOrder
     * @param int $formatCallCount
     * @param string|null $expectedShippingAddress
     * @dataProvider sendVirtualOrderDataProvider
     */
    public function testSendVirtualOrder($isVirtualOrder, $formatCallCount, $expectedShippingAddress)
    {
        $address = 'address_test';
        $this->orderMock->setData(\Magento\Sales\Api\Data\OrderInterface::IS_VIRTUAL, $isVirtualOrder);

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/async_sending')
            ->willReturn(false);

        $this->identityContainerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturn(true);

        $addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);

        $this->addressRenderer->expects($this->exactly($formatCallCount))
            ->method('format')
            ->with($addressMock, 'html')
            ->willReturn($address);

        $this->stepAddressFormat($addressMock, $isVirtualOrder);

        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                [
                    'order' => $this->orderMock,
                    'billing' => $addressMock,
                    'payment_html' => 'payment',
                    'store' => $this->storeMock,
                    'formattedShippingAddress' => $expectedShippingAddress,
                    'formattedBillingAddress' => $address
                ]
            );

        $this->senderBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->senderMock);

        $this->senderMock->expects($this->once())->method('send');

        $this->senderMock->expects($this->once())->method('sendCopyTo');

        $this->orderMock->expects($this->once())
            ->method('setEmailSent')
            ->with(true);

        $this->orderResourceMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, ['send_email', 'email_sent']);

        $this->assertTrue($this->sender->send($this->orderMock));
    }

    /**
     * @return array
     */
    public function sendVirtualOrderDataProvider()
    {
        return [
            [true, 1, null],
            [false, 2, 'address_test']
        ];
    }
}
