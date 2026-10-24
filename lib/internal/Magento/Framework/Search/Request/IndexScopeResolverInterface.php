<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Request;

/**
 * Resolve table name by provided dimensions. Scope Resolver must accept all dimensions that potentially can be used to
 * resolve table name, but certain implementation can filter them if needed
 *
 * @api
 */
interface IndexScopeResolverInterface
{
    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions);
}
