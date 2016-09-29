<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Data;

class Scoped extends \Magento\Framework\Config\Data
{
    /**
     * Configuration scope resolver
     *
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * Scope priority loading scheme
     *
     * @var string[]
     */
    protected $_scopePriorityScheme = [];

    /**
     * Loaded scopes
     *
     * @var array
     */
    protected $_loadedScopes = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId
    ) {
        $this->_configScope = $configScope;
        
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Load data for current scope
     *
     * @return void
     */
    protected function initData()
    {
        $scope = $this->_configScope->getCurrentScope();
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false == isset($this->_loadedScopes[$scopeCode])) {
                    if ($scopeCode !== 'primary' && ($data = $this->_cache->load($scopeCode . '::' . $this->_cacheId))
                    ) {
                        $data = unserialize($data);
                    } else {
                        $data = $this->_reader->read($scopeCode);
                        if ($scopeCode !== 'primary') {
                            $this->_cache->save(serialize($data), $scopeCode . '::' . $this->_cacheId);
                        }
                    }
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode == $scope) {
                    break;
                }
            }
        }
    }
}
