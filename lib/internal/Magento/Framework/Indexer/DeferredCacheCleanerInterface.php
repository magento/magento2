<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

interface DeferredCacheCleanerInterface
{
    /**
     * Defer cache cleaning until flush() is called
     *
     * @see flush()
     */
    public function start(): void;

    /**
     * Flush cache
     */
    public function flush(): void;
}
