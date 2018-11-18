<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\ReviewApi\Api\Data\ReviewInterface;

/**
 * Class Aggregator
 */
class Aggregator implements AggregatorInterface
{
    /**
     * @var AggregatorInterface[]
     */
    private $aggregators;

    /**
     * ReviewValidatorChain constructor
     *
     * @param AggregatorInterface[] $aggregators
     * @throws LocalizedException
     */
    public function __construct(
        array $aggregators = []
    ) {
        foreach ($aggregators as $aggregator) {
            if (!$aggregator instanceof AggregatorInterface) {
                throw new LocalizedException(
                    __('Review Validator must implement AggregatorInterface.')
                );
            }
        }
        $this->aggregators = $aggregators;
    }

    /**
     * @inheritdoc
     */
    public function aggregate(ReviewInterface $review): void
    {
        foreach ($this->aggregators as $aggregator) {
            $aggregator->aggregate($review);
        }
    }
}
