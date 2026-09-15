<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\FrontendInterface;

/**
 * Capability contract for two-tier (remote-synchronized) cache backends that expose their
 * individual tiers, so collaborators can reach a specific tier without probing for methods.
 */
interface TwoTierBackendInterface
{
    /**
     * Get the remote (L2 - persistent, shared) cache frontend.
     *
     * @return FrontendInterface
     */
    public function getRemote(): FrontendInterface;

    /**
     * Get the local (L1 - fast, per-worker) cache frontend.
     *
     * @return FrontendInterface
     */
    public function getLocal(): FrontendInterface;
}
