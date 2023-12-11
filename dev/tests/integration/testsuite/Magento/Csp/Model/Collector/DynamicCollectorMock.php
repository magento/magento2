<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Helps with testing CSP policies.
 */
class DynamicCollectorMock extends DynamicCollector
{
    /**
     * @var PolicyInterface[]
     */
    private $added = [];

    /**
     * @inheritDoc
     */
    public function add(PolicyInterface $policy): void
    {
        $this->added[] = $policy;

        parent::add($policy);
    }

    /**
     * Collect added policies and start a new cycle.
     *
     * @return PolicyInterface[]
     */
    public function consumeAdded(): array
    {
        $policies = $this->added;
        $this->added = [];

        return $policies;
    }
}
