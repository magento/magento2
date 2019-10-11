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

class VoidCommandTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testVoidCommand()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('void');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/void.php';
        $response = include __DIR__ . '/../../_files/response/void.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute([
            'payment' => $paymentDO
        ]);

        /** @var Payment $payment */

        $this->assertTrue($payment->getIsTransactionClosed());
        $this->assertTrue($payment->getShouldCloseParentTransaction());
        $this->assertEquals('1234', $payment->getTransactionAdditionalInfo()['real_transaction_id']);
    }
}
