<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Interface \Magento\Framework\Search\Request\IndexScopeResolverInterface
 *
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
