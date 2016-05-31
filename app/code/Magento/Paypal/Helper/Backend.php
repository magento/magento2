<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Paypal Backend helper
 */
class Backend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $backendConfig;

    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     */
    protected $scopeDefiner;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
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
