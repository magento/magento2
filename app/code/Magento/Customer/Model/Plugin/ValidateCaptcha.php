<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Captcha\Model\CaptchaValidator;

/**
 * Plugin to validate captcha for account creation
 */
class ValidateCaptcha
{
    /**
     * Constant for form Id
     */
    public const CAPTCHA_FORM_ID = 'user_create';

    /**
     * @var CaptchaValidator
     */
    private CaptchaValidator $captchaValidator;

    /**
     * @param CaptchaValidator $captchaValidator
     */
    public function __construct(
        CaptchaValidator $captchaValidator
    ) {
        $this->captchaValidator = $captchaValidator;
    }

    /**
     * Validate captcha before creating account
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreateAccount(
        AccountManagement $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ): void {
        $this->captchaValidator->validate(self::CAPTCHA_FORM_ID);
    }
}
