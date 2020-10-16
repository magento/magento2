<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\SandboxPolicy;

/**
 * @inheritDoc
 */
class SandboxPolicyMerger implements MergerInterface
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        /** @var SandboxPolicy $policy1 */
        /** @var SandboxPolicy $policy2 */
        return new SandboxPolicy(
            $policy1->isFormAllowed() || $policy2->isFormAllowed(),
            $policy1->isModalsAllowed() || $policy2->isModalsAllowed(),
            $policy1->isOrientationLockAllowed() || $policy2->isOrientationLockAllowed(),
            $policy1->isPointerLockAllowed() || $policy2->isPointerLockAllowed(),
            $policy1->isPopupsAllowed() || $policy2->isPopupsAllowed(),
            $policy1->isPopupsToEscapeSandboxAllowed() || $policy2->isPopupsToEscapeSandboxAllowed(),
            $policy1->isPresentationAllowed() || $policy2->isPresentationAllowed(),
            $policy1->isSameOriginAllowed() || $policy2->isSameOriginAllowed(),
            $policy1->isScriptsAllowed() || $policy2->isScriptsAllowed(),
            $policy1->isTopNavigationAllowed() || $policy2->isTopNavigationAllowed(),
            $policy1->isTopNavigationByUserActivationAllowed() || $policy2->isTopNavigationByUserActivationAllowed()
        );
    }

    /**
     * @inheritDoc
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool
    {
        return ($policy1 instanceof SandboxPolicy) && ($policy2 instanceof SandboxPolicy);
    }
}
