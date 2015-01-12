<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class Config implements \Magento\Framework\App\Config\ScopeConfigInterface
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';

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
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue(
        $path = null,
        $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->_scopePool->getScope($scope, $scopeCode)->getValue($path);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeCode = null)
    {
        return (bool) $this->getValue($path, $scope, $scopeCode);
    }
}
