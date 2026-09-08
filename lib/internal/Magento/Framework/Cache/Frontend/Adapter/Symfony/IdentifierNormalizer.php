<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/**
 * Canonicalizes a cache identifier to the single form used for Symfony cache keys.
 *
 * Single source of truth shared by the Symfony frontend adapter (Symfony::cleanIdentifier) and the
 * PreloadingSymfonyAdapter fast-path lookup, so the two cannot drift: a preload key configured in any
 * case/separator (e.g. "SYSTEM_DEFAULT:hash") resolves to the same slot as the runtime id the app
 * loads (e.g. "system_default:hash") — both become "SYSTEM_DEFAULT_HASH".
 */
class IdentifierNormalizer
{
    /**
     * Normalize an identifier: upper-case, "." -> "__", and any other non [A-Za-z0-9_] char -> "_".
     *
     * @param string $identifier
     * @return string
     */
    public static function normalize(string $identifier): string
    {
        $identifier = strtoupper($identifier);
        $identifier = str_replace('.', '__', $identifier);
        return (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $identifier);
    }
}
