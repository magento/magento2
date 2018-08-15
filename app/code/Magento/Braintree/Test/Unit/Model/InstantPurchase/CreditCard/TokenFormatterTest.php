<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\InstantPurchase\CreditCard;

use Magento\Braintree\Model\InstantPurchase\CreditCard\TokenFormatter as CreditCardTokenFormatter;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class TokenFormatterTest extends TestCase
{
    /**
     * @var PaymentTokenInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenMock;

    /**
     * @var CreditCardTokenFormatter
     */
    private $creditCardTokenFormatter;

    /**
     * @var array
     */
    private $tokenDetails = [
        'type' => 'visa',
        'maskedCC' => '1111************9999',
        'expirationDate' => '01-01-2020'
    ];

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->paymentTokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->creditCardTokenFormatter = new CreditCardTokenFormatter();
    }

    /**
     * Testing the payment format with a known credit card type
     *
     * @return void
     */
    public function testFormatPaymentTokenWithKnownCardType()
    {
        $this->tokenDetails['type'] = key(CreditCardTokenFormatter::$baseCardTypes);
        $this->paymentTokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($this->tokenDetails));

        $formattedString = sprintf(
            '%s: %s, %s: %s (%s: %s)',
            __('Credit Card'),
            reset(CreditCardTokenFormatter::$baseCardTypes),
            __('ending'),
            $this->tokenDetails['maskedCC'],
            __('expires'),
            $this->tokenDetails['expirationDate']
        );

        self::assertEquals(
            $formattedString,
            $this->creditCardTokenFormatter->formatPaymentToken($this->paymentTokenMock)
        );
    }

    /**
     * Testing the payment format with a unknown credit card type
     *
     * @return void
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

        self::assertEquals(
            $formattedString,
            $this->creditCardTokenFormatter->formatPaymentToken($this->paymentTokenMock)
        );
    }

    /**
     * Testing the payment format with wrong card data
     *
     * @return void
     */
    public function testFormatPaymentTokenWithWrongData()
    {
        unset($this->tokenDetails['type']);
        $this->paymentTokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($this->tokenDetails));
        self::expectException('\InvalidArgumentException');

        $this->creditCardTokenFormatter->formatPaymentToken($this->paymentTokenMock);
    }
}
