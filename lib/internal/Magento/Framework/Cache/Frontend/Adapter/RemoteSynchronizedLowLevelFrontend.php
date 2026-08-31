<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\LowLevelFrontendInterface;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Low-level frontend wrapper for a remote-synchronized Symfony frontend.
 */
class RemoteSynchronizedLowLevelFrontend implements LowLevelFrontendInterface
{
    /**
     * @param FrontendInterface $frontend
     */
    public function __construct(private FrontendInterface $frontend)
    {
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = 'all', $tags = []): bool
    {
        return $this->frontend->clean($mode, $tags);
    }
}
