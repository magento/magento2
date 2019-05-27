<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Interface describes how to compare given version of poison pill with latest in DB.
 */
interface PoisonPillCompareInterface
{
    /**
     * Check if version of current poison pill is latest.
     *
     * @param string $poisonPillVersion
     * @return bool
     */
    public function isLatestVersion(string $poisonPillVersion): bool;
}
