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
class PoisonPillRead implements PoisonPillReadInterface
{
    /**
     * Returns get latest version of poison pill.
     *
     * @todo Will use cache storage after @MC-15997
     *
     * @return string
     */
    public function getLatestVersion(): ?string
    {
        return null;
    }
}
