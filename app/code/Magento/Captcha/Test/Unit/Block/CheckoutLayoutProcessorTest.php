<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Block;

use Magento\Captcha\Block\CheckoutLayoutProcessor;
use Magento\Captcha\Helper\Data as HelperCaptcha;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutLayoutProcessorTest extends TestCase
{
    /**
     * @var HelperCaptcha|MockObject
     */
    private $helperMock;

    /**
     * @var CheckoutLayoutProcessor
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(HelperCaptcha::class);
        $this->processor = new CheckoutLayoutProcessor($this->helperMock);
    }

    /**
     * The billing step is the checkout's entry point whenever the shipping step is skipped, e.g. for
     * a virtual-product-only cart, so it must receive the login captcha too, not just the shipping step.
     */
    public function testProcessAddsCaptchaToBillingStepLoginFormWhenEnabled(): void
    {
        $this->helperMock->method('getConfig')->with('enable')->willReturn(true);

        $result = $this->processor->process(['components' => ['checkout' => ['children' => []]]]);

        $billingStepCaptcha = $result['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['customer-email']['children']['additional-login-form-fields']
            ['children']['captcha'];
        $shippingStepCaptcha = $result['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['customer-email']['children']['additional-login-form-fields']
            ['children']['captcha'];
        $authenticationCaptcha = $result['components']['checkout']['children']['authentication']['children']['captcha'];

        $expectedCaptcha = [
            'component' => 'Magento_Captcha/js/view/checkout/loginCaptcha',
            'displayArea' => 'additional-login-form-fields',
            'formId' => 'user_login',
            'configSource' => 'checkoutConfig',
        ];
        self::assertEquals($expectedCaptcha, $billingStepCaptcha);
        self::assertEquals($expectedCaptcha, $shippingStepCaptcha);
        self::assertEquals($expectedCaptcha, $authenticationCaptcha);
    }

    public function testProcessLeavesLayoutUnchangedWhenCaptchaDisabled(): void
    {
        $this->helperMock->method('getConfig')->with('enable')->willReturn(false);

        $jsLayout = ['components' => ['checkout' => ['children' => []]]];

        self::assertSame($jsLayout, $this->processor->process($jsLayout));
    }
}
