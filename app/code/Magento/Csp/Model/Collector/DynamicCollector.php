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
     * @var MergerInterface
     */
    private $merger;

    /**
     * @param MergerInterface $merger
     */
    public function __construct(MergerInterface $merger)
    {
        $this->merger = $merger;
    }

    /**
     * Add a policy for current page.
     *
     * @param PolicyInterface $policy
     * @return void
     */
    public function add(PolicyInterface $policy): void
    {
        if (array_key_exists($policy->getId(), $this->added)) {
            if ($this->merger->canMerge($this->added[$policy->getId()], $policy)) {
                $this->added[$policy->getId()] = $this->merger->merge($this->added[$policy->getId()], $policy);
            } else {
                throw new \RuntimeException('Cannot merge a policy of ' .get_class($policy));
            }
        } else {
            $this->added[$policy->getId()] = $policy;
        }
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        return array_merge($defaultPolicies, array_values($this->added));
    }
}
