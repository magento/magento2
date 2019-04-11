<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Api;

/**
 * Describes how to get latest version of poison pill.
 *
 * @api
 */
interface PoisonPillReadInterface
{
    /**
     * Returns get latest version of poison pill.
     *
     * @return int
     */
    public function getLatestVersion(): int;
}
