<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Describes how to compare given version of poison pill with latest in DB.
 */
class PoisonPillCompare implements PoisonPillCompareInterface
{
    /**
     * Stub implementation
     *
     * @todo Will use cache storage after @MC-15997
     *
     * @param string $poisonPillVersion
     * @return bool
     */
    public function isLatestVersion(string $poisonPillVersion): bool
    {
        return true;
    }
}
