<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Paypal Backend helper
 * @since 2.0.0
 */
class Backend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Config\Model\Config
     * @since 2.0.0
     */
    protected $backendConfig;

    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     * @since 2.0.0
     */
    protected $scopeDefiner;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Config\Model\Config $backendConfig,
        \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
    ) {
        parent::__construct($context);
        $this->directoryHelper = $directoryHelper;
        $this->backendConfig = $backendConfig;
        $this->scopeDefiner = $scopeDefiner;
    }

    /**
     * Get selected merchant country code in system configuration
     *
     * @return string
     * @since 2.0.0
     */
    public function getConfigurationCountryCode()
    {
        $countryCode  = $this->_request->getParam(\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY);
        if ($countryCode === null || preg_match('/^[a-zA-Z]{2}$/', $countryCode) == 0) {
            $scope = $this->scopeDefiner->getScope();
            if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $this->backendConfig->setData($scope, $this->_request->getParam($scope));
            }
            $countryCode = $this->backendConfig->getConfigDataValue(
                \Magento\Paypal\Block\Adminhtml\System\Config\Field\Country::FIELD_CONFIG_PATH
            );
        }
        if (empty($countryCode)) {
            $countryCode = $this->directoryHelper->getDefaultCountry();
        }
        return $countryCode;
    }
}
