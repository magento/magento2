<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Collector\MergerInterface;

/**
 * Delegates collecting to multiple collectors.
 */
class CompositePolicyCollector implements PolicyCollectorInterface
{
    /**
     * @var PolicyCollectorInterface[]
     */
    private $collectors;

    /**
     * @var MergerInterface[]
     */
    private $mergers;

    /**
     * @param PolicyCollectorInterface[] $collectors
     * @param MergerInterface[] $mergers
     */
    public function __construct(array $collectors, array $mergers)
    {
        $this->collectors = $collectors;
        $this->mergers = $mergers;
    }

    /**
     * Merge 2 policies with the same ID.
     *
     * @param PolicyInterface $policy1
     * @param PolicyInterface $policy2
     * @return PolicyInterface
     * @throws \RuntimeException When failed to merge.
     */
    private function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        foreach ($this->mergers as $merger) {
            if ($merger->canMerge($policy1, $policy2)) {
                return $merger->merge($policy1, $policy2);
            }
        }

        throw new \RuntimeException(sprintf('Merge for policies #%s was not found', $policy1->getId()));
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $collected = $defaultPolicies;
        foreach ($this->collectors as $collector) {
            $collected = $collector->collect($collected);
        }
        //Merging policies.
        /** @var PolicyInterface[] $result */
        $result = [];
        foreach ($collected as $policy) {
            if (array_key_exists($policy->getId(), $result)) {
                $result[$policy->getId()] = $this->merge($result[$policy->getId()], $policy);
            } else {
                $result[$policy->getId()] = $policy;
            }
        }

        return array_values($result);
    }
}
