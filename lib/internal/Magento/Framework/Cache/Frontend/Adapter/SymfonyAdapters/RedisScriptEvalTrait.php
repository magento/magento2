<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\OptimizedPredisClient;
use Predis\Client as PredisClient;

/**
 * Shared Redis Lua-script execution helpers: phpredis/Predis argument-order normalization plus a
 * per-instance SHA cache so hot paths use EVALSHA (SCRIPT LOAD once) instead of re-sending the full
 * script body on every call.
 *
 * The using class must provide a `$redis` property (phpredis \Redis/\RedisCluster or a Predis client).
 */
trait RedisScriptEvalTrait
{
    /**
     * Cached SCRIPT LOAD SHAs, keyed by a hash of the script body, so hot paths use EVALSHA instead
     * of resending the full script over the wire on each call.
     *
     * @var array<string, string>
     */
    private array $scriptShas = [];

    /**
     * Whether the underlying connection is a Predis client (vs the phpredis extension).
     *
     * @return bool
     */
    private function isPredisClient(): bool
    {
        return $this->redis instanceof PredisClient || $this->redis instanceof OptimizedPredisClient;
    }

    /**
     * Convert Predis error replies into exceptions for consistent phpredis and Predis fallbacks.
     *
     * @param mixed $result
     * @return mixed
     */
    private function unwrapPredisReply($result)
    {
        if ($result instanceof \Predis\Response\ErrorInterface) {
            throw new \RuntimeException((string)$result->getMessage());
        }
        return $result;
    }

    /**
     * Run a Lua script, normalizing the phpredis vs Predis EVAL argument order.
     *
     * Phpredis: eval($script, $keysAndArgs, $numKeys); Predis: eval($script, $numKeys, ...$keysAndArgs).
     *
     * @param string $script
     * @param array $keysAndArgs Flat list: the $numKeys KEYS first, then the ARGV values
     * @param int $numKeys
     * @return mixed
     */
    private function evalScript(string $script, array $keysAndArgs, int $numKeys)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->eval($script, $numKeys, ...$keysAndArgs));
        }
        return $this->redis->eval($script, $keysAndArgs, $numKeys);
    }

    /**
     * Run a cached Lua script by SHA, normalizing the phpredis vs Predis EVALSHA argument order.
     *
     * @param string $sha
     * @param array $keysAndArgs Flat list: the $numKeys KEYS first, then the ARGV values
     * @param int $numKeys
     * @return mixed
     */
    private function evalShaScript(string $sha, array $keysAndArgs, int $numKeys)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->evalsha($sha, $numKeys, ...$keysAndArgs));
        }
        return $this->redis->evalSha($sha, $keysAndArgs, $numKeys);
    }

    /**
     * SCRIPT LOAD a Lua script and return its SHA, normalizing the Predis error reply.
     *
     * @param string $script
     * @return mixed SHA string
     */
    private function scriptLoad(string $script)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->script('load', $script));
        }
        return $this->redis->script('load', $script);
    }

    /**
     * Load a Lua script (cached) and return its SHA1, so callers can EVALSHA it.
     *
     * @param string $script Lua script content
     * @return string SHA1 of the script
     * @throws \RuntimeException
     */
    private function loadLuaScript(string $script): string
    {
        $hash = hash('sha256', $script);
        if (isset($this->scriptShas[$hash])) {
            return $this->scriptShas[$hash];
        }

        try {
            $sha = (string)$this->scriptLoad($script);
            $this->scriptShas[$hash] = $sha;
            return $sha;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to load Lua script: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clear all cached script SHAs (call after a Redis SCRIPT FLUSH).
     *
     * @return void
     */
    public function clearScriptCache(): void
    {
        $this->scriptShas = [];
    }
}
