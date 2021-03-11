<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\InstantPurchase\PayPal;

use Magento\Braintree\Model\InstantPurchase\CreditCard\TokenFormatter as PaypalTokenFormatter;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class TokenFormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentTokenInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentTokenMock;

    /**
     * @var PaypalTokenFormatter
     */
    private $paypalTokenFormatter;

    /**
     * @var array
     */
    private $tokenDetails = [
        'type' => 'visa',
        'maskedCC' => '4444************9999',
        'expirationDate' => '07-07-2025'
    ];

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->paymentTokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->paypalTokenFormatter = new PaypalTokenFormatter();
    }

    /**
     * testFormatPaymentTokenWithKnownCardType
     */
    public function testFormatPaymentTokenWithKnownCardType()
    {
        $this->tokenDetails['type'] = key(PaypalTokenFormatter::$baseCardTypes);
        $this->paymentTokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($this->tokenDetails));

        $formattedString = sprintf(
            '%s: %s, %s: %s (%s: %s)',
            __('Credit Card'),
            reset(PaypalTokenFormatter::$baseCardTypes),
            __('ending'),
            $this->tokenDetails['maskedCC'],
            __('expires'),
            $this->tokenDetails['expirationDate']
        );

        self::assertEquals($formattedString, $this->paypalTokenFormatter->formatPaymentToken($this->paymentTokenMock));
    }

    /**
     * testFormatPaymentTokenWithUnknownCardType
     */
    public function testFormatPaymentTokenWithUnknownCardType()
    {
        $this->paymentTokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($this->tokenDetails));

        $formattedString = sprintf(
            '%s: %s, %s: %s (%s: %s)',
            __('Credit Card'),
            $this->tokenDetails['type'],
            __('ending'),
            $this->tokenDetails['maskedCC'],
            __('expires'),
            $this->tokenDetails['expirationDate']
        );

        self::assertEquals($formattedString, $this->paypalTokenFormatter->formatPaymentToken($this->paymentTokenMock));
    }

    /**
     * testFormatPaymentTokenWithWrongData
     */
    public function testFormatPaymentTokenWithWrongData()
    {
        unset($this->tokenDetails['type']);

        $this->paymentTokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($this->tokenDetails));
        self::expectException('\InvalidArgumentException');

        $this->paypalTokenFormatter->formatPaymentToken($this->paymentTokenMock);
    }
}
