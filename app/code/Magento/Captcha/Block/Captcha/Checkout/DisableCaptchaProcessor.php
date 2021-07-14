<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Block\Captcha\Checkout;

use Magento\Captcha\Helper\Data;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class DisableCaptchaProcessor implements LayoutProcessorInterface
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
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        if (!(bool)$this->helper->getConfig('enable')) {
            $jsLayout['components']['checkout']['children']['authentication']['children']['captcha']['config']['componentDisabled'] = true;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']
            ['customer-email']['children']['additional-login-form-fields']['children']['captcha']['config']['componentDisabled'] = true;
        }
        return $jsLayout;
    }
}
