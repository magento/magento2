<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\ScopeResolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
 *
 * @since 2.0.0
 */
class IndexScopeResolver implements IndexScopeResolverInterface
{
    /**
     * @var Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     * @since 2.0.0
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getScopeId($dimension)
    {
        $scopeId = $dimension->getValue();

        if (!is_numeric($scopeId)) {
            $scopeId = $this->scopeResolver->getScope($scopeId)->getId();
        }

        return $scopeId;
    }
}
