<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Api;

/**
 * Interface describes how to describes how to compare poison pill with latest in DB.
 *
 * @api
 */
interface PoisonPillCompareInterface
{
    /**
     * Check if version of current poison pill is latest.
     *
     * @param int $poisonPillVersion
     * @return bool
     */
    public function isLatestVersion(int $poisonPillVersion): bool;
}
