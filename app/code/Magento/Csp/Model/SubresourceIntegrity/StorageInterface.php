<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

/**
 * Interface for an SRI hashes storage.
 */
interface StorageInterface
{
    /**
     * Loads SRI hashes from storage.
     *
     * Returns a serialized JSON string of hashes, or null when no hashes exist
     * or the storage cannot be read. Null and an empty hash set are distinct:
     * null means "nothing stored or unreadable"; an empty JSON object means
     * the storage exists but contains no entries.
     *
     * @param string|null $context
     * @return string|null
     */
    public function load(?string $context): ?string;

    /**
     * Saves SRI hashes to storage.
     *
     * Returns true only when the input is valid and every write succeeded.
     * Returns false when the input cannot be deserialized or any write fails.
     * Write failures are logged as warnings; check logs for disk-level errors.
     *
     * @param string $data
     * @param string|null $context
     * @return bool
     */
    public function save(string $data, ?string $context): bool;

    /**
     * Deletes all SRI hashes from storage.
     *
     * @param string|null $context
     * @return bool
     */
    public function remove(?string $context): bool;
}
