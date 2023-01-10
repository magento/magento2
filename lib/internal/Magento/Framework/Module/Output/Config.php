<?php
/**
 * Module Output Config Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
 * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
 * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
 * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
 * issues that will be addressed in future releases.
 */
class Config implements \Magento\Framework\Module\Output\ConfigInterface
{
    /**
     * XPath in the configuration where module statuses are stored
     * @deprecated 100.2.0
     */
    const XML_PATH_MODULE_OUTPUT_STATUS = 'advanced/modules_disable_output/%s';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated 101.0.0
     */
    protected $_scopeConfig;

    /**
     * @var string
     * @deprecated 101.0.0
     */
    protected $_storeType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $scopeType
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeType = $scopeType;
    }

    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     * @return boolean
     */
    public function isEnabled($moduleName)
    {
        return $this->isSetFlag(sprintf(self::XML_PATH_MODULE_OUTPUT_STATUS, $moduleName));
    }

    /**
     * Retrieve module enabled specific path
     *
     * @param string $path Fully-qualified config path
     * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     * @return boolean
     */
    public function isSetFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, $this->_storeType);
    }
}
