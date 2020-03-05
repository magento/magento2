<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\FlagPolicy;

/**
 * @inheritDoc
 */
class FlagPolicyMerger implements MergerInterface
{
    /**
     * @inheritDoc
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        return $policy1;
    }

    /**
     * @inheritDoc
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool
    {
        return ($policy1 instanceof FlagPolicy) && ($policy2 instanceof FlagPolicy);
    }
}
