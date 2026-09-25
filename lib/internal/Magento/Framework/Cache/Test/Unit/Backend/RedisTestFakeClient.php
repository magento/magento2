<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Redis;

/**
 * Minimal stand-in for Credis_Client covering only what Redis::load()/save()/remove() call
 * directly: a pipelined batch of hGet() calls flushed by exec(), and a standalone hGet() for the
 * non-preloaded path. Anything save()/remove() need beyond that (hMSet, multi, del, ...) is left
 * unimplemented on purpose, so those calls surface as an Error that the caller's own try/catch
 * around parent::save()/parent::remove() is expected to swallow.
 */
class RedisTestFakeClient
{
    /**
     * @var int
     */
    public int $pipelineCount = 0;

    /**
     * @var array<int, array<int, mixed>>
     */
    private array $queuedExecResults = [];

    /**
     * @var array<string, mixed>
     */
    private array $directResults = [];

    /**
     * @var bool
     */
    private bool $inPipeline = false;

    /**
     * @param array $result
     * @return void
     */
    public function queueExecResult(array $result): void
    {
        $this->queuedExecResults[] = $result;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function setDirectResult(string $id, $value): void
    {
        $this->directResults[$id] = $value;
    }

    /**
     * @return self
     */
    public function pipeline(): self
    {
        $this->pipelineCount++;
        $this->inPipeline = true;

        return $this;
    }

    /**
     * @param string $key
     * @return self|mixed
     */
    public function hGet(string $key)
    {
        if ($this->inPipeline) {
            // Queued: the actual value is returned positionally by exec(), not here.
            return $this;
        }

        $id = $this->stripKeyPrefix($key);

        return $this->directResults[$id] ?? false;
    }

    /**
     * @return array
     */
    public function exec(): array
    {
        $this->inPipeline = false;

        return array_shift($this->queuedExecResults) ?? [];
    }

    /**
     * @param string $key
     * @return string
     */
    private function stripKeyPrefix(string $key): string
    {
        return substr($key, strlen(Redis::PREFIX_KEY));
    }
}
