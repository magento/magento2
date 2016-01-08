<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block\Paypal;

use Magento\Framework\View\Element\Template;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\BraintreeTwo\Gateway\Config\PayPal\Config;

/**
 * Class Component
 */
class Component extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->config->isActive() && $this->config->isDisplayShoppingCart();
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }
}
