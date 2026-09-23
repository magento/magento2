<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Captcha\Model\CaptchaValidator;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Plugin\ValidateCaptcha;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test ValidateCaptcha plugin
 */
class ValidateCaptchaTest extends TestCase
{
    /**
     * @var ValidateCaptcha
     */
    private $plugin;

    /**
     * @var CaptchaValidator|MockObject
     */
    private $captchaValidatorMock;

    /**
     * @var AccountManagement|MockObject
     */
    private $accountManagementMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->captchaValidatorMock = $this->createMock(CaptchaValidator::class);
        $this->accountManagementMock = $this->createMock(AccountManagement::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);

        $this->plugin = new ValidateCaptcha(
            $this->captchaValidatorMock
        );
    }

    /**
     * Test beforeCreateAccount with valid captcha
     *
     * @return void
     * @throws LocalizedException
     */
    public function testBeforeCreateAccountWithValidCaptcha(): void
    {
        $password = 'password123';
        $redirectUrl = 'http://example.com';

        $this->captchaValidatorMock->expects($this->once())
            ->method('validate')
            ->with(ValidateCaptcha::CAPTCHA_FORM_ID);

        $this->plugin->beforeCreateAccount(
            $this->accountManagementMock,
            $this->customerMock,
            $password,
            $redirectUrl
        );
    }

    /**
     * Test beforeCreateAccount with invalid captcha throws exception
     *
     * @return void
     */
    public function testBeforeCreateAccountWithInvalidCaptchaThrowsException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Incorrect CAPTCHA');

        $password = 'password123';
        $redirectUrl = 'http://example.com';

        $this->captchaValidatorMock->expects($this->once())
            ->method('validate')
            ->with(ValidateCaptcha::CAPTCHA_FORM_ID)
            ->willThrowException(new LocalizedException(__('Incorrect CAPTCHA')));

        $this->plugin->beforeCreateAccount(
            $this->accountManagementMock,
            $this->customerMock,
            $password,
            $redirectUrl
        );
    }
}
