<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Api\PolicyCollectorInterface;

/**
 * CSPs dynamically added during the rendering of current page (from .phtml templates for instance).
 */
class DynamicCollector implements PolicyCollectorInterface
{
    /**
     * @var PolicyInterface[]
     */
    private $added = [];

    /**
     * Add a policy for current page.
     *
     * @param PolicyInterface $policy
     * @return void
     */
    public function add(PolicyInterface $policy): void
    {
        $this->added[] = $policy;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        return array_merge($defaultPolicies, $this->added);
    }
}
