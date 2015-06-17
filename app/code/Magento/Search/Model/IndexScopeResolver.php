<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;


use Magento\Framework\App\Resource;
use Magento\Framework\App\ScopeResolverInterface;

class IndexScopeResolver
{
    const SCOPE_DEFAULT = 'default';
    const SCOPE_PREFIX = 'index';
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;
    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @param Resource $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Resource $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param string $indexName
     * @param null|int $scopeId
     * @return string
     */
    public function resolve($indexName, $scopeId = null)
    {
        // TODO: Change comparison to ($scopeId === null) when table creation mechanism would be implemented
        $scope = true ?
            self::SCOPE_DEFAULT
            : $this->scopeResolver->getScope($scopeId)->getId();
        $suffix = self::SCOPE_PREFIX . '_' . $scope;
        return $this->resource->getTableName([$indexName, $suffix]);
    }
}
