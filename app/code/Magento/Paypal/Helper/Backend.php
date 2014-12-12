<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Helper;

/**
 * Paypal Backend helper
 */
class Backend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Backend\Model\Config
     */
    protected $_backendConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Backend\Model\Config $backendConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Backend\Model\Config $backendConfig
    ) {
        parent::__construct($context);
        $this->_coreHelper = $coreHelper;
        $this->_backendConfig = $backendConfig;
    }

    /**
     * Get selected merchant country code in system configuration
     *
     * @return string
     */
    public function getConfigurationCountryCode()
    {
        $countryCode  = $this->_request->getParam(\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY);
        if (is_null($countryCode) || preg_match('/^[a-zA-Z]{2}$/', $countryCode) == 0) {
            $countryCode = $this->_backendConfig->getConfigDataValue(
                \Magento\Paypal\Block\Adminhtml\System\Config\Field\Country::FIELD_CONFIG_PATH
            );
        }
        if (empty($countryCode)) {
            $countryCode = $this->_coreHelper->getDefaultCountry();
        }
        return $countryCode;
    }
}
