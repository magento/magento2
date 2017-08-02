<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Block\Adminhtml\Captcha;

/**
 * Captcha block for adminhtml area
 * @since 2.0.0
 */
class DefaultCaptcha extends \Magento\Captcha\Block\Captcha\DefaultCaptcha
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\App\ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $captchaData, $data);
        $this->_url = $url;
        $this->_config = $config;
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     * @since 2.0.0
     */
    public function getRefreshUrl()
    {
        return $this->_url->getUrl(
            'adminhtml/refresh/refresh',
            ['_secure' => $this->_config->isSetFlag('web/secure/use_in_adminhtml'), '_nosecret' => true]
        );
    }
}
