<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Grid;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\FlagManager;

/**
 * Storage for last grid update time.
 */
class LastUpdateTimeCache
{
    /**
     * Prefix for storage key.
     */
    private const STORAGE_KEY_PREFIX = 'LAST_GRID_UPDATE_TIME';

    /**
     * @var CacheInterface
     */
    private $cache;

    /** @var FlagManager */
    private $flagManager;

    /**
     * @param CacheInterface $cache
     * @param FlagManager|null $flagManager
     */
    public function __construct(
        CacheInterface $cache,
        ?FlagManager $flagManager = null
    ) {
        $this->cache = $cache;
        $this->flagManager = $flagManager ?? \Magento\Framework\App\ObjectManager::getInstance()
            ->get(FlagManager::class);
    }

    /**
     * Save last grid update time.
     *
     * @param string $gridTableName
     * @param string $lastUpdatedAt
     * @return void
     */
    public function save(string $gridTableName, string $lastUpdatedAt): void
    {
        $this->flagManager->saveFlag(
            $this->getFlagKey($gridTableName),
            $lastUpdatedAt
        );
    }

    /**
     * Get last grid update time.
     *
     * @param string $gridTableName
     * @return string|null
     */
    public function get(string $gridTableName): ?string
    {
        $lastUpdatedAt = $this->flagManager->getFlagData($this->getFlagKey($gridTableName));
        return $lastUpdatedAt ?: null;
    }

    /**
     * Remove last grid update time.
     *
     * @param string $gridTableName
     * @return void
     */
    public function remove(string $gridTableName): void
    {
        $this->flagManager->deleteFlag($this->getFlagKey($gridTableName));
    }

    /**
     * Generate cache key.
     *
     * @param string $gridTableName
     * @return string
     */
    private function getFlagKey(string $gridTableName): string
    {
        return self::STORAGE_KEY_PREFIX . ':' . $gridTableName;
    }
}
