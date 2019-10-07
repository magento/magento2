<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache;

/**
 * In memory cache state
 *
 * Used to ease testing of cache state modifications
 */
class InMemoryState implements StateInterface
{
    /** @var bool[] */
    private $runtimeState = [];

    /** @var bool[] */
    private $persistedState = [];

    /**
     * InMemoryState constructor.
     * @param array $persistedState
     */
    public function __construct(array $persistedState = [])
    {
        $this->persistedState = $persistedState;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled($cacheType)
    {
        return $this->runtimeState[$cacheType]
            ?? $this->persistedState[$cacheType]
            ?? false;
    }

    /**
     * @inheritDoc
     */
    public function setEnabled($cacheType, $isEnabled)
    {
        $this->runtimeState[$cacheType] = $isEnabled;
    }

    /**
     * @inheritDoc
     */
    public function persist()
    {
        $this->persistedState = $this->runtimeState + $this->persistedState;
        $this->runtimeState = [];
    }

    /**
     * Creates new instance with persistent state updated values
     *
     * @param bool[] $state
     * @return self
     */
    public function withPersistedState(array $state): self
    {
        $newState = new self();
        $newState->persistedState = $state + $this->persistedState;
        return $newState;
    }
}
