<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Interface \Magento\Framework\Search\Request\IndexScopeResolverInterface
 *
 * @since 2.0.0
 */
interface IndexScopeResolverInterface
{
    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     * @since 2.0.0
     */
    public function resolve($index, array $dimensions);
}
