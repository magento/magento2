<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

/**
 * Capability interface for cache backends that can flush their entire store in one direct operation.
 *
 * The legacy Zend_Cache_Backend_Interface only exposes clean(); PSR-6/Symfony-backed stores can wipe
 * the whole pool far more cheaply via clear(). Callers depend on this contract (via instanceof)
 * instead of probing for the method, and fall back to clean(CLEANING_MODE_ALL) when it is absent.
 */
interface ClearableInterface
{
    /**
     * Remove every entry from the backing store.
     *
     * @return bool
     */
    public function clear(): bool;
}
