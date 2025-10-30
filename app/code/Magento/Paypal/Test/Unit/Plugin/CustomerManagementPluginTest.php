<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Plugin;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use Magento\Paypal\Plugin\CustomerManagementPlugin;
use Magento\Quote\Model\Quote\Payment;

class CustomerManagementPluginTest extends TestCase
{
    /**
     * @var CustomerManagementPlugin
     */
    private $plugin;

    /**
     * @var CustomerManagement
     */
    private $customerManagement;

    /**
     * @var Payment
     */
    private $paymentMethod;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var callable
     */
    private $proceed;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->quote = $this->createMock(Quote::class);
        $this->paymentMethod = $this->createMock(Payment::class);
        $this->customerManagement = $this->createMock(CustomerManagement::class);
        $this->plugin = new CustomerManagementPlugin();
    }

    /**
     * Test to skip address validation for PayPal payment method to guest customer
     */
    public function testAroundValidateAddressesWithPaypal()
    {
        $this->paymentMethod->method('getMethod')->willReturn('paypal_express');
        $this->quote->method('getPayment')->willReturn($this->paymentMethod);
        $this->quote->method('getCustomerIsGuest')->willReturn(true);

        $this->proceed = function ($quote) {
            $this->assertSame($quote, $this->quote);
        };
        $this->plugin->aroundValidateAddresses($this->customerManagement, $this->proceed, $this->quote);
    }

    /**
     * Test to proceed with address validation for other payment methods
     */
    public function testAroundValidateAddressesWithOtherPaymentMethod()
    {
        $this->paymentMethod->method('getMethod')->willReturn('checkmo');
        $this->quote->method('getPayment')->willReturn($this->paymentMethod);
        $this->quote->method('getCustomerIsGuest')->willReturn(true);
        $this->proceed = function ($quote) {
            $this->assertSame($quote, $this->quote);
        };
        $this->plugin->aroundValidateAddresses($this->customerManagement, $this->proceed, $this->quote);
    }

    /**
     * Test to proceed with address validation when PayPal is selected and customer is not a guest
     */
    public function testAroundValidateAddressesWithPaypalAndLoggedInCustomer()
    {
        $this->paymentMethod->method('getMethod')->willReturn('paypal_express');
        $this->quote->method('getPayment')->willReturn($this->paymentMethod);
        $this->quote->method('getCustomerIsGuest')->willReturn(false);
        $this->proceed = function ($quote) {
            $this->assertSame($quote, $this->quote);
        };
        $this->plugin->aroundValidateAddresses($this->customerManagement, $this->proceed, $this->quote);
    }
}
