<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

/**
 * Valkey cache backend
 *
 * Valkey is a Redis-compatible in-memory store. This class extends the Redis backend
 * to leverage the same functionality while allowing Magento to treat Valkey as a separate backend.
 */
class Valkey extends Redis
{
    /**
     * Backend type identifier
     */
    public const BACKEND_TYPE = 'valkey';
}
