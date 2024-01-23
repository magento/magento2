<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\PluginTypesPolicy;

/**
 * @inheritDoc
 */
class PluginTypesPolicyMerger implements MergerInterface
{
    /**
     * @inheritDoc
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        /** @var PluginTypesPolicy $policy1 */
        /** @var PluginTypesPolicy $policy2 */
        return new PluginTypesPolicy(array_merge($policy1->getTypes(), $policy2->getTypes()));
    }

    /**
     * @inheritDoc
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool
    {
        return ($policy1 instanceof PluginTypesPolicy) && ($policy2 instanceof PluginTypesPolicy);
    }
}
