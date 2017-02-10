<?php
/**
 * Module Output Config Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * Checks whether the module is enabled in the configuration.
 *
 * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
 */
class Config implements \Magento\Framework\Module\Output\ConfigInterface
{
    /**
     * XPath in the configuration where module statuses are stored
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    const XML_PATH_MODULE_OUTPUT_STATUS = 'advanced/modules_disable_output/%s';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    protected $_scopeConfig;

    /**
     * @var string
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
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
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return boolean
     */
    public function isEnabled($moduleName)
    {
        return false;
    }

    /**
     * Retrieve module enabled specific path
     *
     * @param string $path Fully-qualified config path
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return boolean
     */
    public function isSetFlag($path)
    {
        return false;
    }
}
