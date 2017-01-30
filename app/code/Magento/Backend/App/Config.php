<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Backend config accessor
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     */
    public function __construct(\Magento\Framework\App\Config\ScopePool $scopePool)
    {
        $this->_scopePool = $scopePool;
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->_scopePool->getScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)->getValue($path);
    }

    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setValue($path, $value)
    {
        $this->_scopePool->getScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)->setValue($path, $value);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @return bool
     */
    public function isSetFlag($path)
    {
        return !!$this->_scopePool->getScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)->getValue($path);
    }
}
