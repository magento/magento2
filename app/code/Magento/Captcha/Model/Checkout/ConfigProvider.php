<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\Checkout;

/**
 * Class \Magento\Captcha\Model\Checkout\ConfigProvider
 *
 * @since 2.0.0
 */
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.0.0
     */
    protected $captchaData;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $formIds;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $formIds
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Captcha\Helper\Data $captchaData,
        array $formIds
    ) {
        $this->storeManager = $storeManager;
        $this->captchaData = $captchaData;
        $this->formIds = $formIds;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->formIds as $formId) {
            $config['captcha'][$formId] = [
                'isCaseSensitive' => $this->isCaseSensitive($formId),
                'imageHeight' => $this->getImageHeight($formId),
                'imageSrc' => $this->getImageSrc($formId),
                'refreshUrl' => $this->getRefreshUrl(),
                'isRequired' => $this->isRequired($formId)
            ];
        }
        return $config;
    }

    /**
     * Returns is captcha case sensitive
     *
     * @param string $formId
     * @return bool
     * @since 2.0.0
     */
    protected function isCaseSensitive($formId)
    {
        return (boolean)$this->getCaptchaModel($formId)->isCaseSensitive();
    }

    /**
     * Returns captcha image height
     *
     * @param string $formId
     * @return int
     * @since 2.0.0
     */
    protected function getImageHeight($formId)
    {
        return $this->getCaptchaModel($formId)->getHeight();
    }

    /**
     * Returns captcha image source path
     *
     * @param string $formId
     * @return string
     * @since 2.0.0
     */
    protected function getImageSrc($formId)
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
     * @since 2.0.0
     */
    protected function getRefreshUrl()
    {
        $store = $this->storeManager->getStore();
        return $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]);
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param string $formId
     * @return bool
     * @since 2.0.0
     */
    protected function isRequired($formId)
    {
        return (boolean)$this->getCaptchaModel($formId)->isRequired();
    }

    /**
     * Return captcha model for specified form
     *
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     * @since 2.0.0
     */
    protected function getCaptchaModel($formId)
    {
        return $this->captchaData->getCaptcha($formId);
    }
}
