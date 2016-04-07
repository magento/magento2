<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\ScopeResolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

class IndexScopeResolver implements IndexScopeResolverInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        $tableNameParts = [$index];
        foreach ($dimensions as $dimension) {
            switch ($dimension->getName()) {
                case 'scope':
                    $tableNameParts[] = $dimension->getName() . $this->getScopeId($dimension);
                    break;
                default:
                    $tableNameParts[] = $dimension->getName() . $dimension->getValue();
            }
        }
        return $this->resource->getTableName(implode('_', $tableNameParts));
    }

    /**
     * Get scope id by code
     *
     * @param Dimension $dimension
     * @return int
     */
    private function getScopeId($dimension)
    {
        if (is_numeric($dimension->getValue())) {
            return $dimension->getValue();
        } else {
            return $this->scopeResolver->getScope($dimension->getValue())->getId();
        }
    }
}
