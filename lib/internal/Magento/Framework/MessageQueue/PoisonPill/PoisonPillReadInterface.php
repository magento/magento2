<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Describes how to get latest version of poison pill.
 * @api
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
