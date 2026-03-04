<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure;

/**
 * Provides identity for context
 */
interface IdentityProviderInterface
{
    /**
     * One of ContextInterface constants
     *
     * @return int
     */
    public function fetchIdentityType(): int;

    /**
     * Identity string representation
     *
     * @return string
     */
    public function fetchIdentity(): string;
}
