<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Describes how to get latest version of poison pill.
 */
interface PoisonPillReadInterface
{
    /**
     * Returns get latest version of poison pill.
     *
     * @return string
     */
    public function getLatestVersion(): string;
}
