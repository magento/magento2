<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\FetchPolicy;

/**
 * @inheritDoc
 */
class FetchPolicyMerger implements MergerInterface
{
    /**
     * @inheritDoc
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        /** @var FetchPolicy $policy1 */
        /** @var FetchPolicy $policy2 */
        return new FetchPolicy(
            $policy1->getId(),
            $policy1->isNoneAllowed() || $policy2->isNoneAllowed(),
            array_merge($policy1->getHostSources(), $policy2->getHostSources()),
            array_merge($policy1->getSchemeSources(), $policy2->getSchemeSources()),
            $policy1->isSelfAllowed() || $policy2->isSelfAllowed(),
            $policy1->isInlineAllowed() || $policy2->isInlineAllowed(),
            $policy1->isEvalAllowed() || $policy2->isEvalAllowed(),
            array_merge($policy1->getNonceValues(), $policy2->getNonceValues()),
            array_merge($policy1->getHashes(), $policy2->getHashes()),
            $policy1->isDynamicAllowed() || $policy2->isDynamicAllowed(),
            $policy1->areEventHandlersAllowed() || $policy2->areEventHandlersAllowed()
        );
    }

    /**
     * @inheritDoc
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool
    {
        return ($policy1 instanceof FetchPolicy) && ($policy2 instanceof FetchPolicy);
    }
}
