<?php
/**
 * Module Output Config Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * Checks the module configuration
 *
 * @deprecated because there are tools to change the module state in the admin panel and magento 2 CLI.
 */
class Config implements \Magento\Framework\Module\Output\ConfigInterface
{
    /**
     * XPath in the configuration where module statuses are stored
     */
    const XML_PATH_MODULE_OUTPUT_STATUS = 'advanced/modules_disable_output/%s';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string
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
     * @inheritdoc
     */
    public function isEnabled($moduleName)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isSetFlag($path)
    {
        return false;
    }
}
