<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Block\Captcha;

/**
 * Captcha block
 * @since 2.0.0
 */
class DefaultCaptcha extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'default.phtml';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_captcha;

    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.0.0
     */
    protected $_captchaData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Captcha\Helper\Data $captchaData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_captchaData = $captchaData;
    }

    /**
     * Returns template path
     *
     * @return string
     * @since 2.0.0
     */
    public function getTemplate()
    {
        return $this->getIsAjax() ? '' : $this->_template;
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     * @since 2.0.0
     */
    public function getRefreshUrl()
    {
        $store = $this->_storeManager->getStore();
        return $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]);
    }

    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->getCaptchaModel()->isRequired()) {
            $this->getCaptchaModel()->generate();
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Returns captcha model
     *
     * @return \Magento\Captcha\Model\CaptchaInterface
     * @since 2.0.0
     */
    public function getCaptchaModel()
    {
        return $this->_captchaData->getCaptcha($this->getFormId());
    }
}
