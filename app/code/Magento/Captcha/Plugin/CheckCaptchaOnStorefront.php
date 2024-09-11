<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Plugin;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Customer\Block\Account\AuthenticationPopup;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Check need captcha for authentication popup
 */
class CheckCaptchaOnStorefront
{
    /**
     * @var HelperCaptcha
     */
    private $helper;

    /**
     * Customer session
     *
     * @var HttpContext
     */
    private $httpContext;

    /**
     * CheckCaptchaOnStorefront constructor
     *
     * @param HelperCaptcha $helper
     * @param HttpContext $httpContext
     */
    public function __construct(
        HelperCaptcha $helper,
        HttpContext $httpContext
    ) {
        $this->helper = $helper;
        $this->httpContext = $httpContext;
    }

    /**
     * Remove template when loggin or disable captcha storefront
     *
     * @param AuthenticationPopup $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTemplate(
        AuthenticationPopup $subject,
        $result
    ) {
        if ($this->isLoggedIn() || !$this->helper->getConfig('enable')) {
            return '';
        }

        return $result;
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    private function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
