<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\InstantPurchase;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Model\InstantPurchase\PaymentAdditionalInformationProvider;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * @covers \Magento\Braintree\Model\InstantPurchase\PaymentAdditionalInformationProvider
 */
class PaymentAdditionalInformationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var PaymentAdditionalInformationProvider
     */
    private $paymentAdditionalInformationProvider;

    /**
     * @var GetPaymentNonceCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $getPaymentNonceCommandMock;

    /**
     * @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenMock;

    /**
     * @var ArrayResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arrayResultMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->getPaymentNonceCommandMock = $this->createMock(GetPaymentNonceCommand::class);
        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);
        $this->arrayResultMock = $this->createMock(ArrayResult::class);
        $this->paymentAdditionalInformationProvider = new PaymentAdditionalInformationProvider(
            $this->getPaymentNonceCommandMock
        );
    }

    /**
     * Test getAdditionalInformation method
     *
     * @return void
     */
    public function testGetAdditionalInformation()
    {
        $customerId = 15;
        $publicHash = '3n4b7sn48g';
        $paymentMethodNonce = 'test';

        $this->paymentTokenMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->paymentTokenMock->expects($this->once())->method('getPublicHash')->willReturn($publicHash);
        $this->getPaymentNonceCommandMock->expects($this->once())->method('execute')->with([
            PaymentTokenInterface::CUSTOMER_ID => $customerId,
            PaymentTokenInterface::PUBLIC_HASH => $publicHash,
        ])->willReturn($this->arrayResultMock);
        $this->arrayResultMock->expects($this->once())->method('get')
            ->willReturn(['paymentMethodNonce' => $paymentMethodNonce]);

        $expected = [
            'payment_method_nonce' => $paymentMethodNonce,
        ];
        $actual = $this->paymentAdditionalInformationProvider->getAdditionalInformation($this->paymentTokenMock);
        self::assertEquals($expected, $actual);
    }
}
