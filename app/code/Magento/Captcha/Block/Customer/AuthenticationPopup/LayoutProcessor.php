<?php
/**
 * Copyright 2025 Adobe.
 * All Rights Reserved.
 */

declare(strict_types=1);
namespace Magento\Captcha\Block\Customer\AuthenticationPopup;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @param HelperCaptcha $helper
     */
    public function __construct(
        private readonly HelperCaptcha $helper
    ) {
    }

    /**
     * Process jsLayout of checkout page
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout): array
    {
        if ($this->helper->getConfig('enable')) {
            $jsLayout['components']['authenticationPopup']['children']['captcha'] = [
                'component' => 'Magento_Captcha/js/view/checkout/loginCaptcha',
                'displayArea' => 'additional-login-form-fields',
                'formId' => 'user_login',
                'configSource' => 'checkout'
            ];
        }
        return $jsLayout;
    }
}
