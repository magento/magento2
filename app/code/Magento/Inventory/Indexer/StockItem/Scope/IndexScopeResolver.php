<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem\Scope;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Implementation of IndexScopeResolverInterface which resolves index scope dynamically
 * depending on current scope state
 */
class IndexScopeResolver implements IndexScopeResolverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var State
     */
    private $scopeState;

    /**
     * Key is state name, value is corresponding responsible resolver object
     *
     * @var array
     */
    private $statesMap;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param State $scopeState
     * @param array $statesMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        State $scopeState,
        array $statesMap
    ) {
        $this->objectManager = $objectManager;
        $this->scopeState = $scopeState;
        $this->statesMap = $statesMap;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        /** @var IndexScopeResolverInterface $indexScopeResolver */
        $indexScopeResolver = $this->objectManager->get($this->statesMap[$this->scopeState->getState()]);

        return $indexScopeResolver->resolve($index, $dimensions);
    }
}
