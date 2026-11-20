<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\HashResolver;

/**
 * Interface for resolving SRI hashes based on deployment strategy.
 *
 */
interface HashResolverInterface
{
    /**
     * Get all SRI hashes for the current context.
     *
     * Returns an associative array where keys are full URLs and values are hash strings.
     *
     * @return array<string, string>
     */
    public function getAllHashes(): array;

    /**
     * Get hash for a specific asset path.
     *
     * @param string $assetPath The asset path to look up
     * @return string|null The hash if found, null otherwise
     */
    public function getHashByPath(string $assetPath): ?string;
}
