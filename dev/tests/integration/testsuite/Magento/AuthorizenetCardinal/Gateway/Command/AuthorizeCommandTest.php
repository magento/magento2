<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\AbstractTest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Tests "Authorize" command for Authorize.net payment requests with 3D-Secure.
 */
class AuthorizeCommandTest extends AbstractTest
{
    /**
     * Tests Authorize command with enabled 3D secure and valid Cardinal response JWT.
     *
     * @magentoConfigFixture default_store three_d_secure/cardinal/enabled_authorizenet 1
     * @magentoConfigFixture default_store three_d_secure/cardinal/environment sandbox
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_key some_api_key
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_identifier some_api_identifier
     * @magentoConfigFixture default_store three_d_secure/cardinal/org_unit_id some_org_unit_id
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     *
     * @magentoAppIsolation enabled
     */
    public function testAuthorizeCommandWith3dSecure()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('authorize');

        $order = include __DIR__ . '/../../Fixture/full_order_with_3dsecure.php';
        $payment = $order->getPayment();

        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../Fixture/expected_request/authorize.php';
        $response = include __DIR__ . '/../../Fixture/response/authorize.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute(
            [
                'payment' => $paymentDO,
                'amount' => 100.00
            ]
        );

        /** @var Payment $payment */
        $rawDetails = [
            'authCode' => 'abc123',
            'avsResultCode' => 'P',
            'cvvResultCode' => '',
            'cavvResultCode' => '',
            'accountType' => 'Visa',
        ];
        $this->assertSame('1111', $payment->getCcLast4());
        $this->assertSame('P', $payment->getCcAvsStatus());
        $this->assertFalse($payment->getData('is_transaction_closed'));

        $transactionDetails = $payment->getTransactionAdditionalInfo();
        foreach ($rawDetails as $key => $value) {
            $this->assertSame($value, $payment->getAdditionalInformation($key));
            $this->assertSame($value, $transactionDetails[Transaction::RAW_DETAILS][$key]);
        }

        $this->assertSame('123456', $payment->getTransactionId());
        $this->assertSame('123456', $transactionDetails['real_transaction_id']);
    }

    /**
     * Tests Authorize command with enabled 3D secure and invalid Cardinal response JWT.
     *
     * @magentoConfigFixture default_store three_d_secure/cardinal/enabled_authorizenet 1
     * @magentoConfigFixture default_store three_d_secure/cardinal/environment sandbox
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_key some_api_key
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_identifier some_api_identifier
     * @magentoConfigFixture default_store three_d_secure/cardinal/org_unit_id some_org_unit_id
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     *
     * @magentoAppIsolation enabled
     */
    public function testAuthorizeCommandWithInvalidJwt()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('authorize');

        $order = include __DIR__ . '/../../../AuthorizenetAcceptjs/_files/full_order.php';
        $payment = $order->getPayment();
        $payment->setAdditionalInformation('cardinalJWT', 'Invalid JWT');

        $paymentDO = $this->paymentFactory->create($payment);

        $this->expectException(LocalizedException::class);

        $command->execute(
            [
                'payment' => $paymentDO,
                'amount' => 100.00
            ]
        );
    }

    /**
     * Tests Authorize command with disabled 3D secure.
     *
     * @magentoConfigFixture default_store three_d_secure/cardinal/enabled_authorizenet 0
     * @magentoConfigFixture default_store three_d_secure/cardinal/environment sandbox
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_key some_api_key
     * @magentoConfigFixture default_store three_d_secure/cardinal/api_identifier some_api_identifier
     * @magentoConfigFixture default_store three_d_secure/cardinal/org_unit_id some_org_unit_id
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     *
     * @magentoAppIsolation enabled
     */
    public function testAuthorizeCommandWithDisabled3dSecure()
    {
        /** @var CommandPoolInterface $commandPool */
        $commandPool = $this->objectManager->get('AuthorizenetAcceptjsCommandPool');
        $command = $commandPool->get('authorize');

        $order = include __DIR__ . '/../../Fixture/full_order_with_3dsecure.php';
        $payment = $order->getPayment();

        $paymentDO = $this->paymentFactory->create($payment);

        $expectedRequest = include __DIR__ . '/../../../AuthorizenetAcceptjs/_files/expected_request/authorize.php';
        $response = include __DIR__ . '/../../../AuthorizenetAcceptjs/_files/response/authorize.php';

        $this->clientMock->method('setRawData')
            ->with(json_encode($expectedRequest), 'application/json');

        $this->responseMock->method('getBody')
            ->willReturn(json_encode($response));

        $command->execute(
            [
                'payment' => $paymentDO,
                'amount' => 100.00
            ]
        );

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
