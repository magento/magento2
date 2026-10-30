<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/**
 * A Redis connection that routes read operations (get(), mget()) to a replica while keeping write
 * and other operations (SET, DEL, tag operations, Lua, pipelines) on the master.
 * Falls back to the master if the replica fails, matching legacy behavior.
 * Extends RedisBase for phpredis compatibility and safe compilation without the extension.
 */
class SlaveAwareRedis extends RedisBase
{
    /**
     * @var \Redis[]
     */
    private array $slaves = [];

    /**
     * Controls whether reads are served only by replicas or load-balanced with the master
     * ,mirroring Cm Redis's master_write_only option.
     *
     * @var bool
     */
    private bool $masterWriteOnly = false;

    /**
     * Controls whether replica misses are retried on the master, mirroring Cm Redis's retry_reads_on_master option.
     *
     * @var bool
     */
    private bool $retryReadsOnMaster = false;

    /**
     * Attach the read replicas (already-connected \Redis clients).
     *
     * @param \Redis[] $slaves
     * @return void
     */
    public function setSlaves(array $slaves): void
    {
        $this->slaves = array_values(array_filter($slaves, static fn($s): bool => $s instanceof \Redis));
    }

    /**
     * Set whether the master is write-only (excluded from read load-balancing).
     *
     * @param bool $masterWriteOnly
     * @return void
     */
    public function setMasterWriteOnly(bool $masterWriteOnly): void
    {
        $this->masterWriteOnly = $masterWriteOnly;
    }

    /**
     * Set whether a replica miss is retried on the master (legacy retry_reads_on_master).
     *
     * @param bool $retryReadsOnMaster
     * @return void
     */
    public function setRetryReadsOnMaster(bool $retryReadsOnMaster): void
    {
        $this->retryReadsOnMaster = $retryReadsOnMaster;
    }

    /**
     * Pick the client for the next read: a replica, or null to read from the master (this connection).
     *
     * - no replicas            -> master
     * - master_write_only=true -> a random replica
     * - master_write_only=false-> uniform pick over {master} ∪ replicas (legacy load-balancing intent)
     *
     * @return \Redis|null
     */
    private function pickReadClient(): ?\Redis
    {
        $count = count($this->slaves);
        if ($count === 0) {
            return null;
        }
        if ($this->masterWriteOnly) {
            return $count === 1 ? $this->slaves[0] : $this->slaves[array_rand($this->slaves)];
        }
        // Includes the master in read load-balancing, using fast non-cryptographic randomness like legacy Cm Redis.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $idx = mt_rand(0, $count);
        return $idx === 0 ? null : $this->slaves[$idx - 1];
    }

    /**
     * @inheritDoc
     *
     * Symfony's RedisTrait::doFetch fetches via mget(), not get(); this override is kept so a direct
     * get() (or a future Symfony that uses it) is still served from a replica, sharing mget()'s routing.
     */
    public function get($key): mixed
    {
        $slave = $this->pickReadClient();
        if ($slave !== null) {
            try {
                $value = $slave->get($key);
                if ($value !== false) {
                    return $value; // replica hit
                }
                if (!$this->retryReadsOnMaster) {
                    return false; // legacy default: a replica miss stays a miss
                }
                // retry_reads_on_master: replica miss (e.g. replication lag) -> read master
            } catch (\Throwable $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // replica error -> read master (more resilient than legacy, which rethrows non-LOADING)
            }
        }
        return parent::get($key);
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function mget($keys): \Redis|array|false
    {
        $slave = $this->pickReadClient();
        if ($slave === null) {
            return parent::mget($keys);
        }

        try {
            $values = $slave->mget($keys);
        } catch (\Throwable $e) {
            return parent::mget($keys);           // replica error -> master
        }
        if (!is_array($values)) {
            return parent::mget($keys);
        }
        if (!$this->retryReadsOnMaster) {
            return $values;                 // legacy default: replica misses stay misses (no back-fill)
        }

        // retry_reads_on_master: back-fill any replica misses from the master
        $keys = array_values($keys);
        $missingPos = [];
        foreach ($keys as $i => $k) {
            if (($values[$i] ?? false) === false) {
                $missingPos[$i] = $k;
            }
        }
        if ($missingPos) {
            try {
                $masterValues = parent::mget(array_values($missingPos));
                if (is_array($masterValues)) {
                    $j = 0;
                    foreach ($missingPos as $i => $k) {
                        $values[$i] = $masterValues[$j] ?? false;
                        $j++;
                    }
                }
            } catch (\Throwable $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // keep the replica results on master error
            }
        }
        return $values;
    }
}
