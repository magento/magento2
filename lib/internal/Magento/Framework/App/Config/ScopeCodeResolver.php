<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverPool;

/**
 * Class for resolving scope code
 */
class ScopeCodeResolver
{
    /**
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * @var array
     */
    private $resolvedScopeCodes = [];

    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * Resolve scope code
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function resolve($scopeType, $scopeCode)
    {
        if (isset($this->resolvedScopeCodes[$scopeType][$scopeCode])) {
            return $this->resolvedScopeCodes[$scopeType][$scopeCode];
        }

        if ($scopeType !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopeResolver = $this->scopeResolverPool->get($scopeType);
            $resolverScopeCode = $scopeResolver->getScope($scopeCode);
        } else {
            $resolverScopeCode = $scopeCode;
        }

        if ($resolverScopeCode instanceof ScopeInterface) {
            $resolverScopeCode = $resolverScopeCode->getCode();
        }

        if ($scopeCode === null) {
            $scopeCode = $resolverScopeCode;
        }

        $this->resolvedScopeCodes[$scopeType][$scopeCode] = $resolverScopeCode;

        return $resolverScopeCode;
    }

    /**
     * Clean resolvedScopeCodes, store codes may have been renamed
     *
     * @return void
     */
    public function clean()
    {
        $this->resolvedScopeCodes = [];
    }
}
