<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\ScopeResolver;


use Magento\Framework\App\Resource;
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
     * @param Resource|Resource $resource
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
                    $tableNameParts[] = $dimension->getName() .
                        $this->scopeResolver->getScope($dimension->getValue())->getId();
                    break;
                default:
                    $tableNameParts[] = $dimension->getName() . $dimension->getValue();
            }
        }
        return $this->resource->getTableName(implode('_', $tableNameParts));
    }
}
