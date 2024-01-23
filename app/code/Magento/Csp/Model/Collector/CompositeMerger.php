<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Merges policies using different mergers.
 */
class CompositeMerger implements MergerInterface
{
    /**
     * @var MergerInterface[]
     */
    private $mergers;

    /**
     * @param MergerInterface[] $mergers
     */
    public function __construct(array $mergers)
    {
        $this->mergers = $mergers;
    }

    /**
     * @inheritDoc
     */
    public function merge(PolicyInterface $policy1, PolicyInterface $policy2): PolicyInterface
    {
        foreach ($this->mergers as $merger) {
            if ($merger->canMerge($policy1, $policy2)) {
                return $merger->merge($policy1, $policy2);
            }
        }

        throw new \RuntimeException('Cannot merge 2 policies of ' .get_class($policy1));
    }

    /**
     * @inheritDoc
     */
    public function canMerge(PolicyInterface $policy1, PolicyInterface $policy2): bool
    {
        foreach ($this->mergers as $merger) {
            if ($merger->canMerge($policy1, $policy2)) {
                return true;
            }
        }

        return false;
    }
}
