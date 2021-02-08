<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Plugin;

use Magento\Braintree\Plugin\DisableQuoteAddressValidation;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteManagement;
use PHPUnit\Framework\TestCase;

class DisableQuoteAddressValidationTest extends TestCase
{
    /**
     * @var DisableQuoteAddressValidation
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DisableQuoteAddressValidation();
    }

    /**
     * @param string $paymentMethod
     * @param bool $isGuest
     * @param array $addresses
     * @param bool $skipValidation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider beforeSubmitDataProvider
     */
    public function testBeforeSubmit(
        string $paymentMethod,
        bool $isGuest,
        array $addresses,
        bool $skipValidation
    ) {
        $subject = $this->createMock(QuoteManagement::class);
        $quote = $this->createMock(Quote::class);
        $payment = $this->createMock(Payment::class);
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $billingAddress = $this->createPartialMock(Address::class, ['setShouldIgnoreValidation']);
        $quote->method('getPayment')->willReturn($payment);
        $quote->method('getCustomer')->willReturn($isGuest ? null : $customer);
        $quote->method('getBillingAddress')->willReturn($billingAddress);
        $customer->method('getAddresses')->willReturn($addresses);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $billingAddress->expects($skipValidation ? $this->once() : $this->never())
            ->method('setShouldIgnoreValidation')
            ->with(true);
        $this->model->beforeSubmit($subject, $quote, []);
    }

    /**
     * @return array
     */
    public function beforeSubmitDataProvider(): array
    {
        return [
            ['braintree_paypal', true, [] ,true],
            ['braintree_paypal', false, [], true],
            ['braintree_paypal', false, [[]], false],
            ['payflowpro', true, [] ,false],
            ['payflowpro', false, [], false],
            ['payflowpro', false, [[]], false],
        ];
    }
}
