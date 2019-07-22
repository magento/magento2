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
use Magento\Sales\Model\Order\Payment\Transaction;

class AuthorizeCommandTest extends AbstractTest
{
    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     */
    public function testAuthorizeCommand()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('authorize');

        $order = include __DIR__ . '/../../_files/full_order.php';
        $payment = $order->getPayment();

        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../_files/expected_request/authorize.php';
        $response = include __DIR__ . '/../../_files/response/authorize.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute([
            'payment' => $paymentDO,
            'amount' => 100.00
        ]);

        /** @var Payment $payment */
        $rawDetails = [
            'authCode' => 'abc123',
            'avsResultCode' => 'Y',
            'cvvResultCode' => 'P',
            'cavvResultCode' => '2',
            'accountType' => 'Visa',
        ];
        $this->assertSame('1111', $payment->getCcLast4());
        $this->assertSame('Y', $payment->getCcAvsStatus());
        $this->assertFalse($payment->getData('is_transaction_closed'));

        $transactionDetails = $payment->getTransactionAdditionalInfo();
        foreach ($rawDetails as $key => $value) {
            $this->assertSame($value, $payment->getAdditionalInformation($key));
            $this->assertSame($value, $transactionDetails[Transaction::RAW_DETAILS][$key]);
        }

        $this->assertSame('123456', $payment->getTransactionId());
        $this->assertSame('123456', $transactionDetails['real_transaction_id']);
    }
}
