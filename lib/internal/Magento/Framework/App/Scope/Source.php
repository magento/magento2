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
 */
class Source implements ArrayInterface
{
    /**
     * @var ScopeResolverPool
     */
    protected $scopeResolverPool;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     * @param string $scope
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
