<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

/**
 * Provider of scope resolvers by type
 * @since 2.0.0
 */
class ScopeResolverPool
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_scopeResolvers = [];

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface[] $scopeResolvers
     * @since 2.0.0
     */
    public function __construct(
        array $scopeResolvers = []
    ) {
        $this->_scopeResolvers = $scopeResolvers;
    }

    /**
     * Retrieve reader by scope type
     *
     * @param string $scopeType
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\ScopeResolverInterface
     * @since 2.0.0
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
