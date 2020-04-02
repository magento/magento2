<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\PolicyCollectorInterface;

/**
 * Policy collector that has a priority over other collectors.
 */
interface PrioritizedPolicyCollectorInterface extends PolicyCollectorInterface
{
    /**
     * The higher the priority the earlier the collector will be called.
     *
     * @return int
     */
    public function getPriority(): int;
}
