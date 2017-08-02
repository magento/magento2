<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Scope;

use Magento\Framework\Search\Request\Dimension;

/**
 * Implementation of IndexScopeResolverInterface which resolves index scope dynamically
 * depending on current scope state
 * @since 2.2.0
 */
class ScopeProxy implements \Magento\Framework\Search\Request\IndexScopeResolverInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var array
     * @since 2.2.0
     */
    private $states = [];

    /**
     * @var State
     * @since 2.2.0
     */
    private $scopeState;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param State $scopeState
     * @param array $states
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        State $scopeState,
        array $states
    ) {
        $this->objectManager = $objectManager;
        $this->scopeState = $scopeState;
        $this->states = $states;
    }

    /**
     * Creates class instance with specified parameters
     *
     * @param string $state
     * @return \Magento\Framework\Search\Request\IndexScopeResolverInterface
     * @throws UnknownStateException
     * @since 2.2.0
     */
    private function create($state)
    {
        if (!array_key_exists($state, $this->states)) {
            throw new UnknownStateException(__("Requested resolver for unknown indexer state: $state"));
        }
        return $this->objectManager->create($this->states[$state]);
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     * @since 2.2.0
     */
    public function resolve($index, array $dimensions)
    {
        return $this->create($this->scopeState->getState())->resolve($index, $dimensions);
    }
}
