<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/**
 * A phpredis \Redis connection whose READ path is served from a read replica.
 *
 * The object is itself connected to the MASTER (Symfony's RedisAdapter builds it through the normal
 * connection factory), so every command runs on the master by default — writes (SET/SETEX/DEL),
 * tag-index ops (SADD/SREM/SMEMBERS), Lua (EVAL) and pipelines all stay on the master. The two data
 * FETCH commands — mget() and get() — are routed to a replica. In practice Symfony's
 * RedisTrait::doFetch fetches via mget(); get() is routed too (sharing the same pickReadClient logic)
 * so a direct get() — or a future Symfony that fetches single keys via get() — is offloaded
 * consistently rather than silently hitting the master. This mirrors legacy Cm_Cache_Backend_Redis,
 * which sends only the data load() to load_from_slave and keeps everything else on the master.
 *
 * On any replica error the read transparently falls back to the master, so a flaky/lagging replica
 * degrades to master-reads rather than failing (legacy skips a bad slave the same way).
 *
 * Extends RedisBase, which is \Redis when the phpredis extension is present (and a stub otherwise so
 * the file still loads/compiles on hosts without phpredis — this object is only ever instantiated on
 * the phpredis code path). See RedisBase for why the base class is indirected.
 */
class SlaveAwareRedis extends RedisBase
{
    /**
     * @var \Redis[]
     */
    private array $slaves = [];

    /**
     * When true, the master serves NO reads (all reads go to replicas). When false (legacy default),
     * the master participates in read load-balancing — a read has a ~1/(replicas+1) chance of being
     * served by the master. Mirrors Cm Redis's master_write_only option.
     *
     * @var bool
     */
    private bool $masterWriteOnly = false;

    /**
     * When true, a replica MISS (nil) is retried on the master. When false (legacy default), a replica
     * miss is returned as-is (treated as a cache miss). Mirrors Cm Redis's retry_reads_on_master.
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
        // include the master (index 0) in the pool so it takes a share of reads, like legacy default.
        // mt_rand (not the CSPRNG random_int) on purpose: this is read load-balancing, not a security
        // decision, and it runs on the hot read path — legacy Cm Redis likewise uses array_rand/mt.
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
                    return $value;                       // replica hit
                }
                if (!$this->retryReadsOnMaster) {
                    return false;                        // legacy default: a replica miss stays a miss
                }
                // retry_reads_on_master: replica miss (e.g. replication lag) -> read master
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Throwable $e) {
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
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Throwable $e) {
                // keep the replica results on master error
            }
        }
        return $values;
    }
}
