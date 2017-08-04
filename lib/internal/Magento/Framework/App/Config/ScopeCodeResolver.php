<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\ScopeResolverPool;

/**
 * Class for resolving scope code
 * @since 2.1.3
 */
class ScopeCodeResolver
{
    /**
     * @var ScopeResolverPool
     * @since 2.1.3
     */
    private $scopeResolverPool;

    /**
     * @var array
     * @since 2.1.3
     */
    private $resolvedScopeCodes = [];

    /**
     * @param ScopeResolverPool $scopeResolverPool
     * @since 2.1.3
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * Resolve scope code
     *
     * @param string $scopeType
     * @param string $scopeCode
     * @return string
     * @since 2.1.3
     */
    public function resolve($scopeType, $scopeCode)
    {
        if (isset($this->resolvedScopeCodes[$scopeType][$scopeCode])) {
            return $this->resolvedScopeCodes[$scopeType][$scopeCode];
        }
        if (($scopeCode === null || is_numeric($scopeCode))
            && $scopeType !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ) {
            $scopeResolver = $this->scopeResolverPool->get($scopeType);
            $resolverScopeCode = $scopeResolver->getScope($scopeCode);
        } else {
            $resolverScopeCode = $scopeCode;
        }

        if ($resolverScopeCode instanceof \Magento\Framework\App\ScopeInterface) {
            $resolverScopeCode = $resolverScopeCode->getCode();
        }

        $this->resolvedScopeCodes[$scopeType][$scopeCode] = $resolverScopeCode;
        return $resolverScopeCode;
    }

    /**
     * Clean resolvedScopeCodes, store codes may have been renamed
     *
     * @return void
     * @since 2.2.0
     */
    public function clean()
    {
        $this->resolvedScopeCodes = [];
    }
}
