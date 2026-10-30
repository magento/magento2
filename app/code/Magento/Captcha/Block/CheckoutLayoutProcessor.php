<?php
/**
 * Copyright 2025 Adobe.
 * All Rights Reserved.
 */

declare(strict_types=1);
namespace Magento\Captcha\Block;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Captcha\Helper\Data as HelperCaptcha;

class CheckoutLayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @param HelperCaptcha $helper
     */
    public function __construct(
        private readonly HelperCaptcha $helper
    ) {
    }

    /**
     * Remove captcha from checkout page if it is disabled
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout): array
    {
        if ($this->helper->getConfig('enable')) {
            $captcha = [
                'component' => 'Magento_Captcha/js/view/checkout/loginCaptcha',
                'displayArea'   => 'additional-login-form-fields',
                'formId' => 'user_login',
                'configSource' => 'checkoutConfig'
            ];
            $jsLayout['components']['checkout']['children']['authentication']['children']['captcha'] = $captcha;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['customer-email']['children']['additional-login-form-fields']
            ['children']['captcha'] = $captcha;
        }
        return $jsLayout;
    }
}
