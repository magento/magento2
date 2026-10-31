<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

/**
 * Capability contract for cache backends that can save or remove a data/hash key pair atomically.
 *
 * Lets callers depend on this contract (via instanceof) instead of coupling to a concrete
 * backend implementation.
 */
interface AtomicPairBackendInterface
{
    /**
     * Save two cache entries as a single atomic operation.
     *
     * The pair (e.g. a value and its paired version marker) is written atomically so a
     * concurrent read can never observe one written and not the other.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param mixed $specificLifetime
     * @param string $pairedData
     * @param string $pairedId
     * @param array $pairedTags
     * @param mixed $pairedSpecificLifetime
     * @return bool
     */
    public function saveAtomicPair(
        string $data,
        string $id,
        array $tags,
        $specificLifetime,
        string $pairedData,
        string $pairedId,
        array $pairedTags,
        $pairedSpecificLifetime
    ): bool;

    /**
     * Remove two cache entries as a single atomic operation.
     *
     * The pair (e.g. a value and its paired version marker) is removed atomically so a
     * concurrent read can never observe one removed and not the other.
     *
     * @param string $id
     * @param string $pairedId
     * @return bool
     */
    public function removeAtomicPair(string $id, string $pairedId): bool;
}
