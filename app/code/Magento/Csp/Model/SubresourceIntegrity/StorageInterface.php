<?php
/**
 * Copyright 2025 Adobe
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
     * Loads SRI hashes from a storage.
     *
     * @param string|null $context
     *
     * @return string|null
     */
    public function load(?string $context): ?string;

    /**
     * Saves SRI hashes to a storage.
     *
     * @param string $data
     * @param string|null $context
     *
     * @return bool
     */
    public function save(string $data, ?string $context): bool;

    /**
     * Deletes all SRI hashes from a storage.
     *
     * @param string|null $context
     *
     * @return bool
     */
    public function remove(?string $context): bool;
}
