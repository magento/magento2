<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache;

use Magento\Framework\App;

/**
 * Cache status manager
 */
class Manager
{
    /**
     * Cache types list
     *
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Cache state service
     *
     * @var StateInterface
     */
    private $cacheState;

    /**
     * Cache types pool
     *
     * @var Type\FrontendPool
     */
    private $pool;

    /**
     * Constructor
     *
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Type\FrontendPool $pool
     */
    public function __construct(
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Type\FrontendPool $pool
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheState = $cacheState;
        $this->pool = $pool;
    }

    /**
     * Updates cache status for the requested types
     *
     * @param string[] $types
     * @param bool $isEnabled
     * @return array List of types with changed status
     */
    public function setEnabled(array $types, $isEnabled)
    {
        $changedStatusTypes = [];
        $isUpdated = false;
        foreach ($types as $type) {
            if ($this->cacheState->isEnabled($type) === $isEnabled) { // no need to poke it, if is not going to change
                continue;
            }
            $this->cacheState->setEnabled($type, $isEnabled);
            $isUpdated = true;
            $changedStatusTypes[] = $type;
        }
        if ($isUpdated) {
            $this->cacheState->persist();
        }
        return $changedStatusTypes;
    }

    /**
     * Cleans up caches
     *
     * @param array $types
     * @return void
     */
    public function clean(array $types)
    {
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
    }

    /**
     * Flushes specified cache storages
     *
     * @param string[] $types
     * @return void
     */
    public function flush(array $types)
    {
        $flushedBackend = [];
        foreach ($types as $type) {
            $backend = $this->pool->get($type)->getBackend();
            if (in_array($backend, $flushedBackend, true)) { // it was already flushed from another frontend
                continue;
            }
            $backend->clean();
            $flushedBackend[] = $backend;
        }
    }

    /**
     * Presents summary about cache status
     *
     * @return array
     */
    public function getStatus()
    {
        $result = [];
        foreach ($this->cacheTypeList->getTypes() as $type) {
            $result[$type['id']] = $type['status'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        $result = [];
        foreach ($this->cacheTypeList->getTypes() as $type) {
            $result[] = $type['id'];
        }
        return $result;
    }
}
