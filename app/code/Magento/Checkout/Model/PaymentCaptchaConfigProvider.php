<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Captcha\Helper\Data as Helper;
use Magento\Captcha\Model\DefaultModel;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides frontend with payments CAPTCHA configuration.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PaymentCaptchaConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Helper
     */
    private $captchaData;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Helper $captchaData
     * @param CustomerSession $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Helper $captchaData,
        CustomerSession $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->captchaData = $captchaData;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        /** @var DefaultModel $captchaModel */
        $captchaModel = $this->captchaData->getCaptcha(CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM);
        $login = null;
        if ($this->customerSession->isLoggedIn()) {
            $login = $this->customerSession->getCustomerData()->getEmail();
        }
        $required =  $captchaModel->isRequired($login);
        if ($required) {
            $captchaModel->generate();
            $imageSrc = $captchaModel->getImgSrc();
        } else {
            $imageSrc = '';
        }

        return [
            'captcha' => [
                CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM => [
                    'isCaseSensitive' => (bool)$captchaModel->isCaseSensitive(),
                    'imageHeight' => $captchaModel->getHeight(),
                    'imageSrc' => $imageSrc,
                    'refreshUrl' => $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]),
                    'isRequired' => $required,
                    'timestamp' => time()
                ]
            ]
        ];
    }
}
