<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Helper;

/**
 * Serializes access to the "downloadable_domains" whitelist in env.php across test processes.
 */
class DownloadableDomainLock
{
    private const LOCK_FILE_NAME = 'magento_downloadable_domains.lock';

    /**
     * Run $callback while holding an exclusive, cross-process lock.
     *
     * @param callable $callback
     * @return mixed
     */
    public static function synchronize(callable $callback)
    {
        $lockFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::LOCK_FILE_NAME;
        $handle = fopen($lockFilePath, 'c');
        if ($handle === false) {
            // Fall back to running unsynchronized rather than failing the test outright.
            return $callback();
        }

        try {
            flock($handle, LOCK_EX);
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
