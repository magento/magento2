<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Api;

use Magento\MessageQueue\Api\Data\PoisonPillInterface;

/**
 * Describes how to get latest version of poison pill.
 *
 * @api
 */
interface PoisonPillReadInterface
{
    /**
     * Returns latest poison pill.
     *
     * @return PoisonPillInterface
     */
    public function getLatest(): PoisonPillInterface;
}
