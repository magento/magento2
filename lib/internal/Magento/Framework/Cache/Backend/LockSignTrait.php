<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

/**
 * Shared generator for a unique per-process regeneration-lock signature.
 *
 * The signature (pid-host-random) identifies the owner of a stale-cache regeneration lock so a
 * process only ever releases a lock it still holds. Used by both the Symfony L2 backend and the
 * legacy RemoteSynchronizedCache so the two stacks derive ownership tokens identically.
 */
trait LockSignTrait
{
    /**
     * Generate a unique per-process lock signature (pid-host-random).
     *
     * @return string
     */
    private function generateLockSign(): string
    {
        $sign = implode('-', [getmypid(), crc32((string)gethostname())]);

        try {
            $sign .= '-' . bin2hex(random_bytes(4));
        } catch (\Exception $e) {
            $sign .= '-' . uniqid('-uniqid-');
        }

        return $sign;
    }
}
