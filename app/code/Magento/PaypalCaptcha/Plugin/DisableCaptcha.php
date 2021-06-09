<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalCaptcha\Plugin;

use Magento\Captcha\Block\Captcha\Checkout\DisableCaptchaProcessor;
use Magento\Captcha\Helper\Data;

class DisableCaptcha
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param DisableCaptchaProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(DisableCaptchaProcessor $subject, array $jsLayout): array
    {
        if (!(bool)$this->helper->getConfig('enable')) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']
            ['payments-list']['children']['paypal-captcha']['config']['componentDisabled'] = true;
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']
            ['payments-list']['children']['paypal-captcha']['children']['captcha']['config']['componentDisabled'] = true;
        }
        return $jsLayout;
    }
}
