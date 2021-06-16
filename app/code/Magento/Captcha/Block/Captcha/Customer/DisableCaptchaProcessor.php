<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Block\Captcha\Customer;

use Magento\Captcha\Helper\Data;
use Magento\Customer\Block\Account\LayoutProcessorInterface;

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
            $jsLayout['components']['authenticationPopup']['children']['captcha']['config']['componentDisabled'] = true;
        }
        return $jsLayout;
    }
}
