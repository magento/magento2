<?php
/**
 * Google Optimizer Data Helper
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_GoogleOptimizer_Helper_Data extends Mage_Core_Helper_Abstract
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
     * @var Mage_Core_Model_Store_ConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var Mage_GoogleAnalytics_Helper_Data
     */
    protected $_analyticsHelper;

    /**
     * @param Mage_Core_Helper_Context $context
     * @param Mage_Core_Model_Store_ConfigInterface $storeConfig
     * @param Mage_GoogleAnalytics_Helper_Data $analyticsHelper
     */
    public function __construct(
        Mage_Core_Helper_Context $context,
        Mage_Core_Model_Store_ConfigInterface $storeConfig,
        Mage_GoogleAnalytics_Helper_Data $analyticsHelper
    ) {
        $this->_storeConfig = $storeConfig;
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
        return (bool)$this->_storeConfig->getConfigFlag(self::XML_PATH_ENABLED, $store);
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
