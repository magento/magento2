<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Sales\Model\Resource\EntityAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceMock;

    protected function setUp()
    {
        $this->stepMockSetup();

        $this->orderResourceMock = $this->getMock(
            '\Magento\Sales\Model\Resource\Order',
            ['saveAttribute'],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\OrderIdentity',
            ['getStore', 'isEnabled', 'getConfigValue', 'getTemplateId', 'getGuestTemplateId'],
            [],
            '',
            false
        );
        $this->identityContainerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->sender = new OrderSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->loggerMock,
            $this->paymentHelper,
            $this->orderResourceMock,
            $this->globalConfig,
            $this->addressRenderer
        );
    }

    /**
     * @param int $configValue
     * @param bool|null $forceSyncMode
     * @param bool|null $emailSendingResult
     * @dataProvider sendDataProvider
     * @return void
     */
    public function testSend($configValue, $forceSyncMode, $emailSendingResult)
    {
        $address = 'address_test';
        $configPath = 'sales_email/general/async_sending';

        $this->orderMock->expects($this->once())
            ->method('setSendEmail')
            ->with(true);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($configValue);

        if (!$configValue || $forceSyncMode) {
            $this->identityContainerMock->expects($this->once())
                ->method('isEnabled')
                ->willReturn($emailSendingResult);

            if ($emailSendingResult) {
                $addressMock = $this->getMock(
                    'Magento\Sales\Model\Order\Address',
                    [],
                    [],
                    '',
                    false
                );

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

                $this->senderMock->expects($this->once())->method('sendCopyTo');

                $this->orderMock->expects($this->once())
                    ->method('setEmailSent')
                    ->with(true);

                $this->orderResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, ['send_email', 'email_sent']);

                $this->assertTrue(
                    $this->sender->send($this->orderMock)
                );
            } else {
                $this->orderResourceMock->expects($this->once())
                    ->method('saveAttribute')
                    ->with($this->orderMock, 'send_email');

                $this->assertFalse(
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
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            [0, 0, true],
            [0, 0, true],
            [0, 0, false],
            [0, 0, false],
            [0, 1, true],
            [0, 1, true],
            [1, null, null, null]
        ];
    }
}
