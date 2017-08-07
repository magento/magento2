<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Scope;

use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class \Magento\Framework\App\Scope\Source
 *
 * @since 2.1.0
 */
class Source implements ArrayInterface
{
    /**
     * @var ScopeResolverPool
     * @since 2.1.0
     */
    protected $scopeResolverPool;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $scope;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     * @param string $scope
     * @since 2.1.0
     */
    public function __construct(
        ScopeResolverPool $scopeResolverPool,
        $scope
    ) {
        $this->scopeResolverPool = $scopeResolverPool;
        $this->scope = $scope;
    }

    /**
     * Return array of scope names
     *
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        $scopes = $this->scopeResolverPool->get($this->scope)->getScopes();
        $array = [];
        foreach ($scopes as $scope) {
            $array[] = ['value' => $scope->getId(), 'label' => $scope->getName()];
        }
        return $array;
    }
}
