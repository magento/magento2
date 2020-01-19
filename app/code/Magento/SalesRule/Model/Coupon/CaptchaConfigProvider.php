<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Captcha\Model\DefaultModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Captcha\Helper\Data as Helper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Adds captcha data related to coupons.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CaptchaConfigProvider implements ConfigProviderInterface
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
     * @param CustomerSession $session
     */
    public function __construct(StoreManagerInterface $storeManager, Helper $captchaData, CustomerSession $session)
    {
        $this->storeManager = $storeManager;
        $this->captchaData = $captchaData;
        $this->customerSession = $session;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $formId = 'sales_rule_coupon_request';
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        /** @var DefaultModel $captchaModel */
        $captchaModel = $this->captchaData->getCaptcha($formId);
        $login = '';
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
                $formId => [
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
