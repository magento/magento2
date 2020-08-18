<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Merges policies with the same ID in order to have only 1 policy DTO-per-policy.
 */
interface MergerInterface
{
    /**
     * Merges 2 found policies into 1.
     *
     * @param PolicyInterface $policy1
     * @param PolicyInterface $policy2
     * @return PolicyInterface
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface;

    /**
     * Whether current merger can merge given 2 policies.
     *
     * @param PolicyInterface $policy1
     * @param PolicyInterface $policy2
     * @return bool
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool;
}
