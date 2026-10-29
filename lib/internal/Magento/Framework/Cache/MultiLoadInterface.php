<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

/**
 * Capability interface for cache frontends that can load several ids in a single batched round-trip.
 *
 * Lets callers depend on the batch-load contract (via instanceof) instead of probing for the method.
 */
interface MultiLoadInterface
{
    /**
     * Load several cache ids at once, returning a map of id => value for the entries that exist.
     *
     * @param string[] $identifiers
     * @return array<string, mixed>
     */
    public function loadMultiple(array $identifiers): array;
}
