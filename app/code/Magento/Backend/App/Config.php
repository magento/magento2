<?php
/**
 * Default application path for backend area
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

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
        return $this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->getValue($path);
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
        $this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->setValue($path, $value);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @return bool
     */
    public function isSetFlag($path)
    {
        return !!$this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->getValue($path);
    }
}
