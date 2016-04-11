<?php
/**
 * Google Optimizer Data Helper
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleOptimizer\Helper;

use \Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\GoogleAnalytics\Helper\Data
     */
    protected $_analyticsHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\GoogleAnalytics\Helper\Data $analyticsHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\GoogleAnalytics\Helper\Data $analyticsHelper
    ) {
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
        return (bool)$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
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
