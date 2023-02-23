<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
