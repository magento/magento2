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
     * @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
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

<<<<<<< HEAD
=======
    /**
     * Test setup
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    protected function setUp()
    {
        $this->paymentTokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->paypalTokenFormatter = new PaypalTokenFormatter();
    }

<<<<<<< HEAD
=======
    /**
     * testFormatPaymentTokenWithKnownCardType
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
=======
    /**
     * testFormatPaymentTokenWithUnknownCardType
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
=======
    /**
     * testFormatPaymentTokenWithWrongData
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
