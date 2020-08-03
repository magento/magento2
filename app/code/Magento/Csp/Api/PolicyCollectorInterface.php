<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Collects CSPs from a source.
 */
interface PolicyCollectorInterface
{
    /**
     * Collect all configured policies.
     *
     * Collector finds CSPs from configurations and returns a list.
     * The resulting list will be used to render policies as is so it is a collector's responsibility to include
     * previously found policies from $defaultPolicies or redefine them.
     *
     * @param PolicyInterface[] $defaultPolicies Default policies/policies found previously.
     * @return PolicyInterface[]
     */
    public function collect(array $defaultPolicies = []): array;
}
