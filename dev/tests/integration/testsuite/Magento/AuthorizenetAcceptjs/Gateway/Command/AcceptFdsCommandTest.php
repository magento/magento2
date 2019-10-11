<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\AbstractTest;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Model\Order\Payment;

class AcceptFdsCommandTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testAcceptFdsCommand()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('accept_fds');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/accept_fds.php';
        $response = include __DIR__ . '/../../_files/response/generic_success.php';

        $this->clientMock->expects($this->once())
            ->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute([
            'payment' => $paymentDO
        ]);
    }
}
