<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\AbstractTest;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Response\CloseTransactionHandler class.
 */
class CloseTransactionHandlerTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     *
     * @return void
     */
    public function testTransactionCloseStatus(): void
    {
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('settle');
        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/settle.php';
        $response = include __DIR__ . '/../../_files/response/settle.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');
        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute(['payment' => $paymentDO]);

        $this->assertFalse($payment->getIsTransactionClosed());
    }
}
