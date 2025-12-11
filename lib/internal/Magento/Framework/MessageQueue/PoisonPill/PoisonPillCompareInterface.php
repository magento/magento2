<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Interface describes how to compare given version of poison pill with latest in DB.
 * @api
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
