<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Api\PolicyCollectorInterface;

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
     * @param PolicyCollectorInterface[] $collectors
     */
    public function __construct(array $collectors)
    {
        $this->collectors = $collectors;
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

        return $collected;
    }
}
