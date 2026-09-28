<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

/**
 * Logging requests to Valkey
 *
 * Valkey is a Redis-compatible in-memory store. This class extends the Redis request logger
 * to leverage the same functionality while allowing to treat Valkey as a separate backend.
 */
class ValkeyRequestLogger extends RedisRequestLogger
{
}
