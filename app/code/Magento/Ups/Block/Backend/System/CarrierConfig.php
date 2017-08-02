<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Block\Backend\System;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Store\Model\Website;
use Magento\Ups\Helper\Config as ConfigHelper;

/**
 * Backend shipping UPS content block
 *
 * @api
 * @since 2.0.0
 */
class CarrierConfig extends Template
{
    /**
     * Shipping carrier config
     *
     * @var \Magento\Ups\Helper\Config
     * @since 2.0.0
     */
    protected $carrierConfig;

    /**
     * @var \Magento\Store\Model\Website
     * @since 2.0.0
     */
    protected $_websiteModel;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Ups\Helper\Config $carrierConfig
     * @param \Magento\Store\Model\Website $websiteModel
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        TemplateContext $context,
        ConfigHelper $carrierConfig,
        Website $websiteModel,
        array $data = []
    ) {
        $this->carrierConfig = $carrierConfig;
        $this->_websiteModel = $websiteModel;
        parent::__construct($context, $data);
    }

    /**
     * Get shipping model
     *
     * @return \Magento\Ups\Helper\Config
     * @since 2.0.0
     */
    public function getCarrierConfig()
    {
        return $this->carrierConfig;
    }

    /**
     * Get website model
     *
     * @return \Magento\Store\Model\Website
     * @since 2.0.0
     */
    public function getWebsiteModel()
    {
        return $this->_websiteModel;
    }

    /**
     * Get store config
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     * @since 2.0.0
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
