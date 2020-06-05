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
     * Merge policies with same IDs and return a list of policies with 1 DTO per policy ID.
     *
     * @param PolicyInterface[] $collected
     * @return PolicyInterface[]
     * @throws \RuntimeException When failed to merge.
     */
    private function merge(array $collected): array
    {
        /** @var PolicyInterface[] $merged */
        $merged = [];

        foreach ($collected as $policy) {
            if (array_key_exists($policy->getId(), $merged)) {
                foreach ($this->mergers as $merger) {
                    if ($merger->canMerge($merged[$policy->getId()], $policy)) {
                        $merged[$policy->getId()] = $merger->merge($merged[$policy->getId()], $policy);
                        continue 2;
                    }
                }

                throw new \RuntimeException(sprintf('Merge for policies #%s was not found', $policy->getId()));
            } else {
                $merged[$policy->getId()] = $policy;
            }
        }

        return $merged;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $collected = $defaultPolicies;
        foreach ($this->collectors as $collector) {
            $collected = $this->merge($collector->collect($collected));
        }

        return array_values($collected);
    }
}
