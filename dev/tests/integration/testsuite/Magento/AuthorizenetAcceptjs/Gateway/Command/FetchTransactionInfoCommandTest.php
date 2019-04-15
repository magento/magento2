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

class FetchTransactionInfoCommandTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/transactionSyncKeys transId,transactionType
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testTransactionApproved()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('fetch_transaction_information');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/transaction_details_authorized.php';
        $response = include __DIR__ . '/../../_files/response/transaction_details_authorized.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $result = $command->execute([
            'payment' => $paymentDO
        ]);

        $expected = [
            'transId' => '1234',
            'transactionType' => 'authOnlyTransaction'
        ];
        $this->assertSame($expected, $result);

        /** @var Payment $payment */
        $this->assertTrue($payment->getIsTransactionApproved());
        $this->assertFalse($payment->getIsTransactionDenied());
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * * @magentoConfigFixture default_store payment/authorizenet_acceptjs/transactionSyncKeys transId,transactionType
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testTransactionVoided()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('fetch_transaction_information');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/transaction_details_authorized.php';
        $response = include __DIR__ . '/../../_files/response/transaction_details_voided.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $result = $command->execute([
            'payment' => $paymentDO
        ]);

        $expected = [
            'transId' => '1234',
            'transactionType' => 'authOnlyTransaction'
        ];
        $this->assertSame($expected, $result);

        /** @var Payment $payment */
        $this->assertFalse($payment->getIsTransactionApproved());
        $this->assertTrue($payment->getIsTransactionDenied());
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/transactionSyncKeys transId,transactionType
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testTransactionDenied()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('fetch_transaction_information');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/transaction_details_authorized.php';
        $response = include __DIR__ . '/../../_files/response/transaction_details_voided.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $result = $command->execute([
            'payment' => $paymentDO
        ]);

        $expected = [
            'transId' => '1234',
            'transactionType' => 'authOnlyTransaction'
        ];
        $this->assertSame($expected, $result);

        /** @var Payment $payment */
        $this->assertFalse($payment->getIsTransactionApproved());
        $this->assertTrue($payment->getIsTransactionDenied());
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/transactionSyncKeys transId,transactionType
     * @magentoDataFixture Magento/AuthorizenetAcceptjs/Fixture/order_auth_only.php
     */
    public function testTransactionPending()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('fetch_transaction_information');

        $order = $this->getOrderWithIncrementId('100000002');
        $payment = $order->getPayment();
        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/transaction_details_authorized.php';
        $response = include __DIR__ . '/../../_files/response/transaction_details_fds_pending.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $result = $command->execute([
            'payment' => $paymentDO
        ]);

        $expected = [
            'transId' => '1234',
            'transactionType' => 'authOnlyTransaction'
        ];
        $this->assertSame($expected, $result);

        /** @var Payment $payment */
        $this->assertNull($payment->getIsTransactionApproved());
        $this->assertNull($payment->getIsTransactionDenied());
    }
}
