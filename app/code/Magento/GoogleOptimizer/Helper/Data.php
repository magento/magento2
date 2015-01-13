<?php
/**
 * Google Optimizer Data Helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Xml path google experiments enabled
     */
    const XML_PATH_ENABLED = 'google/analytics/experiments';

    /**
     * @var bool
     */
    protected $_activeForCmsFlag;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\GoogleAnalytics\Helper\Data
     */
    protected $_analyticsHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleAnalytics\Helper\Data $analyticsHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleAnalytics\Helper\Data $analyticsHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_analyticsHelper = $analyticsHelper;
        parent::__construct($context);
    }

    /**
     * Checks if Google Experiment is enabled
     *
     * @param string $store
     * @return bool
     */
    public function isGoogleExperimentEnabled($store = null)
    {
        return (bool)$this->_scopeConfig->isSetFlag(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Checks if Google Experiment is active
     *
     * @param string $store
     * @return bool
     */
    public function isGoogleExperimentActive($store = null)
    {
        return $this->isGoogleExperimentEnabled($store) && $this->_analyticsHelper->isGoogleAnalyticsAvailable($store);
    }
}
