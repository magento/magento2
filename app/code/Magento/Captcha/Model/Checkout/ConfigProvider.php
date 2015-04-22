<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\Checkout;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const FORM_ID = 'user_login';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $captchaData;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Captcha\Helper\Data $captchaData
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Captcha\Helper\Data $captchaData
    ) {
        $this->storeManager = $storeManager;
        $this->captchaData = $captchaData;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'captchaIsCaseSensitive' => $this->isCaseSensitive(),
            'captchaImageHeight' => $this->getImageHeight(),
            'captchaImageSrc' => $this->getImageSrc(),
            'captchaRefreshUrl' => $this->getRefreshUrl(),
            'captchaIsRequired' => $this->getIsRequired(),
            'captchaFormId' => self::FORM_ID
        ];
    }

    /**
     * Returns is captcha case sensitive
     *
     * @return bool
     */
    protected function isCaseSensitive()
    {
        return (boolean)$this->getCaptchaModel()->isCaseSensitive();
    }

    /**
     * Returns captcha image height
     *
     * @return int
     */
    protected function getImageHeight()
    {
        return $this->getCaptchaModel()->getHeight();
    }

    /**
     * Returns captcha image source path
     *
     * @return string
     */
    protected function getImageSrc()
    {
        $captcha = $this->getCaptchaModel();
        $captcha->generate();
        return $captcha->getImgSrc();
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    protected function getRefreshUrl()
    {
        $store = $this->storeManager->getStore();
        return $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]);
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @return bool
     */
    protected function getIsRequired()
    {
        return (boolean)$this->getCaptchaModel()->isRequired();
    }

    /**
     * @return \Magento\Captcha\Model\ModelInterface
     */
    protected function getCaptchaModel()
    {
        return $this->captchaData->getCaptcha(self::FORM_ID);
    }
}
