<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\ScopeResolver;


use Magento\Framework\App\Resource;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

class IndexScopeResolver implements IndexScopeResolverInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
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
            $tableNameParts[] = $dimension->getName() . $dimension->getValue();
        }
        return $this->resource->getTableName(implode('_', $tableNameParts));
    }
}
