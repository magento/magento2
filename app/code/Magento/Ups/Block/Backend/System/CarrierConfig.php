<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Block\Backend\System;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;
use Magento\Ups\Helper\Config as ConfigHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Backend shipping UPS content block
 *
 * @api
 * @since 100.0.2
 */
class CarrierConfig extends Template
{
    /**
     * @var Website
     */
    protected $_websiteModel;

    /**
     * @param TemplateContext $context
     * @param ConfigHelper $carrierConfig
     * @param Website $websiteModel
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        TemplateContext $context,
        protected readonly ConfigHelper $carrierConfig,
        Website $websiteModel,
        array $data = [],
        ?JsonHelper $jsonHelper = null
    ) {
        $this->_websiteModel = $websiteModel;
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Get shipping model
     *
     * @return ConfigHelper
     */
    public function getCarrierConfig()
    {
        return $this->carrierConfig;
    }

    /**
     * Get website model
     *
     * @return Website
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
     */
    public function getConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }
}
