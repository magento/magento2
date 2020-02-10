<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalCaptcha\Model\Checkout;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\CaptchaInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Configuration provider for Captcha rendering.
 */
class ConfigProviderPayPal implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $captchaData;

    /**
     * @var string
     */
    private static $formId = 'co-payment-form';

    /**
     * @param StoreManagerInterface $storeManager
     * @param Data $captchaData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Data $captchaData
    ) {
        $this->storeManager = $storeManager;
        $this->captchaData = $captchaData;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $config['captchaPayments'][self::$formId] = [
            'isCaseSensitive' => $this->isCaseSensitive(self::$formId),
            'imageHeight' => $this->getImageHeight(self::$formId),
            'imageSrc' => $this->getImageSrc(self::$formId),
            'refreshUrl' => $this->getRefreshUrl(),
            'isRequired' => $this->isRequired(self::$formId),
            'timestamp' => time()
        ];

        return $config;
    }

    /**
     * Returns is captcha case sensitive
     *
     * @param string $formId
     * @return bool
     */
    private function isCaseSensitive(string $formId): bool
    {
        return (bool)$this->getCaptchaModel($formId)->isCaseSensitive();
    }

    /**
     * Returns captcha image height
     *
     * @param string $formId
     * @return int
     */
    private function getImageHeight(string $formId): int
    {
        return (int)$this->getCaptchaModel($formId)->getHeight();
    }

    /**
     * Returns captcha image source path
     *
     * @param string $formId
     * @return string
     */
    private function getImageSrc(string $formId): string
    {
        if ($this->isRequired($formId)) {
            $captcha = $this->getCaptchaModel($formId);
            $captcha->generate();
            return $captcha->getImgSrc();
        }

        return '';
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    private function getRefreshUrl(): string
    {
        $store = $this->storeManager->getStore();
        return $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]);
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param string $formId
     * @return bool
     */
    private function isRequired(string $formId): bool
    {
        return (bool)$this->getCaptchaModel($formId)->isRequired();
    }

    /**
     * Return captcha model for specified form
     *
     * @param string $formId
     * @return CaptchaInterface
     */
    private function getCaptchaModel(string $formId): CaptchaInterface
    {
        return $this->captchaData->getCaptcha($formId);
    }
}
