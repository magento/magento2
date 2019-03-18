<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Api\Data;

/**
 * PoisonPill data interface.
 *
 * @api
 */
interface PoisonPillInterface
{
    /**
     * Returns version of poison pill.
     *
     * @return integer
     */
    public function getVersion(): ?int;
}
