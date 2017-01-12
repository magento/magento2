<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

class ScopeResolverPool
{
    /**
     * @var array
     */
    protected $_scopeResolvers = [];

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface[] $scopeResolvers
     */
    public function __construct(
        array $scopeResolvers
    ) {
        $this->_scopeResolvers = $scopeResolvers;
    }

    /**
     * Retrieve reader by scope type
     *
     * @param string $scopeType
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\ScopeResolverInterface
     */
    public function get($scopeType)
    {
        if (!isset($this->_scopeResolvers[$scopeType]) ||
            !($this->_scopeResolvers[$scopeType] instanceof \Magento\Framework\App\ScopeResolverInterface)
        ) {
            throw new \InvalidArgumentException("Invalid scope type '{$scopeType}'");
        }
        return $this->_scopeResolvers[$scopeType];
    }
}
